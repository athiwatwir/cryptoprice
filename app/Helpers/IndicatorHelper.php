<?php

namespace App\Helpers;

use App\Models\MarketPrices;
use App\Models\Notifications;
use App\Notifications\IndicatorNotification;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class IndicatorHelper
{
    public static $stupidCoins = ['XEMUSDT'];
    public static function calculator()
    {
        $types = [CryptoDataHelper::$TYPE];
        $data = [];


        foreach ($types as $a => $type) {
            $prices = MarketPrices::select('price')->where('type', $type)->orderBy('created_at', 'DESC')->limit(60)->get();
            $coins = [];
            $data['sizeof calculate'] = sizeof($prices);

            foreach ($prices as $index => $row) {

                $jsondata = $row['price'];
                if (!is_null($jsondata) && $jsondata != '') {
                    $jsondata = json_decode($jsondata, true);

                    foreach ($jsondata as $index2 => $d) {
                        $coins[$d['symbol']][$index] = (float) $d['markPrice'];
                    }
                }
            }

            $sentCount = 0;
            foreach ($coins as $coin => $prices) {
                if (in_array($coin, IndicatorHelper::$stupidCoins)) {
                    continue;
                }

                $priceCalculcateMaxArr = $prices;
                unset($priceCalculcateMaxArr[0]);
                unset($priceCalculcateMaxArr[1]);

                $maxPrice = max($priceCalculcateMaxArr);
                $minPrice = min($priceCalculcateMaxArr);

                $changePercent = round((($prices[0] - $prices[2]) / $prices[2]) * 100, 2);

                if ($changePercent >= 1 && $prices[0] >= $maxPrice) {
                    if ($sentCount > 10) {
                        break;
                    }

                    $lastPrice = $prices[0];
                    $failedCount = 0;
                    $loopCount = 0;

                    foreach ($prices as $p) {
                        if ($lastPrice >= $p) {
                        } else {
                            $failedCount++;
                        }

                        $loopCount++;
                        if ($loopCount > 3) {
                            break;
                        }
                    }

                    if ($failedCount <= 1) {
                        $notify = IndicatorHelper::notificationLog($coin, true);
                        if ($notify['isnotify']) {
                            $bidArk = 0;
                            $url = '';
                            if ($type == 'BI') {
                                $bidArk = IndicatorHelper::binanceBidsArks($coin);
                                $url = 'https://www.binance.com/en/futures/' . $coin . '?_from=markets';
                            } else {
                            }

                            IndicatorHelper::sendLong($coin, $changePercent, $notify['count'], $bidArk, $url);
                            CryptoDataHelper::sendCryptoChartToTelegram($coin);
                            $sentCount++;
                        }
                    }
                } elseif ($changePercent <= -1 && ($prices[0] < $minPrice)) {
                    /*
                    if($sentCount > 5){
                        break;
                    }

                    $lastPrice = $prices[0];
                    $failedCount = 0;
                    $loopCount = 0;
                    foreach($prices as $p){
                        if(!($p >=$lastPrice)){
                            $failedCount++;
                        }
                        $lastPrice = $p;
                        $loopCount++;

                        if($loopCount >5){
                            break;
                        }
                    }

                    if($failedCount <= 2){
                        $notify = IndicatorHelper::notificationLog($coin, false);
                        if ($notify['isnotify']) {
                            IndicatorHelper::sendShort($coin, $changePercent, $notify['count']);
                            //CryptoDataHelper::sendCryptoChartToTelegram($coin);
                            $sentCount++;
                        }
                    }
                        */
                }
            }
        }

        return $data;
    }

    public static function notificationLog($coin, $isLong = true)
    {
        $toDaydate = date('Y-m-d');
        $_code = sprintf('%s_%s_%s', $toDaydate, ($isLong ? 'LONG' : 'SHOT'), $coin);

        $notificationLog = Notifications::where('code', $_code)->first();
        $count = 1;
        if (is_null($notificationLog)) {
            $notificationLog = Notifications::create(
                [
                    'code' => $_code,
                    'name' => $coin,
                    'count' => $count,
                    'type' => 'PD'
                ]
            );
        } else {
            $count = $notificationLog->count;
            $count++;
            Notifications::where('code', $_code)->update(
                [
                    'count' => $count,
                ]
            );
        }

        if ($count == 1) {
            return [
                'count' => $count,
                'isnotify' => true,
                'diffSecond' => 0
            ];
        } else {
            $newTime = now()->diffInSeconds($notificationLog->updated_at);

            $diffSecond = $newTime;
            //Log::debug($diffSecond / 60);

            return [
                'count' => $count,
                'isnotify' => ($diffSecond / 60 <= -60) ? true : false,
                'diffSecond' => $diffSecond
            ];
        }
    }

    public static function sendLong($coin, $amount, $count, $bidArk = 0, $url = '')
    {
        $data = [
            'telegram_user_id' => '@cryptopumpdumpnotis',
            'isLong' => true,
            'coin' => $coin,
            'amount' => $amount,
            'count' => $count,
            'bid_ark' => $bidArk,
            'url' => $url
        ];
        Notification::route('telegram', env('TELEGRAM_CHAT_ID'))->notify(new IndicatorNotification($data));
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

    /*
    public static function sendTestTele()
    {
        $data = [
            'telegram_user_id' => '@cryptopumpdumpnotis',
            'isLong' => true,
            'coin' => 'BTC',
            'amount' => 1.5,
            'count' => 1,
        ];
        Notification::route('telegram', env('TELEGRAM_CHAT_ID'))->notify(new IndicatorNotification($data));

    }
        */

    private function sendTelegram($msg = '')
    {

        //return;
        $ch = curl_init('https://api.telegram.org/bot5684645252:AAE-yYoJAo0GPwvjvmDA-Y2GF72gVYE6Vts/sendMessage?chat_id=@cryptopumpdumpnotis&text=' . $msg);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //curl_setopt($ch, CURLOPT_HTTPHEADER, $post_header);

        //curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);
    }



    public static function binanceBidsArks($symbol = '')
    {
        if (empty($symbol)) {
            return 1;
        }

        $url = 'https://fapi.binance.com/fapi/v1/depth?symbol=' . $symbol . '&limit=10';
        $response = Http::get($url);
        $data = $response->getBody()->getContents();
        $dataArr = json_decode($data, true);

        $bids = $dataArr['bids'];
        $arks = $dataArr['asks'];

        $bidScore = 0;
        $arkScore = 0;
        foreach ($bids as $item) {
            $bidScore += (float)$item[1];
        }

        foreach ($arks as $item) {
            $arkScore += (float)$item[1];
        }

        $sumary = $bidScore / $arkScore;
        //Log::debug($dataArr);
        //Log::debug($sumary);
        return round($sumary, 2);
    }
}
