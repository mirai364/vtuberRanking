@extends('template')
@section('title', '時間別ランキング')
@section('content')
    <style>
        table tr {
            background-color: #fff;
            border-bottom: 2px solid #fff;
        }

        table tr:nth-child(even) {
            background-color: #eee;
        }

    </style>
    <div style="margin-top: 3rem;">
        <table class="table text-center">
            <thead>
                <tr>
                    <th style="width: 5rem;" class="text-center col">時間</th>
                    <th class="text-center col">1位</th>
                    <th class="text-center col">2位</th>
                    <th class="text-center col">3位</th>
                    <th class="text-center col">4位</th>
                    <th class="text-center col">5位</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($hourlyMap as $time => $hourlyList)
                    <tr>
                        <th style="text-align: center;vertical-align: middle;"><span>{{ $time }}時</span></th>
                        @foreach ($hourlyList as $videoId => $viewers)
                            <td style="text-align: center;vertical-align: middle;">
                                <?php $streamVideo = $streamVideoMap[$videoId]; ?>
                                <a href="/video/detail/{{ $videoId }}">
                                    <img src="http://img.youtube.com/vi/{{ $streamVideo['videoId'] }}/mqdefault.jpg"
                                        style="width: 120px"><br>
                                    <span style="font-size: small">{{ $streamVideo['videoName'] }}</span><br>
                                    <span style="font-size: small;color: brown;">視聴者:
                                        {{ number_format($viewers['viewers']) }}人</span>
                                </a>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
