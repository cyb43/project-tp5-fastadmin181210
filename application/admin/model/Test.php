<?php

namespace app\admin\model;

use think\Model;

/**
 * 测试模型
 * Class Test
 * @package app\admin\model
 * @author ^2_3^王尔贝
 */
class Test extends Model
{
    // 表名
    protected $name = 'test';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'week_text',
        'flag_text',
        'genderdata_text',
        'hobbydata_text',
        'refreshtime_text',
        'status_text',
        'state_text'
    ];

    /**
     * 自定义初始化
     * @author ^2_3^
     */
    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();

            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    /**
     * 星期列表
     * 星期(单选):monday=星期一,tuesday=星期二,wednesday=星期三
     * @return array
     * @author ^2_3^
     */
    public function getWeekList()
    {
        return ['monday' => __('Week monday'),'tuesday' => __('Week tuesday'),'wednesday' => __('Week wednesday')];
    }

    /**
     * 标志列表
     * 标志(多选):hot=热门,index=首页,recommend=推荐
     * @return array
     * @author ^2_3^
     */
    public function getFlagList()
    {
        return ['hot' => __('Flag hot'),'index' => __('Flag index'),'recommend' => __('Flag recommend')];
    }

    /**
     * 性别列表
     * 性别(单选):male=男,female=女
     * @return array
     * @author ^2_3^
     */
    public function getGenderdataList()
    {
        return ['male' => __('Genderdata male'),'female' => __('Genderdata female')];
    }

    /**
     * 爱好列表
     * 爱好(多选):music=音乐,reading=读书,swimming=游泳
     * @return array
     * @author ^2_3^
     */
    public function getHobbydataList()
    {
        return ['music' => __('Hobbydata music'),'reading' => __('Hobbydata reading'),'swimming' => __('Hobbydata swimming')];
    }

    /**
     * 状态列表
     * @return array
     * @author ^2_3^
     */
    public function getStatusList()
    {
        return ['normal' => __('Normal'),'hidden' => __('Hidden')];
    }

    /**
     * 状态列表
     * 状态值:0=禁用,1=正常,2=推荐
     * @return array
     * @author ^2_3^
     */
    public function getStateList()
    {
        return ['0' => __('State 0'),'1' => __('State 1'),'2' => __('State 2')];
    }

    /**
     * 获取星期文本
     * @param $value
     * @param $data
     * @return mixed|string
     * @author ^2_3^王尔贝
     */
    public function getWeekTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['week']) ? $data['week'] : '');
        $list = $this->getWeekList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    /**
     * 获取标志文本
     * @param $value
     * @param $data
     * @return string
     * @author ^2_3^王尔贝
     */
    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['flag']) ? $data['flag'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getFlagList();

        // 交集部分
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    /**
     * 获取性别文本
     * @param $value
     * @param $data
     * @return mixed|string
     * @author ^2_3^
     */
    public function getGenderdataTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['genderdata']) ? $data['genderdata'] : '');
        $list = $this->getGenderdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    /**
     * 获取爱好文本
     * @param $value
     * @param $data
     * @return string
     * @author ^2_3^
     */
    public function getHobbydataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['hobbydata']) ? $data['hobbydata'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getHobbydataList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    /**
     * 刷新时间文本
     * @param $value
     * @param $data
     * @return false|string
     * @author ^2_3^
     */
    public function getRefreshtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['refreshtime']) ? $data['refreshtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
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
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    /**
     * 获取状态列表文本
     * @param $value
     * @param $data
     * @return mixed|string
     * @author ^2_3^
     */
    public function getStateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['state']) ? $data['state'] : '');
        $list = $this->getStateList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    /**
     * 设置标志
     * @param $value
     * @return string
     * @author ^2_3^
     */
    protected function setFlagAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * 设置爱好
     * @param $value
     * @return string
     * @author ^2_3^
     */
    protected function setHobbydataAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * 设置刷新时间
     * @param $value
     * @return false|int|string
     * @author ^2_3^
     */
    protected function setRefreshtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
