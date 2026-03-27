<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ScraperController;

Route::get('/', [ScraperController::class, 'index'])->name('home');
Route::post('/scrape', [ScraperController::class, 'scrape'])->name('scrape');
