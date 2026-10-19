<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Daftar kategori laporan yang valid (harus sesuai ReportController).
     */
    private const CATEGORIES = [
        '1' => 'Infrastruktur',
        '2' => 'Kebersihan',
        '3' => 'Keamanan',
        '4' => 'Layanan publik',
        '5' => 'Lainnya',
    ];

    /**
     * Kata kunci yang mengindikasikan warga ingin membuat laporan.
     */
    private const REPORT_KEYWORDS = [
        'lapor', 'melapor', 'buat laporan', 'mau lapor', 'ingin melapor',
        'saya mau lapor', 'saya ingin melapor', 'buat pengaduan', 'pengaduan',
        'mau mengadu', 'ingin mengadu', 'aduan', 'laporkan', 'saya ingin lapor',
        'bikin laporan', 'kirim laporan', 'ajukan laporan', 'laporan baru',
    ];

    /**
     * Handle incoming chat requests and forward to Langflow API.
     * Includes token-saving optimizations (Local Fast Path, Input Trimming, Caching)
     * and a multi-step conversational report creation flow.
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'session_id' => ['nullable', 'string', 'max:100'],
        ]);

        // 1. Token Saving: Normalisasi teks & batasi panjang string max 500 karakter
        $message = trim($validated['message']);
        $message = preg_replace('/\s+/', ' ', $message); // Rapikan spasi berlebih
        $message = mb_substr($message, 0, 500); // Batasi input untuk menghemat token
        $sessionId = $validated['session_id'] ?? (string) Str::uuid();

        $lowerMsg = strtolower($message);

        // ────────────────────────────────────────────────────────
        // REPORT FLOW: Cek apakah session ini sedang dalam mode laporan
        // ────────────────────────────────────────────────────────
        $reportState = $this->getReportState($sessionId);

        if ($reportState !== null) {
            return $this->handleReportStep($sessionId, $reportState, $message, $lowerMsg);
        }

        // Deteksi intent "buat laporan" — mulai flow pelaporan
        if ($this->isReportIntent($lowerMsg)) {
            return $this->startReportFlow($sessionId);
        }

        // 2. Fast Path Token Saver: Sapaan dasar diselesaikan lokal (0 Token LLM terpakai)
        $quickGreetings = [
            'halo' => 'Halo! Ada yang bisa saya bantu terkait layanan atau laporan di Kelurahan?',
            'hai' => 'Hai! Ada yang bisa SAPA AI bantu untuk Anda hari ini?',
            'hi' => 'Halo! Ada yang bisa saya bantu?',
            'p' => 'Halo! Silakan sampaikan pertanyaan atau laporan Anda.',
            'ping' => 'Halo! SAPA AI siap membantu Anda.',
            'selamat pagi' => 'Selamat pagi! Ada yang bisa saya bantu hari ini?',
            'selamat siang' => 'Selamat siang! Ada yang bisa saya bantu hari ini?',
            'selamat sore' => 'Selamat sore! Ada yang bisa saya bantu hari ini?',
            'selamat malam' => 'Selamat malam! Ada yang bisa saya bantu?',
            'terima kasih' => 'Sama-sama! Senang bisa membantu Anda.',
            'makasih' => 'Sama-sama! Jika ada pertanyaan lain, jangan ragu untuk bertanya.',
            'thanks' => 'Sama-sama! Semoga harimu menyenangkan.',
        ];

        if (array_key_exists($lowerMsg, $quickGreetings)) {
            return response()->json([
                'output_type' => 'chat',
                'input_type' => 'text',
                'session_id' => $sessionId,
                'reply' => $quickGreetings[$lowerMsg],
                'source' => 'local_fast_path'
            ]);
        }

        // 3. Fast Path Token Saver: Pengecekan Kode Tracking SAPA (0 Token LLM terpakai)
        if (preg_match('/SAPA-[A-Z0-9]{8}/i', $message, $matches)) {
            $code = strtoupper($matches[0]);
            $report = Report::where('tracking_code', $code)->first();

            if ($report) {
                $statusText = match ($report->status) {
                    'baru' => 'Baru Diterima (Menunggu penanganan)',
                    'diproses' => 'Sedang Diproses oleh Petugas',
                    'selesai' => 'Selesai Ditindaklanjuti',
                    'ditolak' => 'Ditolak',
                    default => ucfirst($report->status),
                };

                $reply = "📌 **Status Laporan {$report->tracking_code}**\n\n"
                    . "• **Judul:** {$report->title}\n"
                    . "• **Kategori:** {$report->category}\n"
                    . "• **Lokasi:** {$report->location}\n"
                    . "• **Status:** {$statusText}\n"
                    . ($report->staff_note ? "• **Catatan Petugas:** {$report->staff_note}\n" : "")
                    . "• **Tanggal Lapor:** " . $report->created_at->translatedFormat('d F Y, H:i') . " WIB";

                return response()->json([
                    'output_type' => 'chat',
                    'input_type' => 'text',
                    'session_id' => $sessionId,
                    'reply' => $reply,
                    'source' => 'system_tracking'
                ]);
            }
        }

        // 4. Token Saver: Cache jawaban untuk pertanyaan identik selama 10 menit
        $cacheKey = 'langflow_cache_' . md5($lowerMsg);
        if (Cache::has($cacheKey)) {
            return response()->json([
                'output_type' => 'chat',
                'input_type' => 'text',
                'session_id' => $sessionId,
                'reply' => Cache::get($cacheKey),
                'source' => 'cache'
            ]);
        }

        // 5. Kirim ke API Langflow jika tidak ditemukan di Fast Path lokal
        $baseUrl = config('services.langflow.url', 'http://127.0.0.1:7860');
        $flowId = config('services.langflow.flow_id', '6e10128d-eb36-42f5-8aed-d894f30327fa');
        $apiKey = config('services.langflow.api_key', 'YOUR_API_KEY_HERE');

        $endpoint = rtrim($baseUrl, '/') . '/api/v1/run/' . $flowId;

        $payload = [
            'output_type' => 'chat',
            'input_type' => 'text',
            'input_value' => $message,
            'session_id' => $sessionId,
        ];

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (!empty($apiKey) && $apiKey !== 'YOUR_API_KEY_HERE') {
            $headers['x-api-key'] = $apiKey;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(25)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $this->extractReplyText($data);

                // Simpan jawaban valid ke cache 10 menit untuk hemat token
                if (!empty($reply) && strlen($reply) > 5) {
                    Cache::put($cacheKey, $reply, now()->addMinutes(10));
                }

                return response()->json([
                    'output_type' => 'chat',
                    'input_type' => 'text',
                    'session_id' => $sessionId,
                    'reply' => $reply,
                    'raw' => $data
                ]);
            }

            return response()->json([
                'error' => true,
                'message' => 'Langflow API returned HTTP status ' . $response->status(),
                'details' => $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal terhubung ke server Langflow: ' . $e->getMessage(),
                'reply' => 'Maaf, layanan SAPA AI sedang tidak dapat dijangkau. Pastikan server Langflow berjalan di ' . $baseUrl
            ], 500);
        }
    }

    // ================================================================
    //  REPORT FLOW — Multi-step conversational state management
    // ================================================================

    /**
     * Deteksi apakah pesan warga bermaksud membuat laporan.
     */
    private function isReportIntent(string $lowerMsg): bool
    {
        foreach (self::REPORT_KEYWORDS as $keyword) {
            if (str_contains($lowerMsg, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Ambil state pelaporan dari cache (null jika tidak dalam mode laporan).
     */
    private function getReportState(string $sessionId): ?array
    {
        return Cache::get("report_flow_{$sessionId}");
    }

    /**
     * Simpan state pelaporan ke cache (TTL 30 menit agar tidak expired
     * selama warga masih mengisi form step-by-step).
     */
    private function saveReportState(string $sessionId, array $state): void
    {
        Cache::put("report_flow_{$sessionId}", $state, now()->addMinutes(30));
    }

    /**
     * Hapus state pelaporan (selesai atau dibatalkan).
     */
    private function clearReportState(string $sessionId): void
    {
        Cache::forget("report_flow_{$sessionId}");
    }

    /**
     * Mulai flow pelaporan — langkah pertama: tanya nama.
     */
    private function startReportFlow(string $sessionId)
    {
        $this->saveReportState($sessionId, [
            'step' => 'ask_name',
            'data' => [],
        ]);

        $reply = "📝 **Mode Laporan Aktif**\n\n"
            . "Baik, saya akan membantu Anda membuat laporan.\n"
            . "Kita akan melalui beberapa langkah singkat.\n\n"
            . "Pertama, **siapa nama lengkap Anda?**\n\n"
            . "_Ketik \"batal\" kapan saja untuk membatalkan._";

        return $this->reportResponse($sessionId, $reply);
    }

    /**
     * Proses setiap step dalam flow pelaporan.
     */
    private function handleReportStep(string $sessionId, array $state, string $message, string $lowerMsg)
    {
        // Perintah batal — keluar dari mode laporan kapan saja
        if (in_array($lowerMsg, ['batal', 'cancel', 'keluar', 'stop', 'batalkan'])) {
            $this->clearReportState($sessionId);
            return $this->reportResponse(
                $sessionId,
                "❌ Laporan dibatalkan.\n\nTidak ada data yang disimpan. Jika Anda ingin melapor lagi, cukup ketik **\"lapor\"**."
            );
        }

        $step = $state['step'];
        $data = $state['data'];

        return match ($step) {
            'ask_name'        => $this->stepReceiveName($sessionId, $data, $message),
            'ask_category'    => $this->stepReceiveCategory($sessionId, $data, $message, $lowerMsg),
            'ask_title'       => $this->stepReceiveTitle($sessionId, $data, $message),
            'ask_description' => $this->stepReceiveDescription($sessionId, $data, $message),
            'ask_location'    => $this->stepReceiveLocation($sessionId, $data, $message),
            'ask_phone'       => $this->stepReceivePhone($sessionId, $data, $message),
            'confirm'         => $this->stepReceiveConfirmation($sessionId, $data, $lowerMsg),
            default           => $this->cancelAndRestart($sessionId),
        };
    }

    /**
     * Step 1: Terima nama → tanya kategori.
     */
    private function stepReceiveName(string $sessionId, array $data, string $message)
    {
        $name = trim($message);

        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            return $this->reportResponse(
                $sessionId,
                "⚠️ Nama harus antara 2–120 karakter. Silakan masukkan **nama lengkap** Anda:"
            );
        }

        $data['citizen_name'] = $name;

        $this->saveReportState($sessionId, [
            'step' => 'ask_category',
            'data' => $data,
        ]);

        $categoryList = "";
        foreach (self::CATEGORIES as $num => $cat) {
            $categoryList .= "  {$num}. {$cat}\n";
        }

        $reply = "Terima kasih, **{$name}**! 👋\n\n"
            . "Sekarang, pilih **kategori laporan** dengan mengetik nomornya:\n\n"
            . $categoryList
            . "\n_Contoh: ketik **1** untuk Infrastruktur_";

        return $this->reportResponse($sessionId, $reply);
    }

    /**
     * Step 2: Terima kategori → tanya judul.
     */
    private function stepReceiveCategory(string $sessionId, array $data, string $message, string $lowerMsg)
    {
        // Coba cocokkan berdasarkan nomor
        $trimmed = trim($message);
        $category = null;

        if (array_key_exists($trimmed, self::CATEGORIES)) {
            $category = self::CATEGORIES[$trimmed];
        } else {
            // Coba cocokkan berdasarkan nama kategori
            foreach (self::CATEGORIES as $cat) {
                if (strtolower($cat) === $lowerMsg) {
                    $category = $cat;
                    break;
                }
            }
        }

        if ($category === null) {
            $categoryList = "";
            foreach (self::CATEGORIES as $num => $cat) {
                $categoryList .= "  {$num}. {$cat}\n";
            }
            return $this->reportResponse(
                $sessionId,
                "⚠️ Kategori tidak dikenali. Silakan pilih dengan mengetik **nomor** (1–5):\n\n" . $categoryList
            );
        }

        $data['category'] = $category;

        $this->saveReportState($sessionId, [
            'step' => 'ask_title',
            'data' => $data,
        ]);

        $reply = "Kategori: **{$category}** ✅\n\n"
            . "Selanjutnya, apa **judul singkat** untuk laporan Anda?\n\n"
            . "_Contoh: \"Jalan berlubang di depan balai warga\"_";

        return $this->reportResponse($sessionId, $reply);
    }

    /**
     * Step 3: Terima judul → tanya deskripsi.
     */
    private function stepReceiveTitle(string $sessionId, array $data, string $message)
    {
        $title = trim($message);

        if (mb_strlen($title) < 5 || mb_strlen($title) > 180) {
            return $this->reportResponse(
                $sessionId,
                "⚠️ Judul harus antara 5–180 karakter. Silakan tulis **judul singkat** laporan Anda:"
            );
        }

        $data['title'] = $title;

        $this->saveReportState($sessionId, [
            'step' => 'ask_description',
            'data' => $data,
        ]);

        $reply = "Judul: **{$title}** ✅\n\n"
            . "Sekarang, **ceritakan detail masalah** yang ingin Anda laporkan.\n"
            . "Jelaskan selengkap mungkin agar petugas dapat memahami situasinya.";

        return $this->reportResponse($sessionId, $reply);
    }

    /**
     * Step 4: Terima deskripsi → tanya lokasi.
     */
    private function stepReceiveDescription(string $sessionId, array $data, string $message)
    {
        $description = trim($message);

        if (mb_strlen($description) < 10 || mb_strlen($description) > 5000) {
            return $this->reportResponse(
                $sessionId,
                "⚠️ Deskripsi minimal 10 karakter dan maksimal 5000 karakter.\nSilakan **jelaskan kembali** masalah yang ingin dilaporkan:"
            );
        }

        $data['description'] = $description;

        $this->saveReportState($sessionId, [
            'step' => 'ask_location',
            'data' => $data,
        ]);

        $reply = "Detail laporan dicatat ✅\n\n"
            . "Di mana **lokasi kejadian** ini?\n\n"
            . "_Contoh: \"Jl. Merdeka RT 03/RW 01, depan pos ronda\"_";

        return $this->reportResponse($sessionId, $reply);
    }

    /**
     * Step 5: Terima lokasi → tanya nomor telepon.
     */
    private function stepReceiveLocation(string $sessionId, array $data, string $message)
    {
        $location = trim($message);

        if (mb_strlen($location) < 5 || mb_strlen($location) > 255) {
            return $this->reportResponse(
                $sessionId,
                "⚠️ Lokasi harus antara 5–255 karakter.\nSilakan masukkan **alamat atau patokan lokasi** kejadian:"
            );
        }

        $data['location'] = $location;

        $this->saveReportState($sessionId, [
            'step' => 'ask_phone',
            'data' => $data,
        ]);

        $reply = "Lokasi: **{$location}** ✅\n\n"
            . "Terakhir, masukkan **nomor telepon** Anda agar petugas RT bisa menghubungi terkait laporan ini.\n\n"
            . "_Contoh: 0812 3456 7890_";

        return $this->reportResponse($sessionId, $reply);
    }

    /**
     * Step 6: Terima nomor telepon → tampilkan ringkasan & minta konfirmasi.
     */
    private function stepReceivePhone(string $sessionId, array $data, string $message)
    {
        $phone = trim($message);

        if (!preg_match('/^[0-9+() .\-]{8,30}$/', $phone)) {
            return $this->reportResponse(
                $sessionId,
                "⚠️ Format nomor telepon tidak valid.\nGunakan angka, boleh pakai tanda +, spasi, atau strip.\n\n_Contoh: 0812 3456 7890_"
            );
        }

        $data['citizen_phone'] = $phone;

        $this->saveReportState($sessionId, [
            'step' => 'confirm',
            'data' => $data,
        ]);

        $reply = "📋 **Ringkasan Laporan Anda:**\n\n"
            . "• **Nama:** {$data['citizen_name']}\n"
            . "• **Kategori:** {$data['category']}\n"
            . "• **Judul:** {$data['title']}\n"
            . "• **Deskripsi:** {$data['description']}\n"
            . "• **Lokasi:** {$data['location']}\n"
            . "• **Telepon:** {$data['citizen_phone']}\n\n"
            . "Apakah data di atas sudah benar?\n"
            . "Ketik **\"ya\"** untuk mengirim, atau **\"batal\"** untuk membatalkan.";

        return $this->reportResponse($sessionId, $reply);
    }

    /**
     * Step 7: Konfirmasi → simpan laporan ke database.
     */
    private function stepReceiveConfirmation(string $sessionId, array $data, string $lowerMsg)
    {
        $yesWords = ['ya', 'yes', 'iya', 'ok', 'oke', 'yaa', 'yoi', 'benar', 'betul', 'kirim', 'setuju', 'y'];

        if (!in_array($lowerMsg, $yesWords)) {
            return $this->reportResponse(
                $sessionId,
                "Ketik **\"ya\"** untuk mengirim laporan, atau **\"batal\"** untuk membatalkan."
            );
        }

        // Generate tracking code & simpan ke database
        $trackingCode = 'SAPA-' . strtoupper(Str::random(8));

        Report::create([
            'tracking_code' => $trackingCode,
            'citizen_name'  => $data['citizen_name'],
            'citizen_phone' => $data['citizen_phone'],
            'title'         => $data['title'],
            'category'      => $data['category'],
            'description'   => $data['description'],
            'location'      => $data['location'],
            'status'        => 'baru',
        ]);

        // Hapus state flow — selesai
        $this->clearReportState($sessionId);

        $reply = "✅ **Laporan berhasil dikirim!**\n\n"
            . "📌 Kode laporan Anda: **{$trackingCode}**\n\n"
            . "Simpan kode ini untuk memantau perkembangan laporan Anda.\n"
            . "Cukup ketik kode tersebut di chat ini kapan saja untuk cek status.\n\n"
            . "Laporan Anda akan segera ditinjau oleh petugas RT. "
            . "Terima kasih telah melapor melalui SAPA! 🙏";

        return $this->reportResponse($sessionId, $reply);
    }

    /**
     * Fallback jika state tidak dikenali — reset dan mulai ulang.
     */
    private function cancelAndRestart(string $sessionId)
    {
        $this->clearReportState($sessionId);

        return $this->reportResponse(
            $sessionId,
            "⚠️ Terjadi kesalahan pada sesi laporan. Sesi telah direset.\n\nKetik **\"lapor\"** untuk memulai laporan baru."
        );
    }

    /**
     * Format JSON response standar untuk semua step pelaporan.
     */
    private function reportResponse(string $sessionId, string $reply)
    {
        return response()->json([
            'output_type' => 'chat',
            'input_type'  => 'text',
            'session_id'  => $sessionId,
            'reply'       => $reply,
            'source'      => 'report_flow',
        ]);
    }

    // ================================================================
    //  LANGFLOW RESPONSE PARSER
    // ================================================================

    /**
     * Extract reply text from various potential Langflow JSON response structures.
     */
    private function extractReplyText($data): string
    {
        if (is_string($data)) {
            return $data;
        }

        if (isset($data['outputs'][0]['outputs'][0]['results']['message']['text'])) {
            return $data['outputs'][0]['outputs'][0]['results']['message']['text'];
        }

        if (isset($data['outputs'][0]['outputs'][0]['messages'][0]['message'])) {
            return $data['outputs'][0]['outputs'][0]['messages'][0]['message'];
        }

        if (isset($data['outputs'][0]['outputs'][0]['results']['message']['data']['text'])) {
            return $data['outputs'][0]['outputs'][0]['results']['message']['data']['text'];
        }

        if (isset($data['outputs'][0]['outputs'][0]['artifacts']['message'])) {
            return $data['outputs'][0]['outputs'][0]['artifacts']['message'];
        }

        if (isset($data['result']) && is_string($data['result'])) {
            return $data['result'];
        }

        if (isset($data['message']) && is_string($data['message'])) {
            return $data['message'];
        }

        return 'Maaf, saya tidak dapat memproses balasan dari SAPA AI.';
    }
}
