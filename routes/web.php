<?php

use App\Helpers\IndicatorHelper;
use App\Http\Controllers\MarketPriceController;
use Illuminate\Support\Facades\Route;
use NotificationChannels\Telegram\TelegramUpdates;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/indicator', function () {
    IndicatorHelper::calculator();
});


Route::controller(MarketPriceController::class)->group(function () {
    Route::get('/marketprice/update', 'updatePrice')->name('marketprice.updatePrice');
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
