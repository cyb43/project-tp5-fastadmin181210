<?php

namespace app\admin\model;

use think\Cache;
use think\Model;

/**
 * Class AuthRule
 * @package app\admin\model
 * @author ^2_3^王尔贝
 */
class AuthRule extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected static function init()
    {
        // 更新缓存
        self::afterWrite(function ($row) {
            Cache::rm('__menu__');
        });
    }

    public function getTitleAttr($value, $data)
    {
        return __($value);
    }

}
