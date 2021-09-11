<head>
    <title>チャンネル詳細</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  </head>
  <div class="container ops-main">
  <div class="row">
      <table class="table text-center">
        <tr>
          <td><img id="img" style="width: 80px;height: 80px;border-radius: 50%;" src="{{ $channel->thumbnail }}"></td>
          <td style="height: 80px;vertical-align: middle;text-align: left;">
            <h3 class="ops-title" style="margin-bottom: -2rem;">{{ $channel->channelName }}</h3><br>
            チャンネル登録者数 {{ number_format($channelDataList[0]->subscribers) }}人
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

      <br>
      <div class="col-md-12">
        <h4 class="ops-title">最近の放送</h4>
      </div>
      <table class="table text-center">
        <tr>
          <th class="text-center">開始日時</th>
          <th class="text-center" style="text-align: left;" colspan="2">放送内容</th>
          <th class="text-center">status</th>
        </tr>
        @foreach($videoList as $video)
        <tr>
          <td style="height: 68px;vertical-align: middle;">{{ $video->starttime }}</a></td>
          <td style="height: 68px;vertical-align: middle;"><img src="http://img.youtube.com/vi/{{ $video->videoId }}/mqdefault.jpg" style="width: 120px"></td>
          <td style="height: 68px;vertical-align: middle;text-align: left;">{{ $video->videoName }}</td>
          <td style="height: 68px;vertical-align: middle;">{{ $video->isAlive ? "放送中" : "" }}</td>
        </tr>
        @endforeach
      </table>
    </div>
  </div>