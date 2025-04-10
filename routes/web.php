<?php

use App\Http\Controllers\CVController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('cvs.index');
});

Route::get('/cvs', [CVController::class, 'index'])->name('cvs.index');
Route::post('/cvs', [CVController::class, 'store'])->name('cvs.store');
Route::delete('/cvs/{cv}', [CVController::class, 'destroy'])->name('cvs.destroy');
Route::get('/test-hf', function () {
    $response = \Illuminate\Support\Facades\Http::withHeaders([
        'Authorization' => 'Bearer ' . env('HUGGINGFACE_API_KEY'),
    ])->post('https://api-inference.huggingface.co/models/facebook/bart-large-cnn', [
        'inputs' => 'Laravel is a PHP framework used to build web applications. It offers clean syntax and modern tools.',
    ]);

    return $response->json();
});