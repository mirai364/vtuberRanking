<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelData;
use App\Models\Video;
use DateTime;
use Illuminate\Support\Facades\Cache;

class ChannelController extends Controller
{
    // cache time
    PRIVATE CONST CACHE_TIME = 5;
    // cache time
    PRIVATE CONST CACHE_TIME_CHANNEL = 24 * 60;

    public function index()
    {
        $page = 1;
        $nowDate = date("Y-m-d",strtotime("-1 hour"));
        $channelDataList = Cache::remember('channelDataList_page_' . $page, self::CACHE_TIME_CHANNEL, function () use ($nowDate, $page) {
          $channelDataList = ChannelData::whereDate('createdAt', '=', $nowDate)->orderBy('subscribers', 'desc')->offset(($page-1) * 100)->limit(200)->get();
          return $channelDataList;
        });
        $channelIdList = [];
        foreach ($channelDataList as $channelData) {
          $channelIdList[] = $channelData->channelId;
        }
        $channelList = Cache::remember('channel_page_' . $page, self::CACHE_TIME_CHANNEL, function () use ($channelIdList) {
          $channelList = Channel::whereIn('id', $channelIdList)->get();
          return $channelList;
        });
        $channelMap = [];
        foreach($channelList as $channel) {
          $channelMap[$channel->id] = $channel;
        }

        return view('channel/index', compact('channelDataList', 'channelMap'));
    }
  
    public function detail($channelId)
    {
        $channel = Cache::remember('channel_' . $channelId, self::CACHE_TIME, function () use ($channelId) {
            $channel = Channel::firstWhere('channelId', $channelId);
            return $channel;
          });
        $id = $channel->id;
        $channelDataList = Cache::remember('channelDataList_' . $id, self::CACHE_TIME, function () use ($id) {
            $channelDataList = ChannelData::where('channelId', $id)->orderBy('id', 'desc')->limit(20)->get();
            return $channelDataList;
          });
        $videoList = Cache::remember('videoList_' . $channelId, self::CACHE_TIME, function () use ($channelId) {
            $videoList = Video::where('channelId', $channelId)->whereNotNull('starttime')->orderBy('id', 'desc')->limit(5)->get();
            return $videoList;
          });

        // chart用のデータを作成
        $subscribers = [];
        $play = [];
        $sorted = $channelDataList->sort();
        foreach($sorted as $channelData) {
             $subscribers['label'][] = $channelData->createdAt->format('Y-m-d');
             $subscribers['data'][] = (int) $channelData->subscribers;
             $play['data'][] = (int) $channelData->play;
        }
        $subscribers = json_encode($subscribers);
        $play = json_encode($play);
        $channelDataList = $channelDataList->slice(0,1);

        return view('channel/detail', compact('channel', 'channelDataList', 'videoList', 'subscribers', 'play'));
    }
}
