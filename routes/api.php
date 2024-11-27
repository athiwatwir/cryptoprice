<?php

use App\Http\Controllers\Api\TradingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/open-position', [TradingController::class, 'openPosition']);
