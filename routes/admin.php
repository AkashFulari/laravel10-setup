<?php

use App\Http\Controllers\GlobalController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ['logHttpRequest:admin_auth_api'], "prefix" => "auth"], function () {});
Route::group(["middleware" => ['auth:api', 'logHttpRequest:admin_api']], function () {});

Route::get("getLogs", [GlobalController::class, "GetLogs"]);
Route::get("resetLogs", [GlobalController::class, "ResetLogs"]);
Route::get("downloadLogs", [GlobalController::class, "DownloadLogs"]);
Route::get("refreshLogs", [GlobalController::class, "RefreshLogs"]);
Route::get("checkLogList", [GlobalController::class, "CheckLogList"]);
