<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\SearchController;

Route::get('/search', [
    SearchController::class,
    'index',
]);

Route::post('/contents', [
    ContentController::class,
    'store',
]);

Route::put('/contents/{id}', [
    ContentController::class,
    'update',
]);

Route::delete('/contents/{id}', [
    ContentController::class,
    'destroy',
]);