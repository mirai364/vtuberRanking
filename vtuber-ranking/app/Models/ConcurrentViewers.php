<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ConcurrentViewers extends Model
{
    /**
     * モデルに関連付けるテーブル
     *
     * @var string
     */
    protected $table = 'concurrentViewers';

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
        'videoId',
        'viewers',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'videoId' => 'int',
        'viewers' => 'int',
    ];

    private const CACHE_TIME_VIEWERS = 1 * 60;
    private const CACHE_TIME = 5 * 60;
    public static function findAllByVideoId($videoId)
    {
        return Cache::remember('concurrentViewersList_' . $videoId, self::CACHE_TIME_VIEWERS, function () use ($videoId) {
            return ConcurrentViewers::where('videoId', $videoId)->orderBy('createdAt', 'asc')->get();
        });
    }

    public static function findAllByVideoIdListAndDate($videoIdList, $date)
    {
        return Cache::remember('streamConcurrentViewersList', self::CACHE_TIME_VIEWERS, function () use ($videoIdList, $date) {
            return ConcurrentViewers::whereIn('videoId', $videoIdList)
                ->where('ddate', $date->format('Ymd'))
                ->where('dhour', $date->format('H'))
                ->where('dminutes', $date->format('i'))
                ->orderBy('viewers', 'desc')
                ->get();
        });
    }

    public static function getMaxMinByVideoList($channelId, $videoIdList)
    {
        return Cache::remember('streamConcurrentViewersList_' . $channelId, self::CACHE_TIME, function () use ($videoIdList) {
            $concurrentViewersList = ConcurrentViewers::select('videoId')
                ->selectRaw('MAX(viewers) AS max')
                ->selectRaw('AVG(viewers) AS avg')
                ->selectRaw('SUM(viewers) AS sum')
                ->whereIn('videoId', $videoIdList)
                ->groupBy('videoId')
                ->get();
            $concurrentViewersMap = [];
            foreach ($concurrentViewersList as $concurrentViewers) {
                $concurrentViewersMap[$concurrentViewers->videoId] = [
                    'max' => $concurrentViewers->max,
                    'avg' => $concurrentViewers->avg,
                    'sum' => $concurrentViewers->sum,
                ];
            }
            return $concurrentViewersMap;
        });
    }

    public static function getHourlyStreamConcurrentViewersList($nowDate)
    {
        return Cache::rememberForever('hourlyStreamConcurrentViewersList_' . $nowDate->format("YmdH"), function () use ($nowDate) {
            $concurrentViewersList =  ConcurrentViewers::select('videoId', 'viewers')
                ->selectRaw('dhour AS time')
                ->where('ddate', $nowDate->format('Ymd'))
                ->where('dhour', $nowDate->format('H'))
                ->orderBy('viewers', 'desc')->get();
            $hourlyMap = [];
            foreach ($concurrentViewersList as $concurrentViewers) {
                if (isset($hourlyMap[$concurrentViewers->videoId])) {
                    continue;
                }
                $hourlyMap[$concurrentViewers->videoId] = [
                    'videoId' => $concurrentViewers->videoId,
                    'viewers' => $concurrentViewers->viewers
                ];
                if (count($hourlyMap) > 4) {
                    break;
                }
            }
            return $hourlyMap;
        });
    }

    public static function getDailyStreamConcurrentViewersList($targetDate, $cacheTime)
    {
        if ($cacheTime === null) {
            return Cache::rememberForever('dailyStreamConcurrentViewersList_' . $targetDate->format("Ymd"), function () use ($targetDate) {
                $concurrentViewersList =  ConcurrentViewers::select('videoId', 'viewers')
                    ->selectRaw('dhour AS time')
                    ->where('ddate', $targetDate->format('Ymd'))
                    ->orderBy('viewers', 'desc')->get();
                $dailyMap = [];
                foreach ($concurrentViewersList as $concurrentViewers) {
                    if (isset($dailyMap[$concurrentViewers->videoId])) {
                        continue;
                    }
                    $dailyMap[$concurrentViewers->videoId] = ['videoId' => $concurrentViewers->videoId, 'viewers' => $concurrentViewers->viewers];
                    if (count($dailyMap) > 100) {
                        break;
                    }
                }
                return $dailyMap;
            });
        }
        return Cache::remember('dailyStreamConcurrentViewersList_' . $targetDate->format("Ymd"), $cacheTime, function () use ($targetDate) {
            $concurrentViewersList =  ConcurrentViewers::select('videoId', 'viewers')
                ->selectRaw('dhour AS time')
                ->where('ddate', $targetDate->format('Ymd'))
                ->orderBy('viewers', 'desc')->get();
            $dailyMap = [];
            foreach ($concurrentViewersList as $concurrentViewers) {
                if (isset($dailyMap[$concurrentViewers->videoId])) {
                    continue;
                }
                $dailyMap[$concurrentViewers->videoId] = ['videoId' => $concurrentViewers->videoId, 'viewers' => $concurrentViewers->viewers];
                if (count($dailyMap) > 100) {
                    break;
                }
            }
            return $dailyMap;
        });
    }
}
