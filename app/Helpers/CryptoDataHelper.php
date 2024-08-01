<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class CryptoDataHelper
{

    public static function sendCryptoChartToTelegram($coinName = 'BTCUSDT')
    {
        $t = new CryptoDataHelper();
        $coinName = strtolower($coinName);
        $coinName = str_replace('usdt', 'xxx', $coinName);


        $coinLists = Storage::disk('public')->json('json/data.json');

        $cryptoId = array_search($coinName, array_column($coinLists, 'symbol'));
        if (!$cryptoId) {
            $cryptoId = $coinLists[$cryptoId]['id'];
            $data = $t->getCryptoData($cryptoId);

            if (!empty($data)) {
                $filename = $t->drawChart($data);
                $t->sendPhotoToTelegram($filename);
                //echo "Chart sent to Telegram successfully.";
            }
        }

    }

    private function getCryptoData($cryptoId)
    {
        $client = new Client();
        $response = $client->get("https://api.coingecko.com/api/v3/coins/$cryptoId/market_chart?vs_currency=usd&days=2");
        return json_decode($response->getBody(), true);
    }

    private function drawChart($data, $width = 800, $height = 500)
    {
        $image = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);

        imagefill($image, 0, 0, $white);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $black);

        $prices = $data['prices'];
        $num_points = count($prices);
        $max_price = max(array_column($prices, 1));
        $min_price = min(array_column($prices, 1));

        $padding = 50;
        $graph_width = $width - 2 * $padding;
        $graph_height = $height - 2 * $padding;

        for ($i = 1; $i < $num_points; $i++) {
            $x1 = $padding + (($i - 1) / ($num_points - 1)) * $graph_width;
            $y1 = $height - $padding - (($prices[$i - 1][1] - $min_price) / ($max_price - $min_price)) * $graph_height;
            $x2 = $padding + ($i / ($num_points - 1)) * $graph_width;
            $y2 = $height - $padding - (($prices[$i][1] - $min_price) / ($max_price - $min_price)) * $graph_height;

            imageline($image, $x1, $y1, $x2, $y2, $red);
        }

        $filename = base_path().'/public_html/crypto_chart.jpg';
        imagejpeg($image, $filename);
        imagedestroy($image);

        return $filename;
    }

    private function sendPhotoToTelegram($filename)
    {
        $token = '5684645252:AAE-yYoJAo0GPwvjvmDA-Y2GF72gVYE6Vts';
        $chatId = '@cryptopumpdumpnotis';
        $url = "https://api.telegram.org/bot$token/sendPhoto";

        $client = new Client();
        $client->post($url, [
            'multipart' => [
                [
                    'name' => 'chat_id',
                    'contents' => $chatId,
                ],
                [
                    'name' => 'photo',
                    'contents' => fopen($filename, 'r'),
                ],
            ],
        ]);
    }

}
