<?php

namespace app\admin\model;

use think\Model;
use think\Session;

/**
 * [模型]_管理员表
 * Class Admin
 * @package app\admin\model
 * @author ^2_3^
 */
class Admin extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * [^2_3^]重置用户密码
     * @author baiyouwen
     */
    public function resetPassword($uid, $NewPassword)
    {
        // 加密密码
        $passwd = $this->encryptPassword($NewPassword);

        // 重置密码
        $ret = $this->where(['id' => $uid])->update(['password' => $passwd]);
        return $ret;
    }

    /**
     * 密码加密
     * @param $password
     * @param string $salt
     * @param string $encrypt
     * @return mixed
     * @author ^2_3^王尔贝
     */
    protected function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($password . $salt);
    }

}
