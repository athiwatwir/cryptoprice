<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CryptoV2Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MarketPriceApiController extends Controller
{
    public function index()
    {
        $result = CryptoV2Helper::binanceSnapshotPrice();

        return response()->json([
            'status' => true,
            'message' => $result,
        ], 200);
    }
}
