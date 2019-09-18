<?php

namespace app\common\library;

use app\common\model\User;
use app\common\model\UserRule;
use fast\Random;
use think\Config;
use think\Db;
use think\Hook;
use think\Request;
use think\Validate;

/**
 * 授权类
 * Class Auth
 * @package app\common\library
 * @author ^2_3^
 */
class Auth
{

    // 静态实例
    protected static $instance = null;

    // 错误信息
    protected $_error = '';

    // 是否登录
    protected $_logined = FALSE;

    // 会员模型
    protected $_user = NULL;

    // token令牌
    protected $_token = '';

    //Token默认有效时长
    protected $keeptime = 2592000; //30天;

    // 设置当前请求的URI(控制器/方法)
    protected $requestUri = '';

    // 规则
    protected $rules = [];

    // 默认配置
    protected $config = [];

    // 配置选项
    protected $options = [];

    // 字段允许
    protected $allowFields = ['id', 'username', 'nickname', 'mobile', 'avatar', 'score'];

    /**
     * 构造函数
     * Auth constructor.
     * @param array $options
     *
     * @author ^2_3^
     */
    public function __construct($options = [])
    {
        if ($config = Config::get('user'))
        {
            $this->config = array_merge($this->config, $config);
        }
        $this->options = array_merge($this->config, $options);
    }

    /**
     * 实例
     * @param array $options 参数
     * @return Auth
     *
     * @author ^2_3^
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 获取User模型
     * @return User
     *
     * @author ^2_3^
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * (魔术方法)兼容调用user模型的属性
     * 
     * @param string $name
     * @return mixed
     *
     * @author ^2_3^
     */
    public function __get($name)
    {
        return $this->_user ? $this->_user->$name : NULL;
    }

    /**
     * 根据Token初始化
     * 根据token获取对应userid,判断会员是否存在
     * @param string $token Token令牌
     * @return boolean
     *
     * @author ^2_3^
     */
    public function init($token)
    {

        // 是否登录
        if ($this->_logined)
        {
            return TRUE;
        }

        // 存在错误
        if ($this->_error)
            return FALSE;

        //// 获取token相关信息
        $data = Token::get($token);
        if (!$data)
        {
            return FALSE;
        }

        $user_id = intval($data['user_id']);
        if ($user_id > 0)
        {
            // 会员模型(fa_user)
            $user = User::get($user_id);
            if (!$user)
            {
                $this->setError('Account not exist');
                return FALSE;
            }

            // 状态异常
            if ($user['status'] != 'normal')
            {
                $this->setError('Account is locked');
                return FALSE;
            }

            $this->_user = $user;
            $this->_logined = TRUE;
            $this->_token = $token;

            //初始化成功的事件
            Hook::listen("user_init_successed", $this->_user);

            return TRUE;
        }
        else
        {
            $this->setError('You are not logged in');
            return FALSE;
        }
    }

    /**
     * 注册用户
     *
     * @param string $username  用户名
     * @param string $password  密码
     * @param string $email     邮箱
     * @param string $mobile    手机号
     * @param array $extend    扩展参数
     * @return boolean
     *
     * @author ^2_3^
     */
    public function register($username, $password, $email = '', $mobile = '', $extend = [])
    {
        //// 检测用户名或邮箱、手机号是否存在
        /// 是否存在用户名
        if (User::getByUsername($username))
        {
            $this->setError('Username already exist');
            return FALSE;
        }
        /// 是否存在邮箱
        if ($email && User::getByEmail($email))
        {
            $this->setError('Email already exist');
            return FALSE;
        }
        /// 手机号码是否存在
        if ($mobile && User::getByMobile($mobile))
        {
            $this->setError('Mobile already exist');
            return FALSE;
        }

        $ip = request()->ip();
        $time = time();

        //// 注册数据
        $data = [
            'username' => $username,
            'password' => $password,
            'email'    => $email,
            'mobile'   => $mobile,
            'level'    => 1,
            'score'    => 0,
            'avatar'   => '',
        ];
        $params = array_merge($data, [
            'nickname'  => $username,
            'salt'      => Random::alnum(),
            'jointime'  => $time,
            'joinip'    => $ip,
            'logintime' => $time,
            'loginip'   => $ip,
            'prevtime'  => $time,
            'status'    => 'normal'
        ]);
        $params['password'] = $this->getEncryptPassword($password, $params['salt']);
        $params = array_merge($params, $extend);

        ////////////////同步到Ucenter////////////////
        if (defined('UC_STATUS') && UC_STATUS)
        {
            $uc = new \addons\ucenter\library\client\Client();
            $user_id = $uc->uc_user_register($username, $password, $email);
            // 如果小于0则说明发生错误
            if ($user_id <= 0)
            {
                $this->setError($user_id > -4 ? 'Username is incorrect' : 'Email is incorrect');
                return FALSE;
            }
            else
            {
                $params['id'] = $user_id;
            }
        }

        //// 账号注册时需要开启事务,避免出现垃圾数据
        Db::startTrans();
        try
        {
            $user = User::create($params);
            Db::commit();

            // 此时的Model中只包含部分数据
            $this->_user = User::get($user->id);

            //设置Token
            $this->_token = Random::uuid();
            Token::set($this->_token, $user->id, $this->keeptime);

            //注册成功的事件
            Hook::listen("user_register_successed", $this->_user);

            return TRUE;
        }
        catch (Exception $e)
        {
            $this->setError($e->getMessage());
            Db::rollback();
            return FALSE;
        }
    }

    /**
     * 用户登录
     *
     * @param string    $account    账号,用户名、邮箱、手机号
     * @param string    $password   密码
     * @return boolean
     *
     * @author ^2_3^
     */
    public function login($account, $password)
    {
        // 账号字段(判断是邮箱登录还是手机号码登录或者用户名登录)
        $field = Validate::is($account, 'email') ? 'email' :
            (Validate::regex($account, '/^1\d{10}$/') ? 'mobile' : 'username');

        //// 获取会员模型
        $user = User::get([$field => $account]);
        if (!$user)
        {
            $this->setError('Account is incorrect');
            return FALSE;
        }

        // 账号异常
        if ($user->status != 'normal')
        {
            $this->setError('Account is locked');
            return FALSE;
        }

        // 密码错误
        if ($user->password != $this->getEncryptPassword($password, $user->salt))
        {
            $this->setError('Password is incorrect');
            return FALSE;
        }

        //直接登录会员
        $this->direct($user->id);

        return TRUE;
    }

    /**
     * 注销
     * 
     * @return boolean
     *
     * @author ^2_3^
     */
    public function logout()
    {
        if (!$this->_logined)
        {
            $this->setError('You are not logged in');
            return false;
        }

        //设置登录标识
        $this->_logined = FALSE;
        //删除Token
        Token::delete($this->_token);

        //注销成功的事件
        Hook::listen("user_logout_successed", $this->_user);

        return TRUE;
    }

    /**
     * 修改密码
     * @param string    $newpassword        新密码
     * @param string    $oldpassword        旧密码
     * @param bool      $ignoreoldpassword  忽略旧密码
     * @return boolean
     *
     * @author ^2_3^
     */
    public function changepwd($newpassword, $oldpassword = '', $ignoreoldpassword = false)
    {
        if (!$this->_logined)
        {
            $this->setError('You are not logged in');
            return false;
        }

        //判断旧密码是否正确
        if ($this->_user->password == $this->getEncryptPassword($oldpassword, $this->_user->salt) || $ignoreoldpassword)
        {
            // 密码盐
            $salt = Random::alnum();

            //// 更新密码
            $newpassword = $this->getEncryptPassword($newpassword, $salt);
            $this->_user->save(['password' => $newpassword, 'salt' => $salt]);

            Token::delete($this->_token);
            //修改密码成功的事件
            Hook::listen("user_changepwd_successed", $this->_user);

            return true;
        }
        else
        {
            $this->setError('Password is incorrect');
            return false;
        }
    }

    /**
     * 直接登录账号
     * @param int $user_id
     * @return boolean
     *
     * @author ^2_3^
     */
    public function direct($user_id)
    {
        // 会员模型
        $user = User::get($user_id);
        if ($user)
        {
            ////////////////同步到Ucenter////////////////
            if (defined('UC_STATUS') && UC_STATUS)
            {
                $uc = new \addons\ucenter\library\client\Client();
                $re = $uc->uc_user_login($this->user->id, $this->user->password . '#split#' . $this->user->salt, 3);
                // 如果小于0则说明发生错误
                if ($re <= 0)
                {
                    $this->setError('Username or password is incorrect');
                    return FALSE;
                }
            }

            $ip = request()->ip();
            $time = time();

            //判断连续登录和最大连续登录
            if ($user->logintime < \fast\Date::unixtime('day'))
            {
                // 连续登录天数
                $user->successions = $user->logintime < \fast\Date::unixtime('day', -1) ? 1 : $user->successions + 1;
                // 最大连续登录天数
                $user->maxsuccessions = max($user->successions, $user->maxsuccessions);
            }

            //// 更新登录信息
            $user->prevtime = $user->logintime;
            //记录本次登录的IP和时间
            $user->loginip = $ip;
            $user->logintime = $time;
            $user->save();

            $this->_user = $user;

            // 更新令牌
            $this->_token = Random::uuid();
            Token::set($this->_token, $user->id, $this->keeptime);

            $this->_logined = TRUE;

            //登录成功的事件
            Hook::listen("user_login_successed", $this->_user);
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * 检测是否有对应权限
     * @param string $path      控制器/方法
     * @param string $module    模块,默认为当前模块
     * @return boolean
     *
     * @author ^2_3^
     */
    public function check($path = NULL, $module = NULL)
    {
        if (!$this->_logined)
            return false;

        //// 规则列表
        $ruleList = $this->getRuleList();
        $rules = [];
        foreach ($ruleList as $k => $v)
        {
            $rules[] = $v['name'];
        }

        // 请求路径
        $url = ($module ? $module : request()->module()) . '/' . (is_null($path) ? $this->getRequestUri() : $path);
        $url = strtolower(str_replace('.', '/', $url));

        return in_array($url, $rules) ? TRUE : FALSE;
    }

    /**
     * 判断是否登录
     * @return boolean
     *
     * @author ^2_3^
     */
    public function isLogin()
    {
        if ($this->_logined)
        {
            return true;
        }
        return false;
    }

    /**
     * 获取当前Token
     * @return string
     *
     * @author ^2_3^
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * 获取会员基本信息
     *
     * @author ^2_3^
     */
    public function getUserinfo()
    {
        $data = $this->_user->toArray();
        $allowFields = $this->getAllowFields();

        $userinfo = array_intersect_key($data, array_flip($allowFields));
        $userinfo = array_merge($userinfo, Token::get($this->_token));

        return $userinfo;
    }

    /**
     * 获取会员组别规则列表
     * @return array
     *
     * @author ^2_3^
     */
    public function getRuleList()
    {
        if ($this->rules)
            return $this->rules;

        $group = $this->_user->group;
        if (!$group)
        {
            return [];
        }

        // 会员组权限规则
        $rules = explode(',', $group->rules);
        $this->rules = UserRule::where('status', 'normal')
            ->where('id', 'in', $rules)
            ->field('id,pid,name,title,ismenu')
            ->select();

        return $this->rules;
    }

    /**
     * 获取当前请求的URI
     * @return string
     *
     * @author ^2_3^
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * 设置当前请求的URI
     * @param string $uri 控制器/方法
     *
     * @author ^2_3^
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
    }

    /**
     * 获取允许输出的字段
     * @return array
     * @author ^2_3^
     */
    public function getAllowFields()
    {
        return $this->allowFields;
    }

    /**
     * 设置允许输出的字段
     * @param array $fields
     *
     * @author ^2_3^
     */
    public function setAllowFields($fields)
    {
        $this->allowFields = $fields;
    }

    /**
     * 删除一个指定会员
     * @param int $user_id 会员ID
     * @return boolean
     *
     * @author ^2_3^
     */
    public function delete($user_id)
    {
        $user = User::get($user_id);
        if (!$user)
        {
            return FALSE;
        }

        ////////////////同步到Ucenter////////////////
        if (defined('UC_STATUS') && UC_STATUS)
        {
            $uc = new \addons\ucenter\library\client\Client();
            $re = $uc->uc_user_delete($user['id']);
            // 如果小于0则说明发生错误
            if ($re <= 0)
            {
                $this->setError('Account is locked');
                return FALSE;
            }
        }

        // 调用事务删除账号
        $result = Db::transaction(function($db) use($user_id) {
                    // 删除会员
                    User::destroy($user_id);
                    // 删除会员指定的所有Token
                    Token::clear($user_id);
                    return TRUE;
                });

        if ($result)
        {
            Hook::listen("user_delete_successed", $user);
        }
        return $result ? TRUE : FALSE;
    }

    /**
     * 获取密码加密后的字符串
     * @param string $password  密码
     * @param string $salt      密码盐
     * @return string
     *
     * @author ^2_3^
     */
    public function getEncryptPassword($password, $salt = '')
    {
        return md5(md5($password) . $salt);
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     * @return boolean
     *
     * @author ^2_3^
     */
    public function match($arr = [])
    {
        $request = Request::instance();

        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr)
        {
            return FALSE;
        }

        // 转换小写
        $arr = array_map('strtolower', $arr);

        // 是否存在(匹配)
        if (in_array(strtolower($request->action()), $arr) || in_array('*', $arr))
        {
            return TRUE;
        }

        // 没找到匹配
        return FALSE;
    }

    /**
     * 设置会话有效时间
     * @param int $keeptime 默认为永久
     *
     * @author ^2_3^
     */
    public function keeptime($keeptime = 0)
    {
        $this->keeptime = $keeptime;
    }

    /**
     * 渲染用户数据(加载/扩展用户数据)
     * @param array     $datalist   二维数组
     * @param mixed     $fields     加载的字段列表
     * @param string    $fieldkey   渲染的字段
     * @param string    $renderkey  结果字段
     * @return array
     *
     * author ^2_3^
     */
    public function render(&$datalist, $fields = [], $fieldkey = 'user_id', $renderkey = 'userinfo')
    {
        //// 字段数组
        $fields = !$fields ? ['id', 'nickname', 'level', 'avatar'] :
            (is_array($fields) ? $fields : explode(',', $fields));

        //// 用户ID
        $ids = [];
        foreach ($datalist as $k => $v)
        {
            if (!isset($v[$fieldkey]))
                continue;

            $ids[] = $v[$fieldkey];
        }

        //// 用户列表
        $list = [];
        if ($ids)
        {
            if (!in_array('id', $fields))
            {
                $fields[] = 'id';
            }

            $ids = array_unique($ids);
            $selectlist = User::where('id', 'in', $ids)->column($fields);
            foreach ($selectlist as $k => $v)
            {
                $list[$v['id']] = $v;
            }
        }

        foreach ($datalist as $k => &$v)
        {
            $v[$renderkey] = isset($list[$v[$fieldkey]]) ? $list[$v[$fieldkey]] : NULL;
        }
        unset($v);

        return $datalist;
    }

    /**
     * 设置错误信息
     *
     * @param $error 错误信息
     * @return Auth
     *
     * @author ^2_3^
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     *
     * @author ^2_3^
     */
    public function getError()
    {
        return $this->_error ? __($this->_error) : '';
    }

}
