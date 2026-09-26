<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function create(): View
    {
        return view('reports.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'citizen_name' => ['nullable', 'string', 'max:120'],
            'citizen_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+() .-]+$/'],
            'citizen_email' => ['nullable', 'email', 'max:150'],
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'in:Infrastruktur,Kebersihan,Keamanan,Layanan publik,Lainnya'],
            'description' => ['required', 'string', 'max:5000'],
            'location' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $validated['tracking_code'] = 'SAPA-' . strtoupper(Str::random(8));
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('reports', 'public');
        }

        Report::create($validated);

        return redirect()->route('reports.create')->with('success', "Laporan berhasil dikirim. Kode laporan Anda: {$validated['tracking_code']}");
    }
}
