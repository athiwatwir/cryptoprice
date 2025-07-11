@extends('layouts.default')

@section('content')
<div class="row">
    <div class="col-12">
        <h3>New notis coin</h3>
        <div class="row">
            <div class="col-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="text-center"></th>
                            <th class="text-center"></th>
                            <th class="text-center"></th>
                            <th class="text-end">Now Price</th>
                            <th class="text-end">5Min</th>
                            <th class="text-end">10Min</th>
                            <th class="text-end">30Min</th>
                            <th class="text-end">1H</th>
                            <th class="text-end">4H</th>
                            <th class="text-end">24H</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($coinAlerts as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-center">{{ $item['count'] }}</td>
                            <td class="text-center"><small>{{ $item['created_at'] }}</small></td>
                            <td class="text-center"><small>{{ $item['updated_at'] }}</small></td>
                            <td class="text-end">
                                <small>{{ $item['current_price']}}</small>
                            </td>
                            <td class="text-end @if ($item['ch_5min'] >0)
                                text-success
                            @endif">{{ $item['ch_5min'] }}%</td>
                            <td class="text-end @if ($item['ch_10min'] >0)
                                text-success
                            @endif">{{ $item['ch_10min'] }}%</td>
                            <td class="text-end @if ($item['ch_30min'] >0)
                                text-success
                            @endif">{{ $item['ch_30min'] }}%</td>
                            <td class="text-end @if ($item['ch_1h'] >0)
                                text-success
                            @endif">{{ $item['ch_1h'] }}%</td>
                            <td class="text-end @if ($item['ch_4h'] >0)
                                text-success
                            @endif">{{ $item['ch_4h'] }}%</td>
                            <td class="text-end @if ($item['ch_24h'] >0)
                                text-success
                            @endif">{{ $item['ch_24h'] }}%</td>
                            <td class="text-end">
                                <a href="{{ $url }}{{ $item['name'] }}?_from=markets" target="_blank">VIEW</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <h3>Change%</h3>
        <div class="row">
            <div class="col-12">
                <a href="{{ route('crypto.index',['change'=>'ch_5min','sort'=>'DESC']) }}" class="btn btn-outline-success">5Min</a>
                <a href="{{ route('crypto.index',['change'=>'ch_10min','sort'=>'DESC']) }}" class="btn btn-outline-success">10Min</a>
                <a href="{{ route('crypto.index',['change'=>'ch_30min','sort'=>'DESC']) }}" class="btn btn-outline-success">30Min</a>
                <a href="{{ route('crypto.index',['change'=>'ch_1h','sort'=>'DESC']) }}" class="btn btn-outline-success">1H</a>
                <a href="{{ route('crypto.index',['change'=>'ch_4h','sort'=>'DESC']) }}" class="btn btn-outline-success">4H</a>
                <a href="{{ route('crypto.index',['change'=>'ch_24h','sort'=>'DESC']) }}" class="btn btn-outline-success">24H</a>

                <a href="{{ route('crypto.index',['change'=>'ch_5min','sort'=>'ASC']) }}" class="btn btn-outline-danger">5Min</a>
                <a href="{{ route('crypto.index',['change'=>'ch_10min','sort'=>'ASC']) }}" class="btn btn-outline-danger">10Min</a>
                <a href="{{ route('crypto.index',['change'=>'ch_30min','sort'=>'ASC']) }}" class="btn btn-outline-danger">30Min</a>
                <a href="{{ route('crypto.index',['change'=>'ch_1h','sort'=>'ASC']) }}" class="btn btn-outline-danger">1H</a>
                <a href="{{ route('crypto.index',['change'=>'ch_4h','sort'=>'ASC']) }}" class="btn btn-outline-danger">4H</a>
                <a href="{{ route('crypto.index',['change'=>'ch_24h','sort'=>'ASC']) }}" class="btn btn-outline-danger">24H</a>
            </div>
            <div class="col-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="text-end">5Min</th>
                            <th class="text-end">10Min</th>
                            <th class="text-end">30Min</th>
                            <th class="text-end">1H</th>
                            <th class="text-end">4H</th>
                            <th class="text-end">24H</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allCoins as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-end text-danger">
                                <span class="@if ($item['ch_5min'] >0)
                                text-success
                            @endif">{{ $item['ch_5min'] }}%</span>
                            </td>

                            <td class="text-end text-danger">
                                <span class="@if ($item['ch_10min'] >0)
                                text-success
                            @endif">{{ $item['ch_10min'] }}%</span>
                            </td>

                            <td class="text-end text-danger">
                                <span class="@if ($item['ch_30min'] >0)
                                text-success
                            @endif">{{ $item['ch_30min'] }}%</span>
                            </td>

                            <td class="text-end text-danger">
                                <span class="@if ($item['ch_1h'] >0)
                                text-success
                            @endif">{{ $item['ch_1h'] }}%</span>
                            </td>

                            <td class="text-end text-danger">
                                <span class="@if ($item['ch_4h'] >0)
                                text-success
                            @endif">{{ $item['ch_4h'] }}%</span>
                            </td>

                            <td class="text-end text-danger">
                                <span class="@if ($item['ch_24h'] >0)
                                text-success
                            @endif">{{ $item['ch_24h'] }}%</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ $url }}{{ $item['name'] }}?_from=markets" target="_blank">VIEW</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@stop
