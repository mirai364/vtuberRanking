<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelData;
use App\Models\Video;
use App\Models\ConcurrentViewers;

class ChannelController extends Controller
{
    public function index()
    {
        $page = 1;
        $nowDate = date("Y-m-d", strtotime("-1 hour"));
        $channelDataList = ChannelData::findAllByNow($nowDate, $page);
        $channelIdList = [];
        foreach ($channelDataList as $channelData) {
            $channelIdList[] = $channelData->channelId;
        }
        $channelList = Channel::findAllByIdList($channelIdList);
        $channelMap = [];
        foreach ($channelList as $channel) {
            $channelMap[$channel->id] = $channel;
        }

        return view('channel/index', compact('channelDataList', 'channelMap'));
    }

    public function detail($channelId)
    {
        $channel = Channel::findBychannelId($channelId);
        if (empty($channel)) {
            return redirect('/');
        }
        $channelDataList = ChannelData::findBychannelId($channel->id);
        $videoList = Video::findAllByChannelId($channelId);
        $videoIdList = [];
        foreach ($videoList as $video) {
            $videoIdList[] = $video->id;
        }
        $concurrentViewersMap = ConcurrentViewers::getMaxMinByVideoList($channelId, $videoIdList);

        // chart用のデータを作成
        $subscribers = [];
        $play = [];
        $sorted = $channelDataList->sort();
        foreach ($sorted as $channelData) {
            $subscribers['label'][] = $channelData->createdAt->format('Y-m-d');
            $subscribers['data'][] = (int) $channelData->subscribers;
            $play['data'][] = (int) $channelData->play;
        }
        $subscribers = json_encode($subscribers);
        $play = json_encode($play);

        $channelDataList = $channelDataList->slice(0, 1);
        return view('channel/detail', compact('channel', 'channelDataList', 'videoList', 'subscribers', 'play', 'concurrentViewersMap'));
    }
}
