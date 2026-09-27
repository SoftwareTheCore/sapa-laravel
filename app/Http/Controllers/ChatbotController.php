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
     * Handle incoming chat requests and forward to Langflow API.
     * Includes token-saving optimizations (Local Fast Path, Input Trimming, Caching).
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
