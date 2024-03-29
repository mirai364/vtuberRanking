@extends('template')
@section('title', '動画詳細')
@section('content')
    <table class="table text-center">
        <tr>
            <td>
                <a href="/channel/detail/{{ $channel->channelId }}">
                    <img id="img" style="width: 80px;height: 80px;border-radius: 50%;" src="{{ $channel->thumbnail }}">
                </a>
            </td>
            <td style="height: 80px;vertical-align: middle;text-align: left;">
                <a href="/channel/detail/{{ $channel->channelId }}">
                    <h3 class="ops-title" style="margin-bottom: -2rem;">{{ $channel->channelName }}</h3><br>
                    チャンネル登録者数 {{ isset($channelDataList[0]) ? number_format($channelDataList[0]->subscribers) : '-' }}人
                </a>
            </td>
            <td style="height: 80px;vertical-align: middle;">
                <span class="ops-title" style="margin-bottom: -2rem;">所属グループ: {{ $channel->group ?? 'なし' }}</span>
            </td>
        </tr>
    </table>

    <br>
    <div class="col-md-12">
        <h4 class="ops-title">動画詳細</h4>
    </div>
    <table class="table text-center">
        <tr>
            <th class="text-center">開始日時</th>
            <th class="text-center" style="text-align: left;" colspan="2">放送内容</th>
            <th class="text-center"></th>
        </tr>
        <tr style="{{ $video->isAlive === 1 ? 'background-color: papayawhip' : 'background-color: lightgray' }}">
            <td style="height: 68px;vertical-align: middle;">{{ $video->starttime }}</a></td>
            <td style="height: 68px;vertical-align: middle;"><img
                    src="http://img.youtube.com/vi/{{ $video->videoId }}/mqdefault.jpg" style="width: 120px"></td>
            <td style="height: 68px;vertical-align: middle;text-align: left;">{{ $video->videoName }}</td>
            <td style="width: 15rem;vertical-align: middle;">{!! $video->isAlive === 1 ? "<a href='https://www.youtube.com/watch?v=$video->videoId'><span style='color: brown;padding: 0.1em 0.2em;border: solid 2px brown;'\>ライブ配信中</a>" : '' !!}</span></td>
        </tr>
    </table>

    <br>
    <div class="col-md-12">
        <h4 class="ops-title">視聴者推移</h4>
    </div>
    <div class="chart-container">
        <canvas id="chart"></canvas>
    </div>
    <script>
        $(function() {
            //get the pie chart canvas
            var json = JSON.parse(`<?= $data ?>`);
            var ctx = $("#chart");

            //pie chart data
            var data = {
                labels: json.label,
                datasets: [{
                    type: "line",
                    label: "視聴者",
                    data: json.data,
                    borderColor: "rgba(254,97,132,1)",
                    backgroundColor: "rgba(254,97,132,1)",
                }, ]
            };

            //options
            var options = {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    x: {
                        // The axis for this scale is determined from the first letter of the id as `'x'`
                        // It is recommended to specify `position` and / or `axis` explicitly.
                        type: 'time',
                        time: {
                            unit: 'minute',
                            displayFormats: {
                                'minute': 'DD日 HH:mm'
                            }
                        }
                    }
                },
            };

            var chart1 = new Chart(ctx, {
                data: data,
                options: options
            });
        });
    </script>
@endsection
