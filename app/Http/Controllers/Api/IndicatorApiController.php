<?php

namespace App\Http\Controllers\Api;


use App\Helpers\IndicatorV2Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndicatorApiController extends Controller
{
    public function scanPump()
    {
        sleep(5);
        $result = IndicatorV2Helper::scanPumpCandidates(60);

        return response()->json([
            'status' => true,
            'message' => '',
        ], 200);
    }
}
