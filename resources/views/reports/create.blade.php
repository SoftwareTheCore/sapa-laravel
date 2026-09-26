<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Buat Laporan | SAPA</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body>
<header class="site-header"><a class="brand" href="{{ url('/') }}"><span class="brand-mark">S</span><span><strong>SAPA</strong><small>Sistem Aspirasi & Pengaduan Masyarakat</small></span></a><a class="staff-link" href="{{ route('staff.login') }}"><span class="staff-icon">&#128100;</span>Portal Petugas</a></header>
<main class="form-shell"><p class="eyebrow">LAPORAN CEPAT WARGA</p><h1>Sampaikan laporan Anda</h1><p class="form-intro">Tidak perlu login. Nomor telepon wajib diisi agar petugas dapat menghubungi Anda terkait laporan.</p>
@if (session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
<script>
	window.addEventListener('DOMContentLoaded', function () {
		Swal.fire({
			icon: 'success',
			title: 'Laporan diterima',
			text: @json(session('success')),
			confirmButtonText: 'Mengerti',
			confirmButtonColor: '#267a45',
			showClass: { popup: 'swal2-show' },
		});
	});
</script>
@endif
@if ($errors->any())<div class="alert alert-error"><strong>Periksa kembali data laporan:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="report-form" method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data">
@csrf
<label>Nama<input name="citizen_name" value="{{ old('citizen_name') }}" type="text" placeholder="Nama Anda"></label>
<label>Nomor telepon<input required name="citizen_phone" value="{{ old('citizen_phone') }}" type="tel" inputmode="tel" placeholder="Contoh: 0812 3456 7890"><small class="field-help">Gunakan nomor yang aktif dan dapat dihubungi petugas.</small></label>
<label>Judul laporan<input required name="title" value="{{ old('title') }}" type="text" placeholder="Contoh: Lampu jalan mati di depan balai warga"></label>
<label>Kategori<select required name="category"><option value="">Pilih kategori laporan</option>@foreach (['Infrastruktur','Kebersihan','Keamanan','Layanan publik','Lainnya'] as $category)<option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>@endforeach</select></label>
<label>Ceritakan laporan Anda<textarea required name="description" rows="5" placeholder="Jelaskan kondisi yang ingin dilaporkan">{{ old('description') }}</textarea></label>
<label>Lokasi kejadian<input required name="location" value="{{ old('location') }}" type="text" placeholder="Alamat atau patokan lokasi"></label>
<label>Foto pendukung (opsional)<input name="photo" type="file" accept="image/jpeg,image/png,image/webp"><small class="field-help">Format JPG, PNG, atau WEBP. Maksimal 5 MB.</small></label>
<button class="button button-primary" type="submit">Kirim laporan <span>&rarr;</span></button>
</form></main></body>
</html>
