<?php

namespace app\admin\model;

use think\Model;

/**
 * Class Command
 * @package app\admin\model
 * @author ^2_3^
 */
class Command extends Model
{
    // 表名
    protected $name = 'command';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'executetime_text',
        'type_text',
        'status_text'
    ];

    /**
     * 获取状态列表
     * @return array
     * @author ^2_3^
     */
    public function getStatusList()
    {
        return ['successed' => __('Successed'), 'failured' => __('Failured')];
    }

    /**
     * 获取执行时间文本
     * @param $value
     * @param $data
     * @return false|string
     * @author ^2_3^
     */
    public function getExecutetimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['executetime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    /**
     * 获取类型文本
     * @param $value
     * @param $data
     * @return mixed|string
     * @author ^2_3^
     */
    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['type'];
        $list = ['crud' => '一键生成CRUD', 'menu' => '一键生成菜单', 'min' => '一键压缩打包', 'api' => '一键生成文档'];
        return isset($list[$value]) ? $list[$value] : '';
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

    /**
     * 设置执行时间
     * @param $value
     * @return false|int|string
     * @author ^2_3^
     */
    protected function setExecutetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
