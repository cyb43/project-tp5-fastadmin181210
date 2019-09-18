<?php

namespace app\admin\model;

use think\Model;

/**
 * 会员分组
 * Class UserGroup
 * @package app\admin\model
 * @author ^2_3^
 */
class UserGroup extends Model
{

    // 表名
    protected $name = 'user_group';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'status_text'
    ];

    /**
     * 状态列表
     * @return array
     * @author ^2_3^
     */
    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    /**
     * 获取状态文本
     * @param $value
     * @param $data
     * @return mixed|string
     * @author ^2_3^
     */
    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

}
