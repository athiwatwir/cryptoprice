<?php

namespace App\Http\Controllers;

use App\Helpers\CryptoDataHelper;
use App\Helpers\IndicatorHelper;
use App\Helpers\TrandHelper;
use App\Models\MarketPrices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MarketPriceController extends Controller
{
    public function updatePrice()
    {
        $this->binance();
        //$this->okx();


        IndicatorHelper::calculator();
        TrandHelper::calculator();

        return response()->json([
            'status' => true,
            'message' => "successfully!",
        ], 200);
    }

    private function binance()
    {
        /*
        [
            {
                "symbol": "LOOMUSDT",
                "markPrice": "0.06178000",
                "indexPrice": "0.06182692",
                "estimatedSettlePrice": "0.06198544",
                "lastFundingRate": "0.00005000",
                "interestRate": "0.00005000",
                "nextFundingTime": 1722240000000,
                "time": 1722237541000
            },
    ]
  */

        $url = 'https://fapi.binance.com/fapi/v1/premiumIndex';
        $response = Http::get($url);
        $data = $response->getBody()->getContents();
        $dataArr = json_decode($data,true);

        $newDataArr = [];

        foreach($dataArr as $index => $coin){

            $coinName = $coin['symbol'];
            $price = $coin['markPrice'];

            $stupidCoins = IndicatorHelper::$stupidCoins;

            if(Str::endsWith($coinName, 'USDT') && (in_array($coinName,$stupidCoins) == false)){
                array_push($newDataArr,['symbol'=>$coinName,'markPrice'=>$price]);
            }

        }

        MarketPrices::create([
            'type'=>'BI',
            'price'=>json_encode($newDataArr),
        ]);
    }

    private function okx()
    {
        //https://www.okx.com/docs-v5/en/?shell#public-data-rest-api-get-mark-price
        /*
        {
        "code": "0",
        "data": [
            {
            "instId": "USTC-USDT-SWAP",
            "instType": "SWAP",
            "markPx": "0.02022",
            "ts": "1722242081497"
            },
            {
            "instId": "MEW-USDT-SWAP",
            "instType": "SWAP",
            "markPx": "0.006717",
            "ts": "1722242081497"
            },
        }
        */

        $url = 'https://tr.okx.com/api/v5/public/mark-price?instType=SWAP';
        $response = Http::get($url);
        $data = $response->getBody()->getContents();
        $dataArr = json_decode($data,true);

        $dataArr = $dataArr['data'];

        $newDataArr = [];

        foreach($dataArr as $index => $coin){

            $coinName = $coin['instId'];
            $coinName = str_replace('-','',$coinName);
            $coinName = str_replace('SWAP','',$coinName);

            $price = $coin['markPx'];

            $stupidCoins = [];
            if(Str::endsWith($coinName, 'USDT') && (in_array($coinName,$stupidCoins) == false)){
                array_push($newDataArr,['symbol'=>$coinName,'markPrice'=>$price]);
            }

        }

        MarketPrices::create([
            'type'=>'OK',
            'price'=>json_encode($newDataArr),
        ]);
    }
}
