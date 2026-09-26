<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" type="image/png" href="{{ asset('images/logosapa.png') }}">
	<title>Portal Petugas | SAPA</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="staff-login-page">
	<header class="staff-login-header">
		<a class="brand" href="{{ url('/') }}">
			<img class="brand-logo" src="{{ asset('images/logosapa.png') }}" alt="SAPA - Sistem Aspirasi dan Pengaduan Masyarakat">
		</a>
		<a class="citizen-back" href="{{ url('/') }}">&larr; Kembali ke layanan warga</a>
	</header>

	<main class="staff-login-shell">
		<section class="staff-login-intro">
			<div class="login-intro-kicker">
				<span class="intro-shield">&#10003;</span> SISTEM INTERNAL TERLINDUNGI
			</div>
			<h1>Kerja cepat,<br><em>warga terbantu.</em></h1>
			<p>Kelola laporan warga dengan teratur, transparan, dan penuh tanggung jawab melalui Portal Petugas SAPA.</p>
			<div class="security-points">
				<div>
					<span>&#128274;</span>
					<strong>Akses resmi</strong>
					<small>Khusus petugas terdaftar</small>
				</div>
				<div>
					<span>&#10003;</span>
					<strong>Data terlindungi</strong>
					<small>Terhubung secara aman</small>
				</div>
			</div>
			<div class="intro-line"></div>
			<small class="intro-footer">SAPA &middot; Sistem Aspirasi & Pengaduan Masyarakat</small>
		</section>

		<section class="staff-login-card">
			<div class="card-topline">
				<div class="login-icon">&#128100;</div>
				<span class="login-status"><i></i> Portal aktif</span>
			</div>
			<p class="eyebrow">AKSES DINAS</p>
			<h2>Selamat datang,<br>Petugas SAPA</h2>
			<p class="login-description">Masuk untuk melihat dan menindaklanjuti laporan warga.</p>

			@if ($errors->any())
				<div class="alert alert-error">{{ $errors->first() }}</div>
			@endif

			<form class="staff-login-form" method="POST" action="{{ route('staff.authenticate') }}">
				@csrf
				<label>
					Email kedinasan
					<span class="input-wrap">
						<span class="input-symbol">&#9993;</span>
						<input required name="email" value="{{ old('email') }}" type="email" placeholder="nama@kelurahan.go.id" autocomplete="email">
					</span>
				</label>
				<label>
					Kata sandi
					<span class="input-wrap">
						<span class="input-symbol">&#128274;</span>
						<input required name="password" type="password" placeholder="Masukkan kata sandi" autocomplete="current-password">
					</span>
				</label>
				<div class="login-options">
					<span class="help-link">Hubungi administrator jika lupa</span>
				</div>
				<button class="button button-primary staff-submit" type="submit">
					Masuk ke Portal Petugas <span>&rarr;</span>
				</button>
			</form>

			<div class="login-card-footer">
				<span>&#128274; Koneksi terenkripsi</span>
				<span>&#10003; Autentikasi internal</span>
			</div>
		</section>
	</main>
</body>
</html>
