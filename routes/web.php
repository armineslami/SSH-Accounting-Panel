<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/inbounds/create', [InboundController::class, 'create'])->name('inbounds.create');
    Route::get('/inbounds/search/', [InboundController::class, 'search'])->name('inbounds.search');
    Route::get('/inbounds/{id?}', InboundController::class)->name('inbounds.index');
    Route::post('/inbounds', [InboundController::class, 'store'])->name('inbounds.store');
    Route::patch('/inbounds/{id}', [InboundController::class, 'update'])->name('inbounds.update');
    Route::delete('/inbounds/{id}', [InboundController::class, 'destroy'])->name('inbounds.destroy');

    Route::get('/servers/create', [ServerController::class, 'create'])->name('servers.create');
    Route::get('/servers/{id?}', ServerController::class)->name('servers.index');
    Route::post('/servers', [ServerController::class, 'store'])->name('servers.store');
    Route::patch('/servers/{id}', [ServerController::class, 'update'])->name('servers.update');
    Route::delete('/servers/{id}', [ServerController::class, 'destroy'])->name('servers.destroy');

    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [SettingController::class, 'update'])->name('settings.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
