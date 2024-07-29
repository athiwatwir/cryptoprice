<?php

namespace App\Helpers;
use App\Notifications\IndicatorNotification;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class IndicatorHelper{


    public static function sendTestTele(){
        $data = [
            'telegram_user_id'=>'@cryptopumpdumpnotis',
            'isLong'=>true,
            'coin'=>'BTC',
            'amount'=>1.5,
            'count'=>1
        ];
        Notification::route('telegram', env('TELEGRAM_CHAT_ID'))->notify(new IndicatorNotification($data));

    }
    private function sendTelegram($msg = ''){

        //return;
        $ch = curl_init('https://api.telegram.org/bot5684645252:AAE-yYoJAo0GPwvjvmDA-Y2GF72gVYE6Vts/sendMessage?chat_id=@cryptopumpdumpnotis&text='.$msg);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //curl_setopt($ch, CURLOPT_HTTPHEADER, $post_header);

            //curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);

    }
}
