@extends('template')
@section('title')
{{ is_null($date) ? '' : $date . ' ' }}日別ランキング@endsection
@section('content')
    <style>
        table {
            border-collapse: collapse;
            margin: 0 auto;
            padding: 0;
            width: 650px;
        }

        table tr {
            background-color: #fff;
            border-bottom: 2px solid #fff;
        }

        table tr:nth-child(even) {
            background-color: #eee;
        }

        table th,
        table td {
            padding: .35em 1em;
        }

        table thead th {
            font-size: .85em;
            padding: 1em;
        }

        table thead tr {
            background-color: #fd6767;
            color: #fff;
        }

        table tbody th {
            text-align: left;
            font-size: .8em;
        }

        .ranking {
            text-align: center;
            vertical-align: middle;
            line-height: 60px;
        }

        .channel {
            text-align: left;
            vertical-align: middle !important;
        }

        .videoName {
            text-align: left;
            vertical-align: middle !important;
        }

        @media screen and (max-width: 600px) {
            table {
                border: 0;
                width: 100%
            }

            table th {
                background-color: #fd6767;
                display: block;
                border-right: none;
            }

            table thead {
                border: none;
                clip: rect(0 0 0 0);
                height: 1px;
                margin: -1px;
                overflow: hidden;
                padding: 0;
                position: absolute;
                width: 1px;
            }

            table tr {
                display: block;
                margin-bottom: .625em;
                border: 1px solid #fd6767;
            }

            table td {
                border-bottom: 1px dotted #bbb;
                display: block;
                font-size: .8em;
                text-align: right;
                position: relative;
                padding: 1.5em 1em 1.5em 4em;
                border-right: none;
                height: 50px;
                vertical-align: middle;
            }

            table td::before {
                content: attr(data-label);
                font-weight: bold;
                position: absolute;
                left: 10px;
                color: #000;
            }

            table td:last-child {
                border-bottom: 0;
            }

            table tbody th {
                color: #fff;
                padding: 1em
            }

            table tr:nth-child(even) {
                background-color: #fff;
            }
        }

    </style>
    <div style="margin-top: 3rem;">
        <table class="table text-center">
            <thead>
                <tr>
                    <th class="text-center col" style="width: 8rem">視聴者数</th>
                    <th class="text-center col" colspan="3">放送名</th>
                </tr>
            </thead>
            <tbody>
                <?php $ranking = 1; ?>
                @foreach ($dailyMap as $daily)
                    <?php $streamVideo = $streamVideoMap[$daily['videoId']]; ?>
                    <?php $channel = $channelMap[$streamVideo['channelId']]; ?>
                    <tr>
                        <td>
                            <span class="ranking" style="font-size: big;color: brown;">{{ number_format($daily['viewers']) }}</span>
                        </td>
                        <td>
                            <img src="http://img.youtube.com/vi/{{ $streamVideo['videoId'] }}/mqdefault.jpg"
                                style="width: 120px"><br>
                        </td>
                        <td>
                            <a href="/video/detail/{{ $streamVideo['videoId'] }}">
                                <img id="img" style="width: 60px;height: 60px;border-radius: 50%;"
                                    src="{{ $channel['thumbnail'] }}">
                            </a>
                        </td>
                        <td class="videoName">
                            <a href="/video/detail/{{ $daily['videoId'] }}">
                                <span style="font-size: small">{{ $channel['channelName'] }}</span><br>
                                <span style="font-size: big">{{ $streamVideo['videoName'] }}</span>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
