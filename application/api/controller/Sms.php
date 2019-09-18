<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Sms as Smslib;
use app\common\model\User;

/**
 * 手机短信接口
 * @author ^2_3^
 */
class Sms extends Api
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 发送验证码
     *
     * @param string $mobile 手机号
     * @param string $event 事件名称
     *
     * @author ^2_3^
     */
    public function send()
    {
        //// 发送数据
        $mobile = $this->request->request("mobile");
        $event = $this->request->request("event");
        $event = $event ? $event : 'register';

        // 验证号码
        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }

        // 最新验证码
        $last = Smslib::get($mobile, $event);
        if ($last && time() - $last['createtime'] < 60) {
            $this->error(__('发送频繁'));
        }

        // 查询两个小时内的数据
        $ipSendTotal = \app\common\model\Sms::where(['ip' => $this->request->ip()])
            ->whereTime('createtime', '-1 hours')
            ->count();
        if ($ipSendTotal >= 5) {
            $this->error(__('发送频繁'));
        }

        if ($event) {
            $userinfo = User::getByMobile($mobile);

            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));

            } else if (in_array($event, ['changemobile']) && $userinfo) {
                //被占用
                $this->error(__('已被占用'));

            } else if (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }

        // 生成验证码(可通过添加sms_send行为执行发送短信)
        $ret = Smslib::send($mobile, NULL, $event);
        if ($ret) {
            $this->success(__('发送成功'));

        } else {
            $this->error(__('发送失败'));
        }
    }

    /**
     * 检测验证码
     *
     * @param string $mobile 手机号
     * @param string $event 事件名称
     * @param string $captcha 验证码
     *
     * @author ^2_3^
     */
    public function check()
    {
        //// 检测数据
        $mobile = $this->request->request("mobile");
        $event = $this->request->request("event");
        $event = $event ? $event : 'register';
        $captcha = $this->request->request("captcha");

        // 号码验证
        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }

        if ($event) {
            $userinfo = User::getByMobile($mobile);

            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));

            } else if (in_array($event, ['changemobile']) && $userinfo) {
                //被占用
                $this->error(__('已被占用'));

            } else if (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }

        // 检测验证码
        $ret = Smslib::check($mobile, $captcha, $event);
        if ($ret) {
            $this->success(__('成功'));

        } else {
            $this->error(__('验证码不正确'));
        }
    }

}
