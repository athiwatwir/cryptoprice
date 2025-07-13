<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use App\Models\MarketPrices;
use App\Models\Notifications;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CryptoV2Helper
{




    public static function binanceSnapshotPrice()
    {
        //$stupidCoins = ['XEMUSDT'];

        $url = 'https://fapi.binance.com/fapi/v1/premiumIndex';

        try {
            $response = Http::timeout(30)->get($url);
            if (!$response->ok()) {
                Log::error("Binance API failed: " . $response->status());
                return ("Binance API failed: " . $response->status());
            }

            $dataArr = $response->json(); // ไม่ต้องแปลงด้วย getBody → json() แทน

            $newDataArr = [];

            foreach ($dataArr as $coin) {
                $symbol = $coin['symbol'];
                $markPrice = $coin['markPrice'];

                if (Str::endsWith($symbol, 'USDT')) {
                    $newDataArr[] = [
                        'symbol' => $symbol,
                        'markPrice' => $markPrice,
                    ];
                }
            }

            MarketPrices::create([
                'type' => 'BI',
                'price' => json_encode($newDataArr),
            ]);

            return ('Binance snapshot price stored: ' . count($newDataArr) . ' symbols.');
        } catch (\Exception $e) {
            Log::error("Exception in binanceSnapshotPrice: " . $e->getMessage());
            return ("Exception in binanceSnapshotPrice: " . $e->getMessage());
        }
    }

    public static function getPrices(int $limit = 60): array
    {

        $rows = MarketPrices::select('price')
            ->where('type', 'BI')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();

        $coins = [];

        foreach ($rows as $index => $row) {

            $jsondata = $row['price'];
            if (!is_null($jsondata) && $jsondata != '') {
                $jsondata = json_decode($jsondata, true);

                foreach ($jsondata as $index2 => $d) {
                    $coins[$d['symbol']][$index] = (float) $d['markPrice'];
                }
            }
        }

        return $coins;
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
