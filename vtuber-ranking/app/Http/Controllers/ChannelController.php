<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelData;
use App\Models\Video;
use Illuminate\Support\Facades\Cache;

class ChannelController extends Controller
{
    // cache time
    PRIVATE CONST CACHE_TIME = 30;

    public function index()
    {
        $channelList = Channel::all();

        return view('channel/index', compact('channelList'));
    }
  
    public function detail($channelId)
    {
        $channel = Cache::remember('channel_' . $channelId, self::CACHE_TIME, function () use ($channelId) {
            $channel = Channel::firstWhere('channelId', $channelId);
            return $channel;
          });
        $id = $channel->id;
        $channelDataList = Cache::remember('channelDataList_' . $id, self::CACHE_TIME, function () use ($id) {
            $channelDataList = ChannelData::where('channelId', $id)->orderBy('id', 'desc')->limit(5)->get();
            return $channelDataList;
          });
        $videoList = Cache::remember('videoList_' . $channelId, self::CACHE_TIME, function () use ($channelId) {
            $videoList = Video::where('channelId', $channelId)->whereNotNull('starttime')->orderBy('id', 'desc')->limit(5)->get();
            return $videoList;
          });

        return view('channel/detail', compact('channel', 'channelDataList', 'videoList'));
    }
}
