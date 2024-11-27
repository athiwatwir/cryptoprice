<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class BinanceIndicatorController extends Controller
{
    protected $base_url = "https://fapi.binance.com";

    public function pump(){
        // 1. ดึงคู่เทรด Futures ทั้งหมด
        $exchange_info_url = "{$this->base_url}/fapi/v1/exchangeInfo";
        $exchangeInfo = Http::get($exchange_info_url);

        if ($exchangeInfo->failed()) {
            return response()->json(['error' => 'Failed to fetch exchange info'], 500);
        }

        $symbols = collect($exchangeInfo->json()['symbols'])->pluck('symbol');
       // return response()->json($symbols);

        // 2. ดึงข้อมูลราคาย้อนหลัง 5 นาทีสำหรับทุกคู่เทรด
        $kline_url = "{$this->base_url}/fapi/v1/klines";
        $prices = [];

        foreach ($symbols as $symbol) {
            $response = Http::get($kline_url, [
                'symbol' => $symbol,
                'interval' => '5m', // แท่งเทียน 5 นาที
                'limit' => 3        // ดึงข้อมูลล่าสุดเท่านั้น
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $prices[$symbol] = [
                    'open_time' => $data[0][0],
                    'open' => $data[0][1],
                    'high' => $data[0][2],
                    'low' => $data[0][3],
                    'close' => $data[0][4],
                    'volume' => $data[0][5],
                ];
            }

            return response()->json($data);

            // หน่วงเวลาการดึงข้อมูล เพื่อป้องกัน Rate Limit
            usleep(100000); // 100ms
        }

        // 3. แสดงข้อมูล
        return response()->json($prices);


    }
}
