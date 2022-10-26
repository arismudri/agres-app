<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Product\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post("register", [AuthenticationController::class, "register"]);
Route::post("login", [AuthenticationController::class, "login"]);

Route::group(["middleware" => ["jwt.verify"]], function () {
    Route::get("logout", [AuthenticationController::class, "logout"]);
    Route::get("user-info", [AuthenticationController::class, "userInfo"]);

    Route::group(["middleware" => ["jwt.verify"], "prefix" => "product"], function () {
        Route::get("", [ProductController::class, "index"]);
        Route::get("{id}", [ProductController::class, "show"]);
        Route::post("", [ProductController::class, "store"]);
        Route::put("{id}", [ProductController::class, "update"]);
        Route::delete("{id}", [ProductController::class, "destroy"]);
    });
});
