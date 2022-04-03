<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Video extends Model
{
    /**
     * モデルに関連付けるテーブル
     *
     * @var string
     */
    protected $table = 'video';

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

    const UPDATED_AT = 'updatedAt';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'channelId',
        'videoId',
        'videoName',
        'isAlive',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'channelId' => 'string',
        'videoId' => 'string',
        'videoName' => 'string',
        'starttime' => 'datetime',
        'isAlive' => 'bool',
    ];

    private const CACHE_TIME_VIEWERS = 1 * 60;
    private const CACHE_TIME = 5 * 60;

    public static function findByVideoId($videoId)
    {
        return Cache::remember('videoId_' . $videoId, self::CACHE_TIME_VIEWERS, function () use ($videoId) {
            return Video::firstWhere('videoId', $videoId);
        });
    }

    public static function findAllByIdList($idList)
    {
        $modelList = [];
        $noCacheIdList = [];
        foreach ($idList as $id) {
            $model = Cache::get('video_' . $id);
            if ($model === null) {
                $noCacheIdList[] = $id;
            } else {
                $modelList[] = $model;
            }
        }
        if (empty($noCacheIdList)) {
            return $modelList;
        }

        $noCacheModelList = Video::whereIn('id', $noCacheIdList)->get();
        foreach ($noCacheModelList as $noCacheModel) {
            Cache::put('video_' . $noCacheModel->id, $noCacheModel, self::CACHE_TIME_VIEWERS);
            $modelList[] = $noCacheModel;
        }
        return $modelList;
    }

    public static function findAllByIsAlive()
    {
        return Cache::remember('streamVideoList', self::CACHE_TIME_VIEWERS, function () {
            return Video::where('isAlive', 1)->whereNotNull('starttime')->get();
        });
    }

    public static function findAllByChannelId($channelId)
    {
        return Cache::remember('videoList_' . $channelId, self::CACHE_TIME, function () use ($channelId) {
            return Video::where('channelId', $channelId)
                ->whereIn('isAlive', [0,1,3])
                ->whereNotNull('starttime')
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get();
        });
    }
}
