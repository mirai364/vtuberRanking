<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Channel;
use App\Models\ChannelData;
use App\Models\Video;
use App\Models\ConcurrentViewers;
use Illuminate\Support\Facades\Cache;
use DateTime;

class VideoController extends Controller
{  
    // cache time
    PRIVATE CONST CACHE_TIME = 5;
    PRIVATE CONST CACHE_TIME_VIEWERS = 1;
    PRIVATE CONST CACHE_TIME_RANKING = 60 * 24 * 14;

    public function detail($videoId)
    {
        $video = Cache::remember('video_' . $videoId, self::CACHE_TIME, function () use ($videoId) {
            $video = Video::firstWhere('id', $videoId);
            return $video;
        });
        if (empty($video)) {
          return redirect('/');
        }
        $channelId = $video->channelId;
        $channel = Cache::remember('channel_' . $channelId, self::CACHE_TIME, function () use ($channelId) {
            $channel = Channel::firstWhere('channelId', $channelId);
            return $channel;
          });
        $id = $channel->id;
        // 共通のキャッシュを使用するため, limit:20で取得
        // TODO: helperに移動
        $channelDataList = Cache::remember('channelDataList_' . $id, self::CACHE_TIME, function () use ($id) {
            $channelDataList = ChannelData::where('channelId', $id)->orderBy('id', 'desc')->limit(20)->get();
            return $channelDataList;
          });
        $channelDataList = $channelDataList->slice(0,1);
        $concurrentViewersList = Cache::remember('concurrentViewersList_' . $videoId, self::CACHE_TIME_VIEWERS, function () use ($videoId) {
            $concurrentViewersList = ConcurrentViewers::where('videoId', $videoId)->orderBy('id', 'asc')->get();
            return $concurrentViewersList;
          });
        
        // chart用のデータを作成
        $data = [];
        foreach($concurrentViewersList as $concurrentViewers) {
             $data['label'][] = $concurrentViewers->createdAt->format('Y-m-d H:i:s');
             $data['data'][] = (int) $concurrentViewers->viewers;
        }
        $data = json_encode($data);

        return view('video/detail', compact('video', 'channel', 'channelDataList', 'data'));
    }

    public function streamRanking()
    {
      $streamVideoList = Cache::remember('streamVideoList', self::CACHE_TIME_VIEWERS, function () {
        return Video::where('isAlive', 1)->whereNotNull('starttime')->get();
      });
      $streamVideoMap = [];
      $channelIdList = [];
      $videoIdList = [];
      foreach ($streamVideoList as $streamVideo) {
        $videoIdList[] = $streamVideo->id;
        $channelIdList[] = $streamVideo->channelId;
        $streamVideoMap[$streamVideo->id] = ['channelId' => $streamVideo->channelId, 'videoName' => $streamVideo->videoName, 'starttime' => $streamVideo->starttime, ];
      }
      $targetDate = $streamVideoList->first()->updatedAt;
      $concurrentViewersList = Cache::remember('streamConcurrentViewersList', self::CACHE_TIME_VIEWERS, function () use ($videoIdList, $targetDate) {
        $pastTime = (clone $targetDate)->subSeconds(40);
        return ConcurrentViewers::whereIn('videoId', $videoIdList)->where('createdAt', '<', $targetDate)->where('createdAt', '>', $pastTime)->orderBy('viewers', 'desc')->get();
      });
      $channelList = Cache::remember('streamChannelList' , self::CACHE_TIME_VIEWERS, function () use ($channelIdList) {
        return Channel::whereIn('channelId', $channelIdList)->get();
      });
      $channelMap = [];
      foreach ($channelList as $channel) {
        $channelMap[$channel->channelId] = ['thumbnail' => $channel->thumbnail, 'channelName' => $channel->channelName, 'group' => $channel->group, ];
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
      if(strlen($date) < 8)
      {
        return [$now, null];
      }
      $targetDate = clone $now;
      // XXXX-XX-XX の形式になっているか？
      if(preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $date))
      {
        $targetDate = new DateTime($date . ' 23:59:59');
      }
      // XXXXXXXX の形式になっているか？
      if(preg_match('/\A[0-9]{8}\z/', $date))
      {
        $year = str_split($date, 4)[0];
        $month = str_split($date, 2)[2];
        $day = str_split($date, 2)[3];
        $date = $year . '-' . $month . '-' . $day;
        $targetDate = new DateTime($date . ' 23:59:59');
      }

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

      $hourlyMap=[];
      for ($i=1; $i<24; $i++) {
        $nowDate = (clone $targetDate)->modify('+ 1seconds')->modify('- '.$i.'Hours');
        if ($date !== null && $targetDate->format('Y-m-d') !== $nowDate->format('Y-m-d')) {
          break;
        }
        $hourlyMap[(int)$nowDate->format('H')] = Cache::remember('hourlyStreamConcurrentViewersList_' . $nowDate->format("YmdH"), self::CACHE_TIME_RANKING, function () use ($nowDate) {
          $concurrentViewersList =  ConcurrentViewers::select('videoId', 'viewers')
          ->selectRaw('HOUR(createdAt) AS time')
          ->whereDate('createdAt', '=', $nowDate->format("Y-m-d"))
          ->whereRaw('HOUR(createdAt) = ' . $nowDate->format('H'))
          ->orderBy('viewers', 'desc')->get();
          $hourlyMap = [];
          foreach ($concurrentViewersList as $concurrentViewers) {
            if (isset($hourlyMap[$concurrentViewers->videoId])) {
              continue;
            }
            $hourlyMap[$concurrentViewers->videoId] = ['videoId' => $concurrentViewers->videoId, 'viewers' => $concurrentViewers->viewers];
            if (count($hourlyMap) > 4) {
              break;
            }
          }
          return $hourlyMap;
        });
      }
      $videoIdList = array_unique(array_column(array_merge(...$hourlyMap),'videoId'));
      $streamVideoList = Cache::remember('hourlyStreamVideoList', self::CACHE_TIME_VIEWERS, function () use($videoIdList) {
        return Video::whereIN('id', $videoIdList)->get();
      });
      $streamVideoMap = [];
      $channelIdList = [];
      foreach ($streamVideoList as $streamVideo) {
        $streamVideoMap[$streamVideo->id] = ['channelId' => $streamVideo->channelId, 'videoId' => $streamVideo->videoId, 'videoName' => $streamVideo->videoName, 'starttime' => $streamVideo->starttime, ];
      }

      return view('video/hourlyStreamRanking', compact('hourlyMap', 'streamVideoMap', 'date'));
    }
}
