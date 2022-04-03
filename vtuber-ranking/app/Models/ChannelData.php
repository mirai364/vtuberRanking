<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ChannelData extends Model
{
    /**
     * モデルに関連付けるテーブル
     *
     * @var string
     */
    protected $table = 'channelData';

    /**
     * テーブルに関連付ける主キー
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * モデルのIDを自動増分するか
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * モデルにタイムスタンプを付けるか
     *
     * @var bool
     */
    public $timestamps = true;

    const CREATED_AT = 'createdAt';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'channelId',
        'subscribers',
        'play',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'channelId' => 'int',
        'subscribers' => 'int',
        'play' => 'int',
    ];

    public static function findBychannelId($channelId)
    {
        $cacheTime = Channel::getNextCacheTime();
        return Cache::remember('channelDataList_' . $channelId, $cacheTime, function () use ($channelId) {
            return ChannelData::where('channelId', $channelId)->orderBy('id', 'desc')->limit(30)->get();
        });
    }

    public static function findAllByNow($now, $page)
    {
        $page = max(1, $page);
        $cacheTime = Channel::getNextCacheTime();
        return Cache::remember('channelDataList_page_' . $page, $cacheTime, function () use ($now, $page) {
            return ChannelData::whereDate('createdAt', '=', $now)
                ->orderBy('subscribers', 'desc')
                ->offset(($page - 1) * 100)
                ->limit(200)
                ->get();
        });
    }
}
