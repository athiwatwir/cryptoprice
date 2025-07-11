<?php

namespace App\Helpers;

use App\Models\CoinStats;
use App\Models\MarketPrices;
use App\Models\Notifications;
use App\Notifications\IndicatorNotification;
use App\Notifications\TrandNotification;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrandHelper
{
    public static function calculator()
    {
        $types = [CryptoDataHelper::$TYPE];
        $data = [];

        foreach ($types as $a => $type) {
            $prices = MarketPrices::select('price', 'created_at')->where('type', $type)->orderBy('created_at', 'DESC')->limit(15)->get();

            //dd($prices);
            $coins = [];
            $_coins = [];

            foreach ($prices as $index => $row) {

                $jsondata = $row['price'];
                if (!is_null($jsondata) && $jsondata != '') {
                    $jsondata = json_decode($jsondata, true);

                    foreach ($jsondata as $index2 => $d) {
                        $coins[$d['symbol']][$index] = (float) $d['markPrice'];
                    }
                }
            }


            $lastPrice = 0;
            $loopCoin = 0;
            //Log::debug($coins);
            foreach ($coins as $coin => $prices) {

                if (in_array($coin, ['DARUSDT', 'STRAXUSDT', 'STPTUSDT', 'DGBUSDT'])) {
                    continue;
                }
                $upperCount = 0;
                $failCount = 0;

                $lastPrice = $prices[0];
                foreach ($prices as $index => $price) {

                    if ($price <= $lastPrice) {
                        $upperCount++;
                        $lastPrice = $price;
                    } else {
                        $failCount++;
                    }
                }

                if ($failCount < 4) {
                    $coinStat = CoinStats::where('name', $coin)->first();
                    $max24 = $coinStat->max24;
                    //$prices[0] > $maxPrice
                    if ($prices[0] > $max24) {
                        $toDaydate = date('Y-m-d');
                        $_name = sprintf('%s_TRAND_%s', $toDaydate,  $coin);

                        $notificationLog = Notifications::where('name', $_name)->first();
                        if (is_null($notificationLog)) {

                            $data = [
                                'telegram_user_id' => '@cryptotrands789',
                                'coin' => $coin,
                                'current' => $prices[0],
                                'max' => $max24,
                                'text' => ''
                            ];
                            Notification::route('telegram', '@cryptotrands789')->notify(new TrandNotification($data));

                            $notificationLog = Notifications::create(
                                [
                                    'code' => $_name,
                                    'name' => $coin,
                                    'type' => 'TR',
                                    'count' => 1,
                                ]
                            );
                        }
                    }
                }
                $loopCoin++;
            }
        }

        $data['loopCoin'] = $loopCoin;

        return $data;
    }
}
