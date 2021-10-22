<?php

use Illuminate\Support\Facades\Route;
use Deegitalbe\TrustupProAdminCommon\Http\Controllers\PackageController;

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
|
*/

// Project package version
Route::get('version', [PackageController::class, 'version'])->name('version');