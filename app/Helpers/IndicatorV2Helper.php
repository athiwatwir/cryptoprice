<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use App\Models\MarketPrices;
use App\Models\Notifications;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NotificationChannels\Telegram\Telegram;

class IndicatorV2Helper
{
    public static function indicator()
    {
        IndicatorV2Helper::scanPumpCandidates(60);
    }

    public static function isUptrend(array $prices)
    {
        if (count($prices) < 15) return false;

        $startPrice = $prices[0]['price'];
        $endPrice = $prices[14]['price'];
        $pctChange = ($endPrice - $startPrice) / $startPrice * 100;

        $upCandles = 0;
        for ($i = 1; $i < 15; $i++) {
            if ($prices[$i]['price'] > $prices[$i - 1]['price']) {
                $upCandles++;
            }
        }

        $highPrice = max(array_column($prices, 'price'));

        return $pctChange > 5 && $upCandles >= 3 && $endPrice >= $highPrice;
    }

    public static function scanPumpCandidates(int $limit = 5, float $priceChangeThreshold = 2.0, float $bidAskThreshold = 0.6): array
    {
        $result = [];

        // ดึงราคาย้อนหลัง เช่น 5 แท่งล่าสุด
        $prices = CryptoV2Helper::getPrices($limit);
        //dd($prices);
        foreach ($prices as $symbol => $series) {
            if (count($series) < 6) continue;

            $prev = $series[3];
            $latest = $series[0];
            unset($series[0]);
            unset($series[0]);
            $maxPrice = max($series);

            // คำนวณ % การเปลี่ยนแปลง
            $percentChange = (($latest - $prev) / $prev) * 100;

            if (($percentChange >= $priceChangeThreshold) && ($maxPrice < $latest)) {
                // ไปดู Order Book ว่ามีแรงซื้อมั้ย
                $bidAskRatio = CryptoV2Helper::binanceBidsArks($symbol);

                //if ($bidAskRatio >= $bidAskThreshold) {

                $result[] = [
                    'symbol' => $symbol,
                    'change_percent' => round($percentChange, 2),
                    'bid_ask_ratio' => round($bidAskRatio, 2),
                    'price_now' => $latest,
                    'price_prev' => $prev,
                ];

                TelegramV2Helper::sendCryptoChartToTelegram($symbol);
                TelegramV2Helper::sendLong($symbol, round($percentChange, 2), 0, round($bidAskRatio, 2));
                //}
            }
        }

        //dd($result);

        return $result;
    }
}
