<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ListingController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/listings', [ListingController::class, 'index']);
//Route::post('/register', [AuthController::class, 'register']);
//Route::post('/login', [AuthController::class, 'login']);
Route::post('/dashboard', [AuthController::class, 'logout']);
Route::post('/add-property', [PropertyController::class, 'addProperty']);

Route::get('/address-suggestions', [ListingController::class, 'addressSuggestions']);
Route::post('/listings', [ListingController::class, 'store']);
Route::post('/listings/check-address', [ListingController::class, 'checkAddress']);
Route::get('/listings', [ListingController::class, 'index']);
Route::post('/check-address', [ListingController::class, 'checkAddress']);
Route::post('/logout', [AuthController::class, 'logout']);

