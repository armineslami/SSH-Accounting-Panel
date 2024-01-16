<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
Route::post('/<token>/webhook', TelegramController::class)->name('telegram');


