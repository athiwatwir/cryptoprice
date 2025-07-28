<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use App\Models\MarketPrices;
use App\Models\Notifications;
use Carbon\Carbon;
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

    public static function scanPumpCandidates(int $limit = 5, float $priceChangeThreshold = 1.7, float $bidAskThreshold = 0.6): array
    {
        $result = [];
        $now = Carbon::now()->format('Y-m-d H:i');

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

                TelegramV2Helper::sendLong($symbol, round($percentChange, 2), 0, round($bidAskRatio, 2));
                TelegramV2Helper::sendCryptoChartToTelegram($symbol);
                //}
            }


            $result[] = [
                'symbol' => $symbol,
                'change_percent' => round($percentChange, 2),
                'price_now' => $latest,
                'price_prev' => $prev,
                'max_price' => $maxPrice,
            ];
        }

        //dd($result);

        //Storage::disk('local')->put('scanPumpCandidates/' . $now . '.json', json_encode($result, JSON_PRETTY_PRINT));
        return $result;
    }
}
