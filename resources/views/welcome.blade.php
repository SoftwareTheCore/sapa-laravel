<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/sapalogo.png') }}">
    <title>SAPA | Layanan Warga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ url('/') }}" aria-label="SAPA Beranda">
            <img class="brand-logo" src="{{ asset('images/sapalogo.png') }}" alt="SAPA - Sistem Aspirasi dan Pengaduan Masyarakat">
        </a>
        <a class="staff-link" href="{{ route('staff.login') }}"><span class="staff-icon">&#128100;</span>Portal Petugas</a>
    </header>
    <main>
        <section class="hero-section">
            <div class="hero-copy">
                <p class="eyebrow">LAYANAN WARGA KELURAHAN</p>
                <h1>Sampaikan aspirasi,<br><em>kota bergerak.</em></h1>
                <p class="hero-description">SAPA hadir untuk mendengar laporan, keluhan, dan ide Anda. Sampaikan langsung kepada perangkat wilayah dengan mudah, aman, dan transparan.</p>
                <div class="hero-actions"><a class="button button-primary" href="{{ route('reports.create') }}">Buat Laporan Cepat <span>&rarr;</span></a><a class="text-link" href="#cara-kerja">Bagaimana cara kerja SAPA?</a></div>
                <div class="trust-row"><span><b class="trust-dot">&#10003;</b> Tanpa perlu login</span><span><b class="trust-dot">&#10003;</b> Data terlindungi</span></div>
            </div>
            <!-- <div class="hero-visual" aria-label="Logo SAPA">
                <div class="hero-logo-panel">
                    <img class="hero-logo" src="{{ asset('images/sapalogo.png') }}" alt="Logo resmi SAPA">
                </div>
                <p class="hero-logo-caption">Suara warga, perubahan nyata.</p>
            </div> -->
        </section>
        <section class="service-section" id="cara-kerja">
            <div class="section-heading"><p class="eyebrow">SATU PLATFORM UNTUK SEMUA</p><h2>Layanan yang dekat dengan Anda</h2><p>Mulai dari laporan sederhana hingga aspirasi untuk lingkungan yang lebih baik.</p></div>
            <div class="service-grid">
                <article class="service-card featured-card"><div class="service-icon green-icon">&#9993;</div><div><h3>Laporan Cepat</h3><p>Laporkan masalah di lingkungan Anda tanpa akun dan tanpa proses yang berbelit.</p><a href="{{ route('reports.create') }}">Mulai melapor <span>&rarr;</span></a></div></article>
                <article class="service-card"><div class="service-icon blue-icon">&#128203;</div><div><h3>Pantau Laporan</h3><p>Lihat perkembangan laporan yang pernah Anda sampaikan dengan kode laporan.</p><span class="muted-link">Segera hadir</span></div></article>
                <article class="service-card"><div class="service-icon orange-icon">&#9733;</div><div><h3>Aspirasi Warga</h3><p>Bagikan ide dan masukan untuk membuat kelurahan kita semakin nyaman.</p><span class="muted-link">Segera hadir</span></div></article>
            </div>
        </section>
        <section class="company-profile-section">
            <div class="company-profile-inner">
                <div class="company-profile-visual">
                    <div class="company-profile-rule"></div>
                    <img class="company-profile-logo" src="{{ asset('images/sapalogo.png') }}" alt="Logo SAPA">
                    <span class="company-profile-stamp">KELURAHAN TANGGAP</span>
                </div>
                <div class="company-profile-copy">
                    <p class="eyebrow">TENTANG SAPA</p>
                    <h2>Sistem Aspirasi &amp;<br>Pengaduan Masyarakat</h2>
                    <p class="company-profile-lead">SAPA adalah platform digital milik pemerintah kelurahan untuk menampung laporan, keluhan, dan aspirasi warga secara langsung, cepat, dan transparan.</p>
                    <p class="company-profile-detail">Dibangun untuk memperdekat jarak antara warga dan perangkat wilayah. Setiap laporan ditangani oleh petugas terverifikasi dan dapat dipantau perkembangannya secara real-time.</p>
                    <div class="company-profile-facts">
                        <div>
                            <strong>Tanpa Login</strong>
                            <span>Warga langsung melapor tanpa perlu daftar akun</span>
                        </div>
                        <div>
                            <strong>Kode Tracking</strong>
                            <span>Setiap laporan punya kode unik untuk dipantau</span>
                        </div>
                        <div>
                            <strong>Respon Cepat</strong>
                            <span>Petugas memproses dan memberi catatan tindak lanjut</span>
                        </div>
                    </div>
                    <a class="company-profile-link" href="{{ route('reports.create') }}">Mulai buat laporan sekarang <span>&rarr;</span></a>
                </div>
            </div>
        </section>
    </main>
    <footer class="site-footer"><span><b>SAPA</b> untuk kelurahan yang tanggap dan melayani.</span><span>&copy; {{ date('Y') }} Pemerintah Kelurahan</span></footer>

    {{-- ============================================================
         SAPA AI CHATBOT BUBBLE
         Konfigurasi: ganti LANGFLOW_API_KEY dan LANGFLOW_FLOW_ID
         sesuai pengaturan Langflow kamu.
    ============================================================ --}}
    <div id="sapa-chat-bubble" aria-label="Buka chat SAPA AI" role="button" tabindex="0" title="Tanya SAPA AI">
        <span class="chat-bubble-icon">&#128172;</span>
        <span class="chat-bubble-dot" id="sapa-chat-dot" hidden></span>
    </div>

    <div id="sapa-chat-window" aria-label="Jendela chat SAPA AI">
        <div class="chat-window-header">
            <div class="chat-window-identity">
                <span class="chat-window-avatar">&#129302;</span>
                <div>
                    <strong>SAPA AI</strong>
                    <span id="sapa-chat-status">Siap membantu</span>
                </div>
            </div>
            <button class="chat-window-close" id="sapa-chat-close" aria-label="Tutup chat">&#10005;</button>
        </div>
        <div class="chat-messages" id="sapa-chat-messages">
            <div class="chat-msg chat-msg-bot">
                <span class="msg-avatar">&#129302;</span>
                <div class="msg-bubble">Halo! Saya <b>SAPA AI</b>. Ada yang bisa saya bantu hari ini?<br><br>
                Anda bisa tanya:<br>
                &bull; Status laporan (contoh: <em>cek SAPA-AB12CD34</em>)<br>
                &bull; FAQ & cara membuat laporan<br>
                &bull; Buat laporan baru via chat</div>
            </div>
        </div>
        <div class="chat-input-row">
            <input type="text" id="sapa-chat-input" class="chat-input" placeholder="Ketik pesan..." autocomplete="off" maxlength="500" />
            <button class="chat-send-btn" id="sapa-chat-send" aria-label="Kirim pesan">&#10148;</button>
        </div>
    </div>

    <script>
    (function () {
        // ──────────────────────────────────────────────
        // KONFIGURASI LANGFLOW
        // Endpoint & credentials default dapat diatur via .env
        // ──────────────────────────────────────────────
        var LANGFLOW_BASE_URL = 'http://127.0.0.1:7860';
        var LANGFLOW_FLOW_ID  = '6e10128d-eb36-42f5-8aed-d894f30327fa';
        var LANGFLOW_API_KEY  = 'YOUR_API_KEY_HERE';
        // ──────────────────────────────────────────────

        // Inisialisasi Session ID (menggunakan crypto.randomUUID() jika tersedia)
        function getSessionId() {
            var stored = sessionStorage.getItem('sapa_chat_session_id');
            if (stored) return stored;

            var newId;
            if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
                newId = crypto.randomUUID();
            } else {
                newId = 'sapa-' + Math.random().toString(36).substring(2, 11) + '-' + Date.now().toString(36);
            }
            sessionStorage.setItem('sapa_chat_session_id', newId);
            return newId;
        }

        var SESSION_ID = getSessionId();

        var bubble   = document.getElementById('sapa-chat-bubble');
        var chatWin  = document.getElementById('sapa-chat-window');
        var closeBtn = document.getElementById('sapa-chat-close');
        var input    = document.getElementById('sapa-chat-input');
        var sendBtn  = document.getElementById('sapa-chat-send');
        var messages = document.getElementById('sapa-chat-messages');
        var dot      = document.getElementById('sapa-chat-dot');
        var status   = document.getElementById('sapa-chat-status');

        // Buka / tutup jendela chat
        function openChat() {
            chatWin.classList.add('is-open');
            dot.setAttribute('hidden', '');
            input.focus();
        }
        function closeChat() {
            chatWin.classList.remove('is-open');
        }
        function toggleChat() {
            if (chatWin.classList.contains('is-open')) {
                closeChat();
            } else {
                openChat();
            }
        }

        bubble.addEventListener('click', toggleChat);
        bubble.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') toggleChat(); });
        closeBtn.addEventListener('click', closeChat);

        // Scroll ke pesan terakhir
        function scrollToBottom() {
            messages.scrollTop = messages.scrollHeight;
        }

        // Format teks balasan (escape HTML & dukung bold markdown)
        function formatReplyText(str) {
            if (!str) return '';
            var safe = str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            // Format **teks** menjadi <b>teks</b>
            safe = safe.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
            // Format line breaks
            safe = safe.replace(/\n/g, '<br>');
            return safe;
        }

        // Tambah bubble pesan ke UI
        function appendMessage(role, html) {
            var wrap = document.createElement('div');
            wrap.className = 'chat-msg chat-msg-' + role;
            if (role === 'bot') {
                wrap.innerHTML = '<span class="msg-avatar">&#129302;</span><div class="msg-bubble">' + html + '</div>';
            } else {
                wrap.innerHTML = '<div class="msg-bubble msg-bubble-user">' + html + '</div>';
            }
            messages.appendChild(wrap);
            scrollToBottom();
        }

        // Indikator "mengetik..."
        function showTyping() {
            var el = document.createElement('div');
            el.className = 'chat-msg chat-msg-bot chat-typing';
            el.id = 'sapa-typing';
            el.innerHTML = '<span class="msg-avatar">&#129302;</span><div class="msg-bubble typing-dots"><span></span><span></span><span></span></div>';
            messages.appendChild(el);
            scrollToBottom();
            return el;
        }

        function removeTyping() {
            var el = document.getElementById('sapa-typing');
            if (el) el.remove();
        }

        function escHtml(str) {
            return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        // Ekstraksi balasan dari berbagai struktur JSON Langflow
        function parseLangflowResponse(data) {
            if (data.reply) return data.reply;
            try {
                if (data.outputs && data.outputs[0] && data.outputs[0].outputs && data.outputs[0].outputs[0]) {
                    var out = data.outputs[0].outputs[0];
                    if (out.results && out.results.message && out.results.message.text) {
                        return out.results.message.text;
                    }
                    if (out.messages && out.messages[0] && out.messages[0].message) {
                        return out.messages[0].message;
                    }
                    if (out.results && out.results.message && out.results.message.data && out.results.message.data.text) {
                        return out.results.message.data.text;
                    }
                    if (out.artifacts && out.artifacts.message) {
                        return out.artifacts.message;
                    }
                }
            } catch (e) {
                console.warn('Fallback JSON extraction:', e);
            }
            if (data.result && typeof data.result === 'string') return data.result;
            if (data.message && typeof data.message === 'string') return data.message;
            return 'Maaf, tidak dapat mengambil jawaban dari SAPA AI.';
        }

        // Kirim pesan ke Backend Proxy (atau Direct Fetch ke Langflow)
        function sendMessage() {
            var text = input.value.trim();
            if (!text) return;

            appendMessage('user', escHtml(text));
            input.value = '';
            sendBtn.disabled = true;
            status.textContent = 'Sedang membalas…';

            showTyping();

            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfMeta ? csrfMeta.content : '';

            // 1. Coba panggil Laravel Proxy Endpoint terlebih dahulu
            fetch('{{ route("api.chat") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: text,
                    session_id: SESSION_ID
                })
            })
            .then(function (res) {
                if (!res.ok) {
                    // Jika Proxy gagal/error 404, fallback ke direct Langflow fetch
                    return fetchDirectLangflow(text);
                }
                return res.json();
            })
            .then(function (data) {
                removeTyping();
                var reply = parseLangflowResponse(data);
                appendMessage('bot', formatReplyText(reply));
                status.textContent = 'Siap membantu';
                if (!chatWin.classList.contains('is-open')) dot.removeAttribute('hidden');
            })
            .catch(function (err) {
                // Fallback ke Direct Langflow Fetch jika proxy tidak tersedia
                fetchDirectLangflow(text);
            })
            .finally(function () {
                sendBtn.disabled = false;
                input.focus();
            });
        }

        // Langflow Direct Fetch (sesuai snippet JS yang diberikan)
        function fetchDirectLangflow(text) {
            var payload = {
                "output_type": "chat",
                "input_type": "text",
                "input_value": text,
                "session_id": SESSION_ID
            };

            var options = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'x-api-key': LANGFLOW_API_KEY
                },
                body: JSON.stringify(payload)
            };

            return fetch(LANGFLOW_BASE_URL + '/api/v1/run/' + LANGFLOW_FLOW_ID, options)
                .then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function (data) {
                    removeTyping();
                    var reply = parseLangflowResponse(data);
                    appendMessage('bot', formatReplyText(reply));
                    status.textContent = 'Siap membantu';
                    if (!chatWin.classList.contains('is-open')) dot.removeAttribute('hidden');
                })
                .catch(function (err) {
                    removeTyping();
                    appendMessage('bot', '&#9888; Gagal terhubung ke SAPA AI. Pastikan server Langflow berjalan di <code>' + escHtml(LANGFLOW_BASE_URL) + '</code>.<br><small style="color:#9aa9a2">(' + escHtml(err.message) + ')</small>');
                    status.textContent = 'Koneksi gagal';
                });
        }

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });
    })();
    </script>
</body>
</html>
