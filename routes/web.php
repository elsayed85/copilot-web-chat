<?php

use Illuminate\Support\Facades\Route;
use OpenAI\Laravel\Facades\OpenAI;

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
Route::get('test', function () {
    $stream = OpenAI::completions()->createStreamed([
        'model' => 'text-davinci-003',
        'prompt' => 'This is a test',
        'max_tokens' => 1024,
    ]);
});

Route::get('/{chat?}/{conversation_id?}', function () {
    $service = new \App\Services\CopilotApi();
    $hasGithubToken = $service->hasGithubToken();

    return view('home', [
        'hasGithubToken' => $hasGithubToken,
    ]);
});

Route::post('logout', function () {
    $service = new \App\Services\CopilotApi();
    $service->logout();

    return redirect('/');
})->name('logout');
