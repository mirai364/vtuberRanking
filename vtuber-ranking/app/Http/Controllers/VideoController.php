<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Channel;
use App\Models\ChannelData;
use App\Models\Video;
use App\Models\ConcurrentViewers;
use Illuminate\Support\Facades\Cache;

class VideoController extends Controller
{  
    // cache time
    PRIVATE CONST CACHE_TIME = 5;
    PRIVATE CONST CACHE_TIME_VIEWERS = 1;

    public function detail($videoId)
    {
        $video = Cache::remember('video_' . $videoId, self::CACHE_TIME, function () use ($videoId) {
            $video = Video::firstWhere('id', $videoId);
            return $video;
          });
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
}
