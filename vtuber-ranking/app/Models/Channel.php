<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
