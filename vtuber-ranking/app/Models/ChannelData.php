<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
