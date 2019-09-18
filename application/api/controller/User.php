<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Validate;

/**
 * 会员接口
 *
 * @author ^2_3^
 */
class User extends Api
{
    // 无需登录
    protected $noNeedLogin = [
        'login',
        'mobilelogin',
        'register',
        'resetpwd',
        'changeemail',
        'changemobile',
        'third'
    ];

    // 无需授权
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
     * 会员中心
     * @author ^2_3^
     */
    public function index()
    {
        // token -> ac5945fa-940c-460c-8c8e-5d4d787266fa
//        {
//            "code": 1,
//            "msg": "",
//            "time": "1560856470",
//            "data": {
//                    "welcome": "wangerbei"
//            }
//        }
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录
     * 
     * @param string $account 账号(email/mobile/username)
     * @param string $password 密码
     *
     * @author ^2_3^
     */
    public function login()
    {
        $account = $this->request->request('account');
        $password = $this->request->request('password');

        // 数据验证
        if (!$account || !$password)
        {
            $this->error(__('Invalid parameters'));
        }

        // 登录
        $ret = $this->auth->login($account, $password);
        if ($ret)
        {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);

            // 返回示例
//            {
//                "code": 1,
//                "msg": "登录成功",
//                "time": "1560857018",
//                "data": {
//                            "userinfo": {
//                                "id": 2,
//                        "username": "wangerbei",
//                        "nickname": "wangerbei",
//                        "mobile": "18520220243",
//                        "avatar": "/assets/img/avatar.png",
//                        "score": 0,
//                        "token": "28de8116-c4a3-43dd-b6c8-ceb487a7de08",
//                        "user_id": 2,
//                        "createtime": 1560857018,
//                        "expiretime": 1563449018,
//                        "expires_in": 2592000
//                    }
//                }
//            }

        }
        else
        {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     * 
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     *
     * @author ^2_3^
     */
    public function mobilelogin()
    {
        //// 登录凭证
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');

        // 数据验证
        if (!$mobile || !$captcha)
        {
            $this->error(__('Invalid parameters'));
        }

        // 号码正则验证
        if (!Validate::regex($mobile, "^1\d{10}$"))
        {
            $this->error(__('Mobile is incorrect'));
        }

        ////短讯服务（Short Messaging Service）
        if (!Sms::check($mobile, $captcha, 'mobilelogin'))
        {
            $this->error(__('Captcha is incorrect'));
        }

        $user = \app\common\model\User::getByMobile($mobile);
        if ($user)
        {
            // 登录 如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        }
        else
        {
            // 注册
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }

        if ($ret)
        {
            // 清空手机登录验证码
            Sms::flush($mobile, 'mobilelogin');

            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        }
        else
        {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     * 
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email 邮箱
     * @param string $mobile 手机号
     *
     * @author ^2_3^
     */
    public function register()
    {
        //// 注册信息
        $username = $this->request->request('username');
        $password = $this->request->request('password');
        $email = $this->request->request('email');
        $mobile = $this->request->request('mobile');

        //// 数据验证
        if (!$username || !$password)
        {
            $this->error(__('Invalid parameters'));
        }
        // 邮箱验证
        if ($email && !Validate::is($email, "email"))
        {
            $this->error(__('Email is incorrect'));
        }
        // 手机号码正则验证
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$"))
        {
            $this->error(__('Mobile is incorrect'));
        }

        // 注册
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret)
        {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        }
        else
        {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注销登录
     * @author ^2_3^
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     * 
     * @param string $avatar 头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio 个人简介
     *
     * @author ^2_3^
     */
    public function profile()
    {
        $user = $this->auth->getUser();

        //// 更新数据
        $username = $this->request->request('username');
        $nickname = $this->request->request('nickname');
        $bio = $this->request->request('bio');
        $avatar = $this->request->request('avatar');

        // 实时会员模型
        $exists = \app\common\model\User::where('username', $username)
            ->where('id', '<>', $this->auth->id)
            ->find();

        // 重名检测
        if ($exists)
        {
            $this->error(__('Username already exists'));
        }

        //// 更新信息
        $user->username = $username;
        $user->nickname = $nickname;
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱
     * 
     * @param string $email 邮箱
     * @param string $captcha 验证码
     *
     * @author ^2_3^
     */
    public function changeemail()
    {
        // 缓存数据
        $user = $this->auth->getUser();

        // 修改数据
        $email = $this->request->post('email');
        $captcha = $this->request->request('captcha');

        // 数据验证
        if (!$email || !$captcha)
        {
            $this->error(__('Invalid parameters'));
        }
        // 邮箱正则验证
        if (!Validate::is($email, "email"))
        {
            $this->error(__('Email is incorrect'));
        }
        // 邮箱是否存在
        if (
            \app\common\model\User::where('email', $email)
            ->where('id', '<>', $user->id)
            ->find()
        )
        {
            $this->error(__('Email already exists'));
        }


        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result)
        {
            $this->error(__('Captcha is incorrect'));
        }

        // 会员验证verification字段
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     * 
     * @param string $email 手机号
     * @param string $captcha 验证码
     *
     * @author ^2_3^
     */
    public function changemobile()
    {
        // 缓存的会员信息
        $user = $this->auth->getUser();

        // 修改数据
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');

        //// 数据验证
        if (!$mobile || !$captcha)
        {
            $this->error(__('Invalid parameters'));
        }
        // 正则验证手机号码
        if (!Validate::regex($mobile, "^1\d{10}$"))
        {
            $this->error(__('Mobile is incorrect'));
        }
        // 手机号码是否重复
        if (
            \app\common\model\User::where('mobile', $mobile)
                ->where('id', '<>', $user->id)
                ->find()
        )
        {
            $this->error(__('Mobile already exists'));
        }

        //// 验证码验证
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result)
        {
            $this->error(__('Captcha is incorrect'));
        }

        // 会员verification验证字段
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     * 
     * @param string $platform 平台名称
     * @param string $code Code码
     *
     * @author ^2_3^
     */
    public function third()
    {
        $url = url('user/index');

        //// 平台信息
        $platform = $this->request->request("platform");
        $code = $this->request->request("code");

        // 配置信息
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform]))
        {
            $this->error(__('Invalid parameters').'^2_3^');
        }

        //// 第三方登录
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result)
        {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret)
            {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }

        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     * 
     * @param string $mobile 手机号
     * @param string $newpassword 新密码
     * @param string $captcha 验证码
     *
     * @author ^2_3^
     */
    public function resetpwd()
    {
        //// 参数数据
        $type = $this->request->request("type"); //重置类型(mobile/email)
        $mobile = $this->request->request("mobile");
        $email = $this->request->request("email");
        $newpassword = $this->request->request("newpassword");
        $captcha = $this->request->request("captcha");

        //// 数据验证
        if (!$newpassword || !$captcha)
        {
            $this->error(__('Invalid parameters'));
        }

        if ($type == 'mobile')
        {
            // 正则验证号码
            if (!Validate::regex($mobile, "^1\d{10}$"))
            {
                $this->error(__('Mobile is incorrect'));
            }

            // 会员信息
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user)
            {
                $this->error(__('User not found'));
            }

            // 验证码检测
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret)
            {
                $this->error(__('Captcha is incorrect'));
            }

            // 清空验证码
            Sms::flush($mobile, 'resetpwd');
        }
        else
        {
            // 邮箱验证
            if (!Validate::is($email, "email"))
            {
                $this->error(__('Email is incorrect'));
            }

            // 会员模型
            $user = \app\common\model\User::getByEmail($email);
            if (!$user)
            {
                $this->error(__('User not found'));
            }

            // 验证码验证
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret)
            {
                $this->error(__('Captcha is incorrect'));
            }

            // 清空验证码
            Ems::flush($email, 'resetpwd');
        }

        //模拟一次登录
        $this->auth->direct($user->id);

        // 修改密码
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret)
        {
            $this->success(__('Reset password successful'));
        }
        else
        {
            $this->error($this->auth->getError());
        }
    }

}
