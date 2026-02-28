<?php

use App\Http\Controllers\DocsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/docs', [DocsController::class, 'redirect'])->name('docs');
Route::get('/docs/{page}', [DocsController::class, 'show'])->name('docs.show')
    ->where('page', '[a-z0-9\-]+');
