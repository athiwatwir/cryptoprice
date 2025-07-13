<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;


use App\Helpers\CryptoV2Helper;
use App\Helpers\IndicatorV2Helper;



class CryptoProcessJob implements ShouldQueue
{
    use Queueable;


    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        CryptoV2Helper::binanceSnapshotPrice();
        IndicatorV2Helper::indicator();
    }
}
