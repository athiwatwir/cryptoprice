<?php

namespace App\Helpers;

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
        $types = ['BI'];

        foreach ($types as $a => $type) {
            $prices = MarketPrices::select('price')->where('type', $type)->orderBy('created_at', 'DESC')->limit(6)->get();
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

            $upperCount = 0;
            $failCount = 0;
            $lastPrice = 0;
            //Log::debug($coins);
            foreach ($coins as $coin => $prices) {

                foreach ($prices as $index => $price) {
                    if($price <= $lastPrice){
                        $upperCount++;
                        //$failCount = 0;
                    }else{
                        $failCount++;
                    }

                    $lastPrice = $price;
                }

                if($failCount < 4){
                    if(sizeof($_coins) == 0){
                        $_prices = MarketPrices::select('price')->where('type', $type)->orderBy('created_at', 'DESC')->limit(720)->get();
                        foreach ($_prices as $index => $row) {

                            $jsondata = $row['price'];
                            if (!is_null($jsondata) && $jsondata != '') {
                                $jsondata = json_decode($jsondata, true);

                                foreach ($jsondata as $index2 => $d) {
                                    $_coins[$d['symbol']][$index] = (float) $d['markPrice'];
                                }
                            }
                        }
                    }

                    $maxPrice = max(($_coins[$coin]));
                    //Log::debug($coin);
                    //Log::debug($maxPrice);
                    //Log::debug($prices[0]);

                    //$prices[0] > $maxPrice
                    if($prices[0] > $maxPrice){
                        $toDaydate = date('Y-m-d');
                        $_name = sprintf('%s_TRAND_%s', $toDaydate,  $coin);

                        $notificationLog = Notifications::where('name', $_name)->first();
                        if(empty($notificationLog)){

                            $data = [
                                'telegram_user_id' => '@cryptotrands789',
                                'coin' => $coin,
                            ];
                            Notification::route('telegram', '@cryptotrands789')->notify(new TrandNotification($data));


                            $notificationLog = Notifications::create(
                                [
                                    'name' => $_name,
                                    'count' => 1,
                                ]
                            );

                        }

                    }

                }
            }
        }


    }
}
