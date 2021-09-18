@extends('template')
@section('title', 'チャンネル一覧')
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
