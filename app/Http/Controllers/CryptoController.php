<?php

namespace App\Http\Controllers;

use App\Helpers\CryptoDataHelper;
use App\Models\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CryptoController extends Controller
{
    public function index()
    {

        $date = Carbon::now()->timezone('Asia/Bangkok');
        $now = $date->copy()->subHours(12);
        $now = $now->format('Y-m-d H:i');

        $coinAlerts = Notifications::where('type', 'PD')->where('created_at', '>=', $now)->orderBy('updated_at', 'DESC')->limit(20)->get();

        $fillterCoins = [];

        /*
        foreach ($coinAlerts as $coin) {
            array_push($fillterCoins, $coin->name);
        }
            */
        $prices = CryptoDataHelper::getPrices($fillterCoins);

        $data = [];
        foreach ($coinAlerts as $item) {
            $coinPrices = $prices[$item->name];
            array_push($data, [
                'name' => $item->name,
                'count' => $item->count,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'current_price' => $coinPrices[0],
                'ch_5min' => $this->calPercent($coinPrices[0], $coinPrices[4]),
                'ch_10min' => $this->calPercent($coinPrices[0], isset($coinPrices[9]) ? $coinPrices[9] : 0),
                'ch_30min' => $this->calPercent($coinPrices[0], isset($coinPrices[29]) ? $coinPrices[29] : 0),
                'ch_1h' => $this->calPercent($coinPrices[0], isset($coinPrices[59]) ? $coinPrices[59] : 0),
                'ch_2h' => $this->calPercent($coinPrices[0], isset($coinPrices[119]) ? $coinPrices[119] : 0),
                'ch_4h' => $this->calPercent($coinPrices[0], isset($coinPrices[239]) ? $coinPrices[239] : 0),
                'ch_24h' => $this->calPercent($coinPrices[0], isset($coinPrices[1439]) ? $coinPrices[1439] : 0)
            ]);
        }

        $allCoins = [];
        foreach ($prices as $coinName => $item) {
            array_push($allCoins, [
                'name' => $coinName,
                'ch_5min' => $this->calPercent($item[0], $item[4]),
                'ch_10min' => $this->calPercent($item[0], $item[9]),
                'ch_30min' => $this->calPercent($item[0], isset($item[29]) ? $item[29] : 0),
                'ch_1h' => $this->calPercent($item[0], isset($item[59]) ? $item[59] : 0),
                'ch_2h' => $this->calPercent($item[0], isset($item[119]) ? $item[119] : 0),
                'ch_4h' => $this->calPercent($item[0], isset($item[239]) ? $item[239] : 0),
                'ch_24h' => $this->calPercent($item[0], isset($item[1439]) ? $item[1439] : 0)
            ]);
        }

        $change = request()->change;
        $sort = request()->sort;

        $keyColumn = array_column($allCoins, $change); // ดึงค่าของ key 'age' ออกมา
        if ($sort == 'ASC') {
            array_multisort($keyColumn, SORT_ASC, $allCoins); // เรียงตาม $ages แบบ ascending
        } else {
            array_multisort($keyColumn, SORT_DESC, $allCoins); // เรียงตาม $ages แบบ ascending
        }

        $url = '';
        if (CryptoDataHelper::$TYPE == 'BI') {
            $url = 'https://www.binance.com/en/futures/';
        } else {
            $url = 'https://www.okx.com/trade-swap/';
        }

        return view('pages.crypto.index', [
            'coinAlerts' => $data,
            'allCoins' => $allCoins,
            'url' => $url
        ]);
    }

    private function calPercent($a, $b)
    {
        if ($b == 0) {
            return 0;
        }
        $changePercent = round((($a - $b) / $b) * 100, 2);
        return $changePercent;
    }
}
