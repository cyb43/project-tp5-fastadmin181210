<?php

namespace app\admin\model;

use app\common\model\MoneyLog;
use think\Model;

/**
 * 会员模型
 * Class User
 * @package app\admin\model
 * @author ^2_3^
 */
class User extends Model
{

    // 表名
    protected $name = 'user';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'prevtime_text',
        'logintime_text',
        'jointime_text'
    ];

    /**
     * 获取原始数据
     * @return array|object
     * @author ^2_3^
     */
    public function getOriginData()
    {
        return $this->origin;
    }

    /**
     * 初始化
     * @author ^23^
     */
    protected static function init()
    {
        //// 更新之前
        self::beforeUpdate(function ($row) {
            $changed = $row->getChangedData();

            //如果有修改密码
            if (isset($changed['password'])) {
                if ($changed['password']) {
                    $salt = \fast\Random::alnum();
                    $row->password = \app\common\library\Auth::instance()
                        ->getEncryptPassword($changed['password'], $salt);
                    $row->salt = $salt;

                } else {
                    unset($row->password);
                }
            }
        });


        self::beforeUpdate(function ($row) {
            $changedata = $row->getChangedData();

            if (isset($changedata['money'])) {
                $origin = $row->getOriginData();
                MoneyLog::create([
                    'user_id' => $row['id'],
                    'money' => $changedata['money'] - $origin['money'],
                    'before' => $origin['money'],
                    'after' => $changedata['money'],
                    'memo' => '管理员变更金额'
                ]);
            }
        });
    }

    /**
     * 性别列表
     * @return array
     * @author ^2_3^
     */
    public function getGenderList()
    {
        return ['1' => __('Male'), '0' => __('Female')];
    }

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
     * 上次登录时间文本
     * @param $value
     * @param $data
     * @return false|string
     * @author ^2_3^
     */
    public function getPrevtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['prevtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    /**
     * 登录时间文本
     * @param $value
     * @param $data
     * @return false|string
     * @author ^2_3^
     */
    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['logintime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    /**
     * 加入时间文本
     * @param $value
     * @param $data
     * @return false|string
     * @author ^2_3^
     */
    public function getJointimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['jointime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    /**
     * 设置上次登录时间
     * @param $value
     * @return false|int|string
     * @author ^2_3^
     */
    protected function setPrevtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    /**
     * 设置登录时间
     * @param $value
     * @return false|int|string
     * @author ^2_3^
     */
    protected function setLogintimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    /**
     * 设置加入时间
     * @param $value
     * @return false|int|string
     * @author ^2_3^
     */
    protected function setJointimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    /**
     * 关联模型
     * @return $this
     * @author ^2_3^
     */
    public function group()
    {
        return $this->belongsTo('UserGroup', 'group_id', 'id', [], 'LEFT')
            ->setEagerlyType(0);
    }

}
