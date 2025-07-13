<?php

namespace App\Http\Controllers;


use App\Helpers\IndicatorV2Helper;

use App\Jobs\CryptoProcessJob;


class MarketPriceController extends Controller
{
    public function processJob()
    {

        CryptoProcessJob::dispatch();

        return response()->json([
            'status' => true,
            'message' => "updatePrice successfully!",
        ], 200);
    }

    public function test()
    {
        IndicatorV2Helper::indicator();
    }
}
