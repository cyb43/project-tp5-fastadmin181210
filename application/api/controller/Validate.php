<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\User;

/**
 * 验证接口
 * @author ^2_3^
 */
class Validate extends Api
{

    protected $noNeedLogin = '*';
    protected $layout = '';
    protected $error = null;

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 检测邮箱
     * 
     * @param string $email 邮箱
     * @param string $id 排除会员ID
     *
     * @author ^2_3^
     */
    public function check_email_available()
    {
        //// 检测数据
        $email = $this->request->request('email');
        $id = (int) $this->request->request('id');

        //// 检测
        $count = User::where('email', '=', $email)
            ->where('id', '<>', $id)
            ->count();
        if ($count > 0)
        {
            $this->error(__('邮箱已经被占用'));
        }
        $this->success();
    }

    /**
     * 检测用户名
     * 
     * @param string $username 用户名
     * @param string $id 排除会员ID
     *
     * @author ^2_3^
     */
    public function check_username_available()
    {
        //// 检测数据
        $email = $this->request->request('username');
        $id = (int) $this->request->request('id');

        //// 检测
        $count = User::where('username', '=', $email)
            ->where('id', '<>', $id)
            ->count();
        if ($count > 0)
        {
            $this->error(__('用户名已经被占用'));
        }
        $this->success();
    }

    /**
     * 检测手机
     * 
     * @param string $mobile 手机号
     * @param string $id 排除会员ID
     *
     * @author ^2_3^
     */
    public function check_mobile_available()
    {
        //// 检测数据(我喜欢萨克斯的声音，很动人很伤感令人充满怀念(逝去的时光，凋谢的永恒)。_^2_3^王尔贝(2019-6-20))
        $mobile = $this->request->request('mobile');
        $id = (int) $this->request->request('id');

        //// 检测
        $count = User::where('mobile', '=', $mobile)
            ->where('id', '<>', $id)
            ->count();
        if ($count > 0)
        {
            $this->error(__('该手机号已经占用'));
        }
        $this->success();
    }

    /**
     * 检测手机
     * 
     * @param string $mobile 手机号
     *
     * @author ^2_3^
     */
    public function check_mobile_exist()
    {
        //// 检测数据
        $mobile = $this->request->request('mobile');
        $count = User::where('mobile', '=', $mobile)->count();

        if (!$count)
        {
            $this->error(__('手机号不存在'));
        }
        $this->success();
    }

    /**
     * 检测邮箱
     * 
     * @param string $mobile 邮箱
     *
     * @author ^2_3^
     */
    public function check_email_exist()
    {
        //// 检测数据
        $email = $this->request->request('email');
        $count = User::where('email', '=', $email)->count();
        if (!$count)
        {
            $this->error(__('邮箱不存在'));
        }
        $this->success();
    }

    /**
     * 检测手机验证码
     * 
     * @param string $mobile    手机号
     * @param string $captcha   验证码
     * @param string $event     事件
     *
     * @author ^2_3^
     */
    public function check_sms_correct()
    {
        //// 检测数据
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        $event = $this->request->request('event');

        //// 检测验证码
        if (!\app\common\library\Sms::check($mobile, $captcha, $event))
        {
            $this->error(__('验证码不正确'));
        }
        $this->success();
    }

    /**
     * 检测邮箱验证码
     * 
     * @param string $email     邮箱
     * @param string $captcha   验证码
     * @param string $event     事件
     *
     * @author ^2_3^
     */
    public function check_ems_correct()
    {
        //// 检测数据
        $email = $this->request->request('email');
        $captcha = $this->request->request('captcha');
        $event = $this->request->request('event');

        //// 检测邮箱验证码
        if (!\app\common\library\Ems::check($email, $captcha, $event))
        {
            $this->error(__('验证码不正确'));
        }
        $this->success();
    }

}
