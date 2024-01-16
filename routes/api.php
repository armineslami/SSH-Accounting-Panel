<?php

use App\Telegram\Buttons\Buttons;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

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

Route::post('/<token>/webhook', function () {
//    $updates = Telegram::commandsHandler(true);
    $updates = Telegram::getWebhookUpdate();

    $command = $updates->message->text;

    if ($command === "/start") {
        Telegram::triggerCommand("start", $updates);
    }
    elseif (
        $command === Buttons::LOGIN ||
        $command === "/login" ||
        preg_match('/^([a-zA-Z0-9]+):([a-zA-Z0-9!@#$%^&*()\-=_+{}|:"<>?\[\]\\\;\',.\/]+)$/', $command)) {
        Telegram::triggerCommand("login", $updates);
    }
    else {
        Telegram::triggerCommand("help", $updates);
    }
});
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');


