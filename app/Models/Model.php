<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\DB;

class Model extends BaseModel
{
    /**
     * 转换时间字符串为时间戳
     *
     * @param DateTime|int $value
     * @return DateTime|int
     */
    public function fromDateTime($value) {
        return $this->asDateTime($value)->timestamp;
    }
    
    /**
     * Insert Batch
     * @param $data
     */
    public static function insertBatch($data)
    {
        if(count($data) == count($data,1)) $data = [$data];    // 一维数组转二维数组
        $data = array_values($data);    // 去除键仅保留值
        $model = new static();
        $db = DB::connection($model->getConnectionName());
        if($model->timestamps) {
            $now = Carbon::now()->timestamp;
            $created = isset($data[0][$model->getCreatedAtColumn()]);
            $updated = isset($data[0][$model->getUpdatedAtColumn()]);
            foreach($data as $k => $v) {
                if(!$created) $data[$k][$model->getCreatedAtColumn()] = $now;
                if(!$updated) $data[$k][$model->getUpdatedAtColumn()] = $now;
            }
        }
        $value = '';
        foreach($data as $v) $value .= '("'. implode('","', str_replace('"', '\\"', array_values($v))) .'"),';
        $value = substr($value, 0, -1);
        $query = 'insert ignore into '. $db->getTablePrefix() . $model->getTable() .' (`'. implode('`,`', array_keys($data[0])) .'`) values '. $value;
        $db->insert($query);
    }
}
