<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StaffController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/laporan-cepat', function () {
    return app(ReportController::class)->create();
})->name('reports.create');

Route::post('/laporan-cepat', [ReportController::class, 'store'])->name('reports.store');

Route::get('/portal-petugas', function () {
    return app(StaffController::class)->login();
})->name('staff.login');
Route::post('/portal-petugas', [StaffController::class, 'authenticate'])->name('staff.authenticate');

Route::middleware(['auth', 'staff'])->prefix('petugas')->name('staff.')->group(function () {
    Route::get('/laporan', [StaffController::class, 'dashboard'])->name('dashboard');
    Route::get('/laporan/{report}/foto', [StaffController::class, 'photo'])->name('reports.photo');
    Route::patch('/laporan/{report}', [StaffController::class, 'update'])->name('reports.update');
    Route::post('/logout', [StaffController::class, 'logout'])->name('logout');
});
