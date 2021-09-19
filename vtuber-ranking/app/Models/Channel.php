<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Channel extends Model
{
    /**
     * モデルに関連付けるテーブル
     *
     * @var string
     */
    protected $table = 'channel';

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
     * モデルの属性のデフォルト値
     *
     * @var array
     */
    protected $attributes = [
        'channelName' => Null,
        'group' => Null,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'channelId',
        'channelName',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'channelId' => 'string',
        'thumbnail' => 'string',
        'channelName' => 'string',
        'group' => 'string',
    ];

    public static function getNextCacheTime()
    {
        // 次の日の1時までキャッシュ
        $now = new DateTime();
        if ((int)$now->format('H') === 0) {
            $nextDay = (clone $now)->setTime(1,0,0);
        } else {
            $nextDay = (clone $now)->modify('+ 1days')->setTime(1,0,0);
        }

        return $nextDay->getTimestamp() - $now->getTimestamp();
    }

    public static function findBychannelId($channelId)
    {
        $cacheTime = self::getNextCacheTime();
        return Cache::remember('channel_' . $channelId, $cacheTime, function () use ($channelId) {
            return Channel::firstWhere('channelId', $channelId);
        });
    }

    public static function findAllByChannelIdList($channelIdList)
    {
        $modelList = [];
        $noCacheIdList = [];
        foreach ($channelIdList as $channelId) {
            $model = Cache::get('channel_' . $channelId);
            if ($model === null) {
                $noCacheIdList[] = $channelId;
            } else {
                $modelList[] = $model;
            }
        }
        if (empty($noCacheIdList)) {
            return $modelList;
        }

        $cacheTime = self::getNextCacheTime();
        $noCacheChannelList = Channel::whereIn('channelId', $noCacheIdList)->get();
        foreach($noCacheChannelList as $channel) {
            Cache::put('channel_' . $channel->channelId, $channel, $cacheTime);
            $modelList[] = $channel;
        }
        return $modelList;
    }

    public static function findAllByIdList($idList)
    {
        $modelList = [];
        $noCacheIdList = [];
        foreach ($idList as $id) {
            $model = Cache::get('channel_id_' . $id);
            if ($model === null) {
                $noCacheIdList[] = $id;
            } else {
                $modelList[] = $model;
            }
        }
        if (empty($noCacheIdList)) {
            return $modelList;
        }

        $cacheTime = self::getNextCacheTime();
        $noCacheChannelList = Channel::whereIn('id', $noCacheIdList)->get();
        foreach($noCacheChannelList as $channel) {
            Cache::put('channel_id_' . $channel->id, $channel, $cacheTime);
            $modelList[] = $channel;
        }
        return $modelList;
    }
}
