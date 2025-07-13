<?php

use App\Http\Controllers\Api\IndicatorApiController;
use App\Http\Controllers\Api\MarketPriceApiController;
use App\Http\Controllers\Api\TradingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/open-position', [TradingController::class, 'openPosition']);

Route::get('market-price', [MarketPriceApiController::class, 'index']);
Route::get('indicator/scan-pump', [IndicatorApiController::class, 'scanPump']);
