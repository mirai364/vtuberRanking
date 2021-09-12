<head>
    <title>チャンネル詳細</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.1/chart.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script> 
</head>
  <div class="container ops-main">
  <div class="row">
      <table class="table text-center">
        <tr>
          <td>
            <a href="https://www.youtube.com/channel/{{ $channel->channelId }}">
              <img id="img" style="width: 80px;height: 80px;border-radius: 50%;" src="{{ $channel->thumbnail }}">
            </a>
          </td>
          <td style="height: 80px;vertical-align: middle;text-align: left;">
            <a href="https://www.youtube.com/channel/{{ $channel->channelId }}">
              <h3 class="ops-title" style="margin-bottom: -2rem;">{{ $channel->channelName }}</h3><br>
              チャンネル登録者数 {{ number_format($channelDataList[0]->subscribers) }}人
            </a>
          </td>
          <td style="height: 80px;vertical-align: middle;">
            <span class="ops-title" style="margin-bottom: -2rem;">所属グループ: {{ $channel->group ?? "なし" }}</span>
          </td>
        </tr>
      </table>

      <br>
      <div class="col-md-12">
        <h4 class="ops-title">登録者・再生数の推移</h4>
      </div>
      <table class="table text-center">
        <tr>
          <th class="text-center">日付</th>
          <th class="text-center">登録者</th>
          <th class="text-center">再生数</th>
        </tr>
        @foreach($channelDataList as $channelData)
        <tr>
          <td>{{ $channelData->createdAt->format('Y-m-d') }}</a></td>
          <td>{{ number_format($channelData->subscribers) }}</td>
          <td>{{ number_format($channelData->play) }}</td>
        </tr>
        @endforeach
      </table>
      <div class="chart-container">
          <canvas id="chart" height="150" style="height: 350px"></canvas>
      </div>
      <script>
        $(function(){
            //get the pie chart canvas
            var subscribers = JSON.parse(`<?= $subscribers; ?>`);
            var play = JSON.parse(`<?= $play; ?>`);
            var ctx = $("#chart");

            //pie chart data
            var data = {
              labels: subscribers.label,
              datasets: [
                {
                  type: "line",
                  label: "登録者",
                  data: subscribers.data,
                  borderColor : "rgba(254,97,132,0.8)",
                  backgroundColor : "rgba(254,97,132,0.5)",
                  yAxisID: "y",
                  fill: true,
                }, 
                {
                  type: "line",
                  label: "再生数",
                  data: play.data,
                  borderColor : "rgba(54,164,235,0.8)",
                  backgroundColor : "rgba(54,164,235,0.5)",
                  yAxisID: "y1",
                  fill: true,
                }
              ]
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
                y1: {
                  type: 'linear',
                  display: true,
                  position: 'right',
                },
              },
            };

            var chart1 = new Chart(ctx, {
              data: data,
              options: options
            });
        });
      </script>

      <br>
      <div class="col-md-12">
        <h4 class="ops-title">最近の放送</h4>
      </div>
      <table class="table text-center">
        <tr>
          <th class="text-center">開始日時</th>
          <th class="text-center" style="text-align: left;" colspan="2">放送内容</th>
          <th class="text-center"></th>
        </tr>
        @foreach($videoList as $video)
        <tr style="{{ $video->isAlive ? "background-color: papayawhip" : "background-color: lightgray" }}">
          <td style="height: 68px;vertical-align: middle;">{{ $video->starttime }}</a></td>
          <td style="height: 68px;vertical-align: middle;"><img src="http://img.youtube.com/vi/{{ $video->videoId }}/mqdefault.jpg" style="width: 120px"></td>
          <td style="height: 68px;vertical-align: middle;text-align: left;">{{ $video->videoName }}</td>
          <td style="width: 15rem;vertical-align: middle;">{!! $video->isAlive ? "<span style='color: brown;padding: 0.1em 0.2em;border: solid 2px brown;'\>ライブ配信中" : "" !!}</span></td>
        </tr>
        @endforeach
      </table>
    </div>
  </div>