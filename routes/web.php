<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ScraperController;
use Illuminate\Support\Facades\DB;

Route::get('/', [ScraperController::class, 'index'])->name('home');
Route::post('/scrape', [ScraperController::class, 'scrape'])->name('scrape');

Route::get('/scrape-status', function () {
    return response()->json([
        'jobs_count' => DB::table('jobs')->count()
    ]);
})->name('scrape.status');
