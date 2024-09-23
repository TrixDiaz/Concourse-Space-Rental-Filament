<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\PolicyController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/terms', [TermsController::class, 'show'])->name('terms.show');
Route::get('/policy', [PolicyController::class, 'show'])->name('policy.show'); 