<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Channel;
use App\Models\ChannelData;
use App\Models\Video;
use App\Models\ConcurrentViewers;
use DateTime;
use Carbon\Carbon;

class VideoController extends Controller
{
  public function detail($videoId)
  {
    $video = Video::find($videoId);
    if (empty($video)) {
      return redirect('/');
    }

    $channel = Channel::findBychannelId($video->channelId);
    $channelDataList = ChannelData::findBychannelId($channel->id)->slice(0, 1);
    $concurrentViewersList = ConcurrentViewers::findAllByVideoId($videoId);

    // chart用のデータを作成
    $data = [];
    foreach ($concurrentViewersList as $concurrentViewers) {
      $data['label'][] = $concurrentViewers->createdAt->format('Y-m-d H:i:00');
      $data['data'][] = (int) $concurrentViewers->viewers;
    }
    $data = json_encode($data);

    return view('video/detail', compact('video', 'channel', 'channelDataList', 'data'));
  }

  public function streamRanking()
  {
    $streamVideoList = Video::findAllByIsAlive();
    $streamVideoMap = [];
    $channelIdList = [];
    $videoIdList = [];
    foreach ($streamVideoList as $streamVideo) {
      $videoIdList[] = $streamVideo->id;
      $channelIdList[] = $streamVideo->channelId;
      $streamVideoMap[$streamVideo->id] = ['channelId' => $streamVideo->channelId, 'videoId' => $streamVideo->videoId, 'videoName' => $streamVideo->videoName, 'starttime' => $streamVideo->starttime,];
    }
    $now = Carbon::now();
    $concurrentViewersList = ConcurrentViewers::findAllByVideoIdListAndDate($videoIdList, $now);
    if ($concurrentViewersList->isEmpty()) {
      $now->subMinutes(1);
      $concurrentViewersList = ConcurrentViewers::findAllByVideoIdListAndDate($videoIdList, $now);
    }
    $channelList = Channel::findAllByChannelIdList($channelIdList);
    $channelMap = [];
    foreach ($channelList as $channel) {
      $channelMap[$channel->channelId] = ['thumbnail' => $channel->thumbnail, 'channelName' => $channel->channelName, 'group' => $channel->group,];
    }

    return view('video/streamRanking', compact('concurrentViewersList', 'streamVideoMap', 'channelMap'));
  }

  /**
   * @param string $date
   * @return DateTime
   */
  private function checkDate(string $date = null)
  {
    $now = new DateTime();
    if ($date === null) {
      return [$now, null];
    }
    if (strlen($date) < 8) {
      return [$now, null];
    }
    $targetDate = clone $now;
    // XXXX-XX-XX の形式になっているか？
    if (preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $date)) {
      $targetDate = new DateTime($date . ' 23:59:59');
    }
    // XXXXXXXX の形式になっているか？
    if (preg_match('/\A[0-9]{8}\z/', $date)) {
      $year = str_split($date, 4)[0];
      $month = str_split($date, 2)[2];
      $day = str_split($date, 2)[3];
      $date = $year . '-' . $month . '-' . $day;
      $targetDate = new DateTime($date . ' 23:59:59');
    }
    $date = $targetDate->format('Y年n月j日') . '(' . self::DAY_CONVERT[$targetDate->format('N')] . ')';

    // あんまり古いデータは取得できないようにする
    $pastDay = (clone $now)->modify('- 14days');
    if ($pastDay > $targetDate) {
      return [$now, null];
    }
    return $now <= $targetDate ? [$now, null] : [$targetDate, $date];
  }

  public function hourlyStreamRanking($date = null)
  {
    [$targetDate, $date] = $this->checkDate($date);

    $hourlyMap = [];
    for ($i = 1; $i <= 24; $i++) {
      $nowDate = (clone $targetDate)->modify('+ 1seconds')->modify('- ' . $i . 'Hours');
      if ($date !== null && $targetDate->format('Y-m-d') !== $nowDate->format('Y-m-d')) {
        break;
      }
      $hourlyMap[(int)$nowDate->format('H')] = ConcurrentViewers::getHourlyStreamConcurrentViewersList($nowDate);
    }

    $streamVideoList = Video::findAllByIdList(array_unique(array_column(array_merge(...$hourlyMap), 'videoId')));
    $streamVideoMap = [];
    foreach ($streamVideoList as $streamVideo) {
      $streamVideoMap[$streamVideo->id] = ['channelId' => $streamVideo->channelId, 'videoId' => $streamVideo->videoId, 'videoName' => $streamVideo->videoName, 'starttime' => $streamVideo->starttime,];
    }

    return view('video/hourlyStreamRanking', compact('hourlyMap', 'streamVideoMap', 'date'));
  }

  CONST DAY_CONVERT = [
    1 => '<span style="color: yellow">月</span>',
    2 => '<span style="color: pink">火</span>',
    3 => '<span style="color: green">水</span>',
    4 => '<span style="color: orange">木</span>',
    5 => '<span style="color: lightblue">金</span>',
    6 => '<span style="color: purple">土</span>',
    7 => '<span style="color: red">日</span>',
  ];

  public function dailyStreamRanking($date = null)
  {
    [$targetDate, $date] = $this->checkDate($date);
    $cacheTime = null;
    if ($date === null) {
      // 当日分は5分キャッシュで、随時更新処理
      $cacheTime = 5 * 60;
      $now = new DateTime();
      $date = $now->format('Y年n月j日') . '(' . self::DAY_CONVERT[$now->format('N')] . ')';
    }

    $dailyMap = ConcurrentViewers::getDailyStreamConcurrentViewersList($targetDate, $cacheTime);
    $streamVideoList = Video::findAllByIdList(array_keys($dailyMap));
    $streamVideoMap = [];
    foreach ($streamVideoList as $streamVideo) {
      $streamVideoMap[$streamVideo->id] = ['channelId' => $streamVideo->channelId, 'videoId' => $streamVideo->videoId, 'videoName' => $streamVideo->videoName, 'starttime' => $streamVideo->starttime,];
    }
    $channelList = Channel::findAllByChannelIdList(array_unique(array_column($streamVideoMap, 'channelId')));
    $channelMap = [];
    foreach ($channelList as $channel) {
      $channelMap[$channel->channelId] = ['thumbnail' => $channel->thumbnail, 'channelName' => $channel->channelName, 'group' => $channel->group,];
    }

    return view('video/dailyStreamRanking', compact('dailyMap', 'streamVideoMap', 'channelMap', 'date'));
  }
}
