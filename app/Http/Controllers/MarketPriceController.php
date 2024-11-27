<?php

namespace App\Http\Controllers;

use App\Helpers\CryptoDataHelper;
use App\Helpers\IndicatorHelper;
use App\Helpers\TrandHelper;
use App\Models\CoinStats;
use App\Models\MarketPrices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MarketPriceController extends Controller
{
    public function updatePrice()
    {

        $this->binance();
        //$this->okx();

        /*

        IndicatorHelper::calculator();
*/
        return response()->json([
            'status' => true,
            'message' => "updatePrice successfully!",
        ], 200);
    }

    public function indicator(){
        //sleep(10);
        $data = IndicatorHelper::calculator();
        return response()->json([
            'status' => true,
            'message' => "indicator successfully!",
            'data'=>$data
        ], 200);
    }

    public function updateTrand(){

        $data = TrandHelper::calculator();

        return response()->json([
            'status' => true,
            'message' => "successfully!",
            'data'=>$data
        ], 200);

    }

    public function max(){
        $_prices = MarketPrices::select('price')->where('type', 'BI')->orderBy('created_at', 'DESC')->limit(2880)->get();
        $_coins = [];
        foreach ($_prices as $index => $row) {

            $jsondata = $row['price'];
            if (!is_null($jsondata) && $jsondata != '') {
                $jsondata = json_decode($jsondata, true);

                foreach ($jsondata as $index2 => $d) {
                    $_coins[$d['symbol']][$index] = (float) $d['markPrice'];
                }
            }
        }

        foreach($_coins as $index => $prices){
            unset($_coins[$index][0]);
            unset($_coins[$index][1]);


            $max4 = max(array_slice($prices,0,240));
            $max6 = max(array_slice($prices,0,360));
            $max12 = max(array_slice($prices,0,720));
            $max24 = max(array_slice($prices,0,1440));
            $max48 = max($prices);

            CoinStats::updateOrCreate(
                ['name'=>$index],
                [
                    'name'=>trim($index),
                    'max4'=>round($max4,6),
                    'max6'=>round($max6,6),
                    'max12'=>round($max12,6),
                    'max24'=>round($max24,6),
                    'max48'=>round($max48,6),
                ]
            );


        }

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
        $response = Http::timeout(30)->get($url);
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

        $markPrice = MarketPrices::create([
            'type'=>'BI',
            'price'=>json_encode($newDataArr),
        ]);

        $markPrice->created_at = Carbon::now('Asia/Bangkok');
        $markPrice->updated_at = Carbon::now('Asia/Bangkok');
        $markPrice->save();
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
