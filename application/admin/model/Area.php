<?php

namespace app\admin\model;

use think\Model;

/**
 * 地区表模型
 * Class Area
 * @package app\admin\model
 * @author ^2_3^
 */
class Area extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

}
