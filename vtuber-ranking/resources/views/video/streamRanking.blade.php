@extends('template')
@section('title', '放送中一覧')
@section('streamRanking', 'select')
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
            @foreach ($concurrentViewersList as $concurrentViewers)
                @if( !isset($streamVideoMap[$concurrentViewers->videoId]) )
                    @continue
                @endif
                <?php $streamVideo = $streamVideoMap[$concurrentViewers->videoId]; ?>
                @if( !isset($channelMap[$streamVideo['channelId']]) )
                    @continue
                @endif
                <?php $channel = $channelMap[$streamVideo['channelId']]; ?>
                <tr>
                    <td>
                        <span class="ranking" style="font-size: big;color: brown;">{{ number_format($concurrentViewers->viewers) }}</span>
                    </td>
                    <td>
                        <img src="http://img.youtube.com/vi/{{ $streamVideo['videoId'] }}/mqdefault.jpg"
                            style="height: 60px"><br>
                    </td>
                    <td>
                        <a href="/video/detail/{{ $streamVideo['videoId'] }}">
                            <img id="img" style="width: 60px;height: 60px;border-radius: 50%;"
                                src="{{ $channel['thumbnail'] }}">
                        </a>
                    </td>
                    <td class="videoName">
                        <a href="/video/detail/{{ $streamVideo['videoId'] }}">
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
