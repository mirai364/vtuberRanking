<head>
    <title>チャンネル一覧</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  </head>
  <div class="container ops-main">
  <div class="row">
    <div class="col-md-12">
      <h3 class="ops-title">チャンネル一覧</h3>
    </div>
  </div>
  <div class="row">
    <div class="col-md-11 col-md-offset-1">
      <table class="table text-center">
        <tr>
          <th class="text-center">チャンネルID</th>
          <th class="text-center">チャンネル名</th>
          <th class="text-center">グループ</th>
        </tr>
        @foreach($channelList as $channel)
        <tr>
          <td>
            <a href="/channel/detail/{{ $channel->channelId }}">{{ $channel->channelId }}</a>
          </td>
          <td>{{ $channel->channelName }}</td>
          <td>{{ $channel->group }}</td>
        </tr>
        @endforeach
      </table>
    </div>
  </div>