<?php

namespace App\Models;

use Carbon\Carbon;

class UserAuthLog extends Model
{
    protected $fillable = ['identifier', 'action'];

    /**
     * 重试次数（1分钟内）
     * @return mixed
     */
    public function retrys () {
        $minute = Carbon::now()->subMinute()->timestamp;
        return UserAuthLog::where('created_at', '>', $minute)->where(['identifier' => $this->identifier, 'action' => $this->action])->count();
    }
}
