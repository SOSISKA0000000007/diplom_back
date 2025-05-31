<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\MessageController;

Route::get('/listings/top', [ListingController::class, 'topListings']);

Route::middleware('auth:api')->group(function () {
    Route::get('/listings/my', [ListingController::class, 'myListings']);
});
Route::middleware('auth:api')->group(function () {
    Route::put('/listings/{id}', [ListingController::class, 'update']);
});
Route::middleware('auth:api')->group(function () {
    Route::post('/listings/{id}/favorite', [ListingController::class, 'addToFavorites']);
    Route::delete('/listings/{id}/favorite', [ListingController::class, 'removeFromFavorites']);
    Route::get('/favorites', [ListingController::class, 'getFavorites']);
});
Route::middleware('auth:api')->group(function () {
    Route::get('/listings/{id}', [ListingController::class, 'show']);
    Route::delete('/listings/{id}', [ListingController::class, 'destroy']); // Новый маршрут для удаления
});



Route::get('/listings/{id}', [ListingController::class, 'show']);


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/listings', [ListingController::class, 'index']);
Route::post('/listings', [ListingController::class, 'store']);
Route::get('/listings/my', [ListingController::class, 'myListings']);
Route::post('/listings/{id}/archive', [ListingController::class, 'archive']);
Route::post('/add-property', [PropertyController::class, 'addProperty']);
Route::get('/address-suggestions', [ListingController::class, 'addressSuggestions']);
Route::post('/listings/check-address', [ListingController::class, 'checkAddress']);

