<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Session;
use think\Validate;

/**
 * 会员中心
 * @author ^2_3^
 */
class User extends Frontend
{

    // 布局
    protected $layout = 'default';
    protected $noNeedLogin = ['login', 'register', 'third'];
    protected $noNeedRight = ['*'];

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();

        //  授权类
        $auth = $this->auth;

        // 是否开启前台会员中心
        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

        // ucenter插件
        $ucenter = get_addon_info('ucenter');
        if ($ucenter && $ucenter['state']) {
            include ADDON_PATH . 'ucenter' . DS . 'uc.php';
        }

        //// 监听注册/登录/注销的事件
        // 登录成功
        Hook::add('user_login_successed', function ($user) use ($auth) {
            // 30天
            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
            Cookie::set('uid', $user->id, $expire);
            Cookie::set('token', $auth->getToken(), $expire);
        });
        // 注册成功
        Hook::add('user_register_successed', function ($user) use ($auth) {
            Cookie::set('uid', $user->id);
            Cookie::set('token', $auth->getToken());
        });
        // 会员删除成功
        Hook::add('user_delete_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        // 注销成功
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
    }

    /**
     * 空请求/空方法
     * @param $name
     * @return mixed
     * @author ^2_3^
     */
    public function _empty($name)
    {
        $data = Hook::listen("user_request_empty", $name);

        foreach ($data as $index => $datum) {
            $this->view->assign($datum);
        }

        return $this->view->fetch('user/' . $name);
    }

    /**
     * 会员中心
     * @author ^2_3^
     */
    public function index()
    {
        $this->view->assign('title', __('User center'));
        return $this->view->fetch();
    }

    /**
     * 注册会员
     * @author ^2_3^
     */
    public function register()
    {
        $url = $this->request->request('url');

        if ($this->auth->id) {
            $this->success(__('You\'ve logged in, do not login again'), $url);
        }

        if ($this->request->isPost()) {
            //// 注册数据
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $email = $this->request->post('email');
            $mobile = $this->request->post('mobile', '');
            $captcha = $this->request->post('captcha');
            $token = $this->request->post('__token__');

            //// 数据规则
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:6,30',
                'email'     => 'require|email',
                'mobile'    => 'regex:/^1\d{10}$/',
                'captcha'   => 'require|captcha',
                '__token__' => 'token',
            ];

            //// 数据提示
            $msg = [
                'username.require' => 'Username can not be empty',
                'username.length'  => 'Username must be 3 to 30 characters',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
                'captcha.require'  => 'Captcha can not be empty',
                'captcha.captcha'  => 'Captcha is incorrect',
                'email'            => 'Email is incorrect',
                'mobile'           => 'Mobile is incorrect',
            ];

            //// 注册数据
            $data = [
                'username'  => $username,
                'password'  => $password,
                'email'     => $email,
                'mobile'    => $mobile,
                'captcha'   => $captcha,
                '__token__' => $token,
            ];

            //// 数据验证
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
            }

            //// 会员注册
            if ($this->auth->register($username, $password, $email, $mobile)) {

                $synchtml = '';
                ////////////////同步到Ucenter////////////////
                if (defined('UC_STATUS') && UC_STATUS) {
                    $uc = new \addons\ucenter\library\client\Client();
                    $synchtml = $uc->uc_user_synregister($this->auth->id, $password);
                }

                $this->success(__('Sign up successful') . $synchtml,
                    $url ? $url : url('user/index'));

            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }

        //// 判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register)/i", $referer)) {
            $url = $referer;
        }

        $this->view->assign('url', $url);
        $this->view->assign('title', __('Register'));
        return $this->view->fetch();
    }

    /**
     * 会员登录
     * @access http://project-tp5-fastadmin181210.test/index/user/login.html
     * @author ^2_3^
     */
    public function login()
    {
        $url = $this->request->request('url');

        // 已经登录
        if ($this->auth->id) {
            $this->success(__('You\'ve logged in, do not login again'), $url);
        }

        if ($this->request->isPost()) {
            //// 登录数据
            $account = $this->request->post('account');
            $password = $this->request->post('password');
            $keeplogin = (int)$this->request->post('keeplogin');
            $token = $this->request->post('__token__');

            //// 数据规则
            $rule = [
                'account'   => 'require|length:3,50',
                'password'  => 'require|length:6,30',
                '__token__' => 'token',
            ];

            //// 数据提示
            $msg = [
                'account.require'  => 'Account can not be empty',
                'account.length'   => 'Account must be 3 to 50 characters',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
            ];

            //// 登录数据
            $data = [
                'account'   => $account,
                'password'  => $password,
                '__token__' => $token,
            ];

            //// 数据验证(验证类)
            $validate = new Validate($rule, $msg);
            // 验证
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
                return FALSE;
            }
            // 登录
            if ($this->auth->login($account, $password)) {
                $synchtml = '';

                ////////////////同步到Ucenter////////////////
                if (defined('UC_STATUS') && UC_STATUS) {
                    $uc = new \addons\ucenter\library\client\Client();
                    $synchtml = $uc->uc_user_synlogin($this->auth->id);
                }

                $this->success(__('Logged in successful') . $synchtml,
                    $url ? $url : url('user/index'));

            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }

        //// 判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Login'));
        return $this->view->fetch();
    }

    /**
     * 注销登录
     * @author ^2_3^
     */
    function logout()
    {
        // 注销本站
        $this->auth->logout();

        $synchtml = '';
        ////////////////同步到Ucenter////////////////
        if (defined('UC_STATUS') && UC_STATUS) {
            $uc = new \addons\ucenter\library\client\Client();
            $synchtml = $uc->uc_user_synlogout();
        }
        $this->success(__('Logout successful') . $synchtml, url('user/index'));
    }

    /**
     * 个人信息
     * @author ^2_3^
     */
    public function profile()
    {
        $this->view->assign('title', __('Profile'));
        return $this->view->fetch();
    }

    /**
     * 修改密码
     * @author ^2_3^
     */
    public function changepwd()
    {
        if ($this->request->isPost()) {
            //// 修改数据
            $oldpassword = $this->request->post("oldpassword");
            $newpassword = $this->request->post("newpassword");
            $renewpassword = $this->request->post("renewpassword");
            $token = $this->request->post('__token__'); //表单令牌;

            //// 数据规则
            $rule = [
                'oldpassword'   => 'require|length:6,30',
                'newpassword'   => 'require|length:6,30',
                'renewpassword' => 'require|length:6,30|confirm:newpassword',
                '__token__'     => 'token',
            ];

            //// 数据提示
            $msg = [
            ];

            //// 修改数据
            $data = [
                'oldpassword'   => $oldpassword,
                'newpassword'   => $newpassword,
                'renewpassword' => $renewpassword,
                '__token__'     => $token,
            ];

            //// 验证字段描述信息
            $field = [
                'oldpassword'   => __('Old password'),
                'newpassword'   => __('New password'),
                'renewpassword' => __('Renew password')
            ];

            //// 数据验证
            $validate = new Validate($rule, $msg, $field);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
                return FALSE;
            }

            ///// 修改密码
            $ret = $this->auth->changepwd($newpassword, $oldpassword);
            if ($ret) {
                $synchtml = '';

                ////////////////同步到Ucenter////////////////
                if (defined('UC_STATUS') && UC_STATUS) {
                    $uc = new \addons\ucenter\library\client\Client();
                    $synchtml = $uc->uc_user_synlogout();
                }
                $this->success(__('Reset password successful') . $synchtml, url('user/login'));

            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }

        $this->view->assign('title', __('Change password'));
        return $this->view->fetch();
    }

}
