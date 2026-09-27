# 🏛️ SAPA - Sistem Aspirasi & Pengaduan Masyarakat

SAPA adalah platform digital (berbasis Laravel) untuk kelurahan yang memungkinkan warga menyampaikan laporan, keluhan, dan aspirasi secara langsung, cepat, dan transparan. Proyek ini dilengkapi dengan integrasi **AI Chatbot (SAPA AI)** bertenaga **Langflow**, yang mempermudah warga dalam mencari informasi dan melacak status laporan.

---

## ✨ Fitur Utama

- **📝 Laporan Cepat Tanpa Login**: Warga dapat membuat laporan lingkungan tanpa proses pendaftaran yang berbelit-belit.
- **🔍 Pelacakan Status Real-time**: Warga mendapatkan kode tiket unik (misal: `SAPA-AB12CD34`) untuk melacak proses penanganan.
- **🤖 SAPA AI Chatbot (Langflow Integration)**: Asisten cerdas di dalam website untuk menjawab FAQ, memberi tahu cara melapor, dan mengecek status tiket.
- **⚡ Token-Optimized AI Integration**: 
  - *Local Fast-Path*: Sapaan dasar (halo, terima kasih) dan cek kode tiket diproses 100% lokal oleh sistem tanpa memotong kuota token LLM.
  - *Caching*: Menghindari query identik berulang ke server AI.
  - *Proxy Security*: API Key aman disembunyikan di backend Laravel.
- **👮 Portal Petugas**: Dasbor untuk perangkat kelurahan untuk mengelola, memperbarui status, dan memberi catatan pada laporan warga.

---

## 🛠️ Teknologi yang Digunakan

- **Backend**: Laravel 10.x, PHP 8.x, MySQL
- **Frontend**: Blade Templating, Vanilla CSS (Custom Design System), JavaScript
- **AI Engine**: Langflow (REST API via `ChatbotController` Laravel Proxy)
- **IBM Bob IDE**

---

## 🚀 Panduan Instalasi (Local Development)

### 1. Kebutuhan Sistem
Pastikan sistem Anda telah menginstal:
- PHP >= 8.1
- Composer
- MySQL / MariaDB
- Node.js & NPM
- [Langflow](https://github.com/langflow-ai/langflow) (Untuk fitur SAPA AI)

### 2. Kloning & Setup Proyek
```bash
git clone https://github.com/username/sapa-laravel.git
cd sapa-laravel

# Install dependensi PHP & Node
composer install
npm install

# Buat file konfigurasi environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Konfigurasi Database & AI (.env)
Buka file `.env` lalu sesuaikan koneksi database MySQL Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_sapa
DB_USERNAME=root
DB_PASSWORD=
```
Tambahkan juga kredensial Langflow Anda di bagian bawah file `.env`:
```env
LANGFLOW_BASE_URL=http://127.0.0.1:7860
LANGFLOW_FLOW_ID=masukkan-flow-id-langflow-anda
LANGFLOW_API_KEY=YOUR_API_KEY_HERE
```

### 4. Migrasi & Build
```bash
# Jalankan migrasi database
php artisan migrate

# Build asset frontend (CSS/JS)
npm run build
```

### 5. Jalankan Aplikasi
```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Langflow Server (Pastikan terinstall)
langflow run
```
Akses aplikasi melalui browser di: `http://localhost:8000`

---

## 💡 Arsitektur Integrasi SAPA AI (Langflow)

Integrasi Chatbot pada proyek ini **tidak menggunakan MCP** langsung di frontend, melainkan menggunakan **REST API Proxy** melalui Laravel.

1. **Frontend Request**: JS di `welcome.blade.php` menangkap ketikan user.
2. **Laravel Proxy (`ChatbotController`)**:
   - Jika pesan berisi sapaan sederhana atau cek resi (`SAPA-XXXXX`), Laravel langsung membalas (*Fast Path* -> 0 Token LLM).
   - Jika tidak, request diteruskan ke Langflow endpoint `/api/v1/run/{flow_id}` beserta `x-api-key`.
3. **Langflow Server**: Memproses AI logic dan mengembalikan JSON.
4. **Frontend Response**: Pesan di-render dengan format HTML (mendukung cetak tebal & baris baru).

---

## 🛡️ Keamanan & Optimasi
- **CORS Aman & API Key Tersembunyi**: Browser tidak pernah mengirim kunci API langsung ke server Langflow.
- **CSRF Protected**: Pengiriman pesan dari web dilindungi dengan verifikasi token standar Laravel.
- **Prompt Compression**: Spasi berlebih dikompres dan input dibatasi maksimal 500 karakter untuk menghemat _API Calls_ AI.

---
*Dibuat untuk mendekatkan jarak antara Warga dan Perangkat Kelurahan.*
