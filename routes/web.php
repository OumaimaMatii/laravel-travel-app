<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VoyageController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\Api\ActiviteController;
use App\Http\Controllers\Api\VilleController;

Route::get('/login', function () {
    return response()->json(['message' => 'Non authentifié'], 401);
})->name('login');