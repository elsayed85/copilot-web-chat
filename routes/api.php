<?php

use App\Http\Controllers\ConversationController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('conversation', [ConversationController::class, 'handel'])->name('api.chat');
Route::any('get-languages', [ConversationController::class, 'getLanguages'])->name('api.get-languages');
Route::any('get-locale', [ConversationController::class, 'getLocale'])->name('api.get-locale');
Route::any('change-language', [ConversationController::class, 'changeLanguage'])->name('api.change-language');

Route::post('/auth/github', function () {
    $service = new \App\Services\Github();
    $auth = $service->auth();

    return response()->json($auth);
});

Route::post('/auth/github/check', function () {
    $service = new \App\Services\Github();
    $auth = $service->check();

    return response()->json([
        'authorized' => $auth,
    ]);
});
