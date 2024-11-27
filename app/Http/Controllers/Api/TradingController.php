<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BinanceService;

class TradingController extends Controller
{
    protected $binanceService;

    public function __construct(BinanceService $binanceService)
    {
        $this->binanceService = $binanceService;
    }

    public function openPosition(Request $request)
    {
        $symbol = $request->input('symbol', 'BTCUSDT');
        $quantity = $request->input('quantity', 0.001);
        $leverage = $request->input('leverage', 10);

        $response = $this->binanceService->openLongPosition($symbol, $quantity, $leverage);

        return response()->json($response);
    }
}
