<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EarthquakeController;

Route::get('/', [EarthquakeController::class, 'index']);
Route::get('/earthquakes/data', [EarthquakeController::class, 'getData']);

use App\Http\Controllers\Admin\SourceController;
use App\Http\Controllers\Admin\SourceTypeController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [SourceController::class, 'index'])->name('dashboard');

    Route::get('/admin/sources/create', [SourceController::class, 'create'])->name('admin.sources.create');
    Route::post('/admin/sources', [SourceController::class, 'store'])->name('admin.sources.store');
    Route::get('/admin/sources/{source}/edit', [SourceController::class, 'edit'])->name('admin.sources.edit');
    Route::patch('/admin/sources/{source}', [SourceController::class, 'update'])->name('admin.sources.update');
    Route::post('/admin/sources/{source}/toggle', [SourceController::class, 'toggle'])->name('admin.sources.toggle');

    Route::resource('/admin/source-types', SourceTypeController::class)->except(['show'])->names('admin.source-types');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
