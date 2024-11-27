<?php

namespace App\Services;

use Binance\API;

class BinanceService
{
    protected $binance;

    public function __construct()
    {
        $apiKey = env('BINANCE_API_KEY');
        $apiSecret = env('BINANCE_API_SECRET');
        $this->binance = new API($apiKey, $apiSecret);

        // ตั้งค่า Testnet Endpoint
        $this->binance->futuresTestnet(true);
        $this->binance->useServerTime();
    }

    // เปิด Long Position
    public function openLongPosition($symbol, $quantity, $leverage)
    {
        try {
            // ตั้ง Leverage
            $this->binance->futuresLeverage($symbol, $leverage);

            // เปิด Long Position
            $order = $this->binance->futuresOrder(
                "BUY", // เปิด Long
                $symbol,
                $quantity,
                "MARKET" // ใช้ Market Order
            );

            return $order;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
