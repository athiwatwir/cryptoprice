<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use App\Models\MarketPrices;
use App\Models\Notifications;
use Illuminate\Support\Facades\Http;


class CryptoDataHelper
{
    public static $TYPE = 'BI';

    public static function getPrices($fillterCoins = [])
    {
        $types = [CryptoDataHelper::$TYPE];

        foreach ($types as $a => $type) {
            $prices = MarketPrices::select('price')->where('type', $type)->orderBy('created_at', 'DESC')->limit(1440)->get();
            $coins = [];
            $data['sizeof calculate'] = sizeof($prices);

            foreach ($prices as $index => $row) {


                $jsondata = $row['price'];
                if (!is_null($jsondata) && $jsondata != '') {
                    $jsondata = json_decode($jsondata, true);

                    foreach ($jsondata as $index2 => $d) {
                        if (sizeof($fillterCoins) > 0) {
                            if (!in_array($d['symbol'], $fillterCoins)) {
                                continue;
                            }
                        }
                        $coins[$d['symbol']][$index] = (float) $d['markPrice'];
                    }
                }
            }

            return ($coins);
        }
    }

    public static function sendCryptoChartToTelegram($symbol = 'BTCUSDT')
    {
        $interval = '1h';

        // ดึงข้อมูลกราฟจาก Binance
        $response = Http::get("https://api.binance.com/api/v3/klines", [
            'symbol' => $symbol,
            'interval' => $interval,
            'limit' => 50,
        ]);

        $klines = $response->json();

        // วาดกราฟ (เช่นใช้ chart.js, QuickChart API หรือวาดด้วย PHP GD)
        $chartUrl = "https://quickchart.io/chart?version=3&c=" . urlencode(json_encode([
            'type' => 'candlestick',
            'data' => [
                'datasets' => [
                    [
                        'label' => $symbol,
                        'data' => array_map(function ($kline) {
                            return [
                                'x' => $kline[0],
                                'o' => (float)$kline[1],
                                'h' => (float)$kline[2],
                                'l' => (float)$kline[3],
                                'c' => (float)$kline[4],
                            ];
                        }, $klines),
                    ]
                ]
            ]
        ]));

        //dd($chartUrl);

        // ส่งภาพไป Telegram
        $token = '5684645252:AAE-yYoJAo0GPwvjvmDA-Y2GF72gVYE6Vts';
        $chatId = '@cryptopumpdumpnotis';
        //$url = "https://api.telegram.org/bot$token/sendPhoto";

        Http::post("https://api.telegram.org/bot" . $token . "/sendPhoto", [
            'chat_id' => $chatId,
            'photo' => $chartUrl,
            'caption' => "กราฟ $symbol รายชั่วโมง (1H)"
        ]);
    }
}
