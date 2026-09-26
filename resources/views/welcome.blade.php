<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPA | Layanan Warga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ url('/') }}" aria-label="SAPA Beranda">
            <span class="brand-mark">S</span>
            <span><strong>SAPA</strong><small>Sistem Aspirasi & Pengaduan Masyarakat</small></span>
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
            <div class="hero-visual" aria-label="Ilustrasi warga menyampaikan aspirasi"><div class="visual-circle circle-back"></div><div class="visual-circle circle-front"></div><div class="visual-card card-message"><span>&#128172;</span><strong>Aspirasi Anda</strong><small>sedang didengar</small></div><div class="visual-card card-check"><span>&#10003;</span><strong>Mudah & Aman</strong><small>untuk semua warga</small></div><div class="person-shape"><div class="person-head"></div><div class="person-body"></div><div class="person-arm"></div></div><div class="visual-label">Suara warga, perubahan nyata.</div></div>
        </section>
        <section class="service-section" id="cara-kerja">
            <div class="section-heading"><p class="eyebrow">SATU PLATFORM UNTUK SEMUA</p><h2>Layanan yang dekat dengan Anda</h2><p>Mulai dari laporan sederhana hingga aspirasi untuk lingkungan yang lebih baik.</p></div>
            <div class="service-grid">
                <article class="service-card featured-card"><div class="service-icon green-icon">&#9993;</div><div><h3>Laporan Cepat</h3><p>Laporkan masalah di lingkungan Anda tanpa akun dan tanpa proses yang berbelit.</p><a href="{{ route('reports.create') }}">Mulai melapor <span>&rarr;</span></a></div></article>
                <article class="service-card"><div class="service-icon blue-icon">&#128203;</div><div><h3>Pantau Laporan</h3><p>Lihat perkembangan laporan yang pernah Anda sampaikan dengan kode laporan.</p><span class="muted-link">Segera hadir</span></div></article>
                <article class="service-card"><div class="service-icon orange-icon">&#9733;</div><div><h3>Aspirasi Warga</h3><p>Bagikan ide dan masukan untuk membuat kelurahan kita semakin nyaman.</p><span class="muted-link">Segera hadir</span></div></article>
            </div>
        </section>
    </main>
    <footer class="site-footer"><span><b>SAPA</b> untuk kelurahan yang tanggap dan melayani.</span><span>&copy; {{ date('Y') }} Pemerintah Kelurahan</span></footer>
</body>
</html>
