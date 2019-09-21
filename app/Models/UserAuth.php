<?php

namespace App\Models;

class UserAuth extends Model
{
    protected $fillable = ['user_id', 'type', 'identifier', 'certificate'];

    public static $types = [
        'PASSWORD', 'MOBILE', 'EMAIL', 'WECHAT', 'FACEBOOK'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
