<?php

namespace app\admin\controller;

use app\admin\model\AdminLog;
use app\common\controller\Backend;
use think\Config;
use think\Hook;
use think\Session;
use think\Validate;

/**
 * 后台首页
 * @internal
 * @author ^2_3^
 */
class Index extends Backend
{

    protected $noNeedLogin = ['login'];
    protected $noNeedRight = ['index', 'logout'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();

        // http://project-tp5-fastadmin181210.test/admin/index/index
//        $num = 0;
//        for ( $i = 0; $i < 78; $i++ ) {
//            $num += 1;
//            $num = str_pad($num, 4, '0', STR_PAD_LEFT);
//            $url = "http://mp3-f.ting89.com:9090/2018/36/超级贴身保镖/{$num}.mp3";
//            file_put_contents('^2_3^.txt', $url."\r\n", FILE_APPEND);
//        }
//        dump( $num );
//        exit();

    }

    /**
     * 后台首页
     * @author ^2_3^
     */
    public function index()
    {
        //// 左侧菜单
        // $menulist 左边菜单;
        // $navlist 顶部TAB导航;
        // $fixedmenu 后台指定菜单;
        // $referermenu 来源菜单;
        list($menulist, $navlist, $fixedmenu, $referermenu) = $this->auth->getSidebar([
                //// 左侧菜单角标(徽章)设置
                // 数组的键名是对应的左侧菜单栏的相对链接，数组的键值是需要显示的文字或数字，可以传字符串或数组。
                // 如果是字符串，则角标(徽章)的颜色是按照'red', 'green', 'yellow', 'blue', 'teal', 'orange', 'purple'的方式进行循环的。
                // 如果是数组，这三个值分别表示：[显示的文字, 颜色，展现方式(badge或label)]。
                // 如果需要删除这个小角标，则可以直接到数组置为空即可。在这里仅仅是PHP端操作小角标的方式，在JS端同样可以进行相应的操作。
                'dashboard' => 'hot',
                'addon'     => ['new', 'red', 'badge'],
                'auth/rule' => __('Menu'),
                'general'   => ['new', 'purple'],
            ],
                $this->view->site['fixedpage'] //值为dashboard，默认页，会跳转到admin/dashboard/index；
            );

        // 控制器名
        $action = $this->request->request('action');
        if ($this->request->isPost()) {
            if ($action == 'refreshmenu') {
                $this->success('', null, ['menulist' => $menulist, 'navlist' => $navlist]);
            }
        }

        $this->view->assign('menulist', $menulist);
        $this->view->assign('navlist', $navlist);
        $this->view->assign('fixedmenu', $fixedmenu); //后台指定菜单;
        $this->view->assign('referermenu', $referermenu); //来源菜单;
        $this->view->assign('title', __('Home'));

//        dump( "^2_3^ 后台首页('admin/index/index')，会跳转到admin/dashboard/index;");
//        exit();

        return $this->view->fetch();
    }

    /**
     * 最新信息
     * @return array
     * @author ^2_3^
     */
    public function news()
    {
        $callback = $this->request->param('callback');
        $data_news = array(
            'newslist' => array(
                array(
                    'id' => 1,
                    'title' => '标题',
                    'icon' => 'glyphicon glyphicon-hand-right',
                    'url' => 'http://cybao.club'
                )
            ),
            'new' => 1,
            'url' => '/admin/index/news'
        );
//        $data = json_encode( $data_news );
//        echo $callback.'('.$data.')';

        return jsonp( $data_news );
    }

    /**
     * 管理员登录
     * @author ^2_3^
     */
    public function login()
    {
        // 后台中心
        $url = $this->request->get('url', 'index/index');

        // 自动登录
        if ($this->auth->isLogin()) {
            $this->success(__("You've logged in, do not login again"), $url);
        }

        //// 登录请求
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');

            //// 数据验证
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:3,30',
                '__token__' => 'token',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                '__token__' => $token,
            ];
            /// 需要登录验证码
            if (Config::get('fastadmin.login_captcha')) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = $this->request->post('captcha');
            }
            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password'), 'captcha' => __('Captcha')]);
            $result = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
            AdminLog::setTitle(__('Login')); //设置登录日志信息；

            //// 登录操作
            $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
            if ($result === true) {
                Hook::listen("admin_login_after", $this->request);
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);

            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->token()]);
            }
        }

        //// 根据cookie自动登录
        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin()) {
            $this->redirect($url);
        }

        $background = Config::get('fastadmin.login_background');
        $background = stripos($background, 'http') === 0 ? $background : config('site.cdnurl') . $background;
        $this->view->assign('background', $background);
        $this->view->assign('title', __('Login'));
        Hook::listen("admin_login_init", $this->request);
        return $this->view->fetch();
    }

    /**
     * 注销登录
     * @author ^2_3^
     */
    public function logout()
    {
        $this->auth->logout();
        Hook::listen("admin_logout_after", $this->request);
        $this->success(__('Logout successful'), 'index/login');
    }

}
