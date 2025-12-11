<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

Route::prefix('api')->group(function () {
    Route::apiResource('categories', CategoryController::class);
});


Route::get('/', function () {
    return view('welcome');
});
