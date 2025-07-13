<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use App\Notifications\IndicatorNotification;
use Illuminate\Support\Facades\Http;

class TelegramV2Helper
{
    public static $token = '5684645252:AAE-yYoJAo0GPwvjvmDA-Y2GF72gVYE6Vts';
    public static $chatId = '@cryptopumpdumpnotis';

    public static function sendLong($coin, $amount, $count, $bidArk = 0)
    {
        $url = 'https://www.binance.com/en/futures/' . $coin . '?_from=markets';

        $msg = "[" . $count . "]🟢 LONG #" . $coin . ' up ' . $amount . '%  BidArk: ' . $bidArk . "\n";
        $msg .= '<a href="' . $url . '">Open Binance</a>';

        TelegramV2Helper::sendTelegramMessage($msg);
    }




    public static function sendTelegramMessage($message)
    {
        $botToken = TelegramV2Helper::$token;
        $chatId = TelegramV2Helper::$chatId;

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $response = Http::post($url, [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML', // ใช้ HTML เพื่อส่ง URL ได้
            'disable_web_page_preview' => true, // ปิด/เปิด preview url
        ]);

        //dd($response->json());
    }




    public static function sendShort($coin, $amount, $count)
    {
        $data = [
            'telegram_user_id' => '@cryptopumpdumpnotis',
            'isLong' => false,
            'coin' => $coin,
            'amount' => $amount,
            'count' => $count,
        ];
        Notification::route('telegram', env('TELEGRAM_CHAT_ID'))->notify(new IndicatorNotification($data));
    }

    public static function sendCryptoChartToTelegram($symbol = 'BTCUSDT')
    {
        $interval = '1h';

        // ดึงข้อมูลกราฟจาก Binance
        $response = Http::get("https://api.binance.com/api/v3/klines", [
            'symbol' => $symbol,
            'interval' => $interval,
            'limit' => 80,
        ]);

        $klines = $response->json();

        //dd($klines);

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
        $botToken = TelegramV2Helper::$token;
        $chatId = TelegramV2Helper::$chatId;
        //$url = "https://api.telegram.org/bot$token/sendPhoto";

        Http::post("https://api.telegram.org/bot" . $botToken . "/sendPhoto", [
            'chat_id' => $chatId,
            'photo' => $chartUrl,
            'caption' => "กราฟ $symbol รายชั่วโมง (1H)"
        ]);
    }
}
