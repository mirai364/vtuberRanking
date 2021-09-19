@extends('template')
@section('title', 'チャンネル一覧')
@section('channel', 'select')
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

        .channel {
            text-align: left;
        }

        .group {
            text-align: middle;
            font-weight: bold;
        }
    </style>
    <div style="margin-top: 3rem;">
        <table class="table text-center" style="width: 70%">
            <thead>
                <tr>
                    <th class="text-center col" colspan="2">チャンネル名</th>
                    <th class="text-center col">グループ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($channelDataList as $channelData)
                    <?php $channel = $channelMap[$channelData->channelId]; ?>
                    <tr>
                        <td>
                            <a href="/channel/detail/{{ $channel->channelId }}">
                                <img id="img" style="width: 50px;height: 50px;border-radius: 50%;"
                                    src="{{ $channel->thumbnail }}">
                            </a>
                        </td>
                        <td class="channel">
                            <a href="/channel/detail/{{ $channel->channelId }}">
                                <span>{{ $channel->channelName }}</span><br>
                                <span style="font-size: small">チャンネル登録者数
                                    {{ number_format($channelData->subscribers) }}人</span>
                            </a>
                        </td>
                        <td class="group">{{ $channel->group }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
