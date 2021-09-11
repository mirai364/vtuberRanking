<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
