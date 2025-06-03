<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/')->middleware('redirect.auth.guest');

// Contoh route yang dilindungi (hanya bisa diakses jika sudah login)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard'); // Buat Dashboard.vue
    })->name('dashboard');
});

require __DIR__.'/auth.php'; // Tambahkan baris ini