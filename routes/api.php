<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(["middleware" => ['logHttpRequest:user_auth_api'], "prefix" => "auth"], function () {});
Route::group(["middleware" => ['auth:api', 'logHttpRequest:user_api']], function () {});
