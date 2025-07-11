<?php

use App\Helpers\CryptoDataHelper;
use App\Helpers\IndicatorHelper;
use App\Helpers\TrandHelper;
use App\Http\Controllers\BinanceIndicatorController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\MarketPriceController;

use Illuminate\Support\Facades\Route;
use NotificationChannels\Telegram\TelegramUpdates;

/*
Route::get('/', function () {
    //TrandHelper::calculator();
    return view('welcome');
});
*/

Route::controller(CryptoController::class)->group(function () {
    Route::get('/', 'index')->name('crypto.index');
});



Route::get('/indicator', function () {
    IndicatorHelper::calculator();
});




Route::controller(MarketPriceController::class)->group(function () {
    Route::get('marketprice/process', 'processJob')->name('marketprice.process');
    Route::get('marketprice/send-chart', 'sendChart')->name('marketprice.sendChart');


    Route::get('/marketprice/update', 'updatePrice')->name('marketprice.updatePrice');

    Route::get('/marketprice/update-trand', 'updateTrand')->name('marketprice.updateTrand');
    Route::get('/marketprice/indicator', 'indicator')->name('marketprice.indicator');

    Route::get('/marketprice/max', 'max')->name('marketprice.max');
});


Route::controller(BinanceIndicatorController::class)->group(function () {
    Route::get('/bn/pump', 'pump')->name('bn.pump');
});


Route::get('/_t', function () {
    // Response is an array of updates.
    $updates = TelegramUpdates::create()
        // (Optional). Get's the latest update. NOTE: All previous updates will be forgotten using this method.
        // ->latest()

        // (Optional). Limit to 2 updates (By default, updates starting with the earliest unconfirmed update are returned).
        ->limit(2)

        // (Optional). Add more params to the request.
        ->options([
            'timeout' => 0,
        ])
        ->get();

    dd($updates);
})->name('test');
