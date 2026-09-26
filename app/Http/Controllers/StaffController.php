<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function login(): View
    {
        return view('staff.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt(array_merge($credentials, ['is_staff' => true]), $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi petugas tidak sesuai.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('staff.dashboard'));
    }

    public function dashboard(Request $request): View
    {
        $status = $request->query('status');
        $allowedStatuses = ['baru', 'diproses', 'selesai', 'ditolak'];
        $reportsQuery = Report::latest();

        if (in_array($status, $allowedStatuses, true)) {
            $reportsQuery->where('status', $status);
        } else {
            $status = 'semua';
        }

        return view('staff.dashboard', [
            'reports' => $reportsQuery->paginate(12)->withQueryString(),
            'statusCounts' => Report::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'activeStatus' => $status,
        ]);
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:baru,diproses,selesai,ditolak'],
            'staff_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $report->update(array_merge($validated, ['handled_by' => Auth::id()]));

        return back()->with('success', 'Tindak lanjut laporan berhasil disimpan.');
    }

    public function photo(Report $report)
    {
        abort_unless($report->photo_path && Storage::disk('public')->exists($report->photo_path), 404);

        return response()->file(Storage::disk('public')->path($report->photo_path));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login');
    }
}
