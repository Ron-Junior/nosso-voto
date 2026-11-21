<?php

use App\Http\Controllers\Api\InspirationClipController;
use Illuminate\Support\Facades\Route;

Route::match(['POST', 'OPTIONS'], '/v1/inspirations/clip', InspirationClipController::class)
    ->name('api.inspirations.clip');

