<?php

namespace app\admin\library;

use app\admin\model\Admin;
use fast\Random;
use fast\Tree;
use think\Config;
use think\Cookie;
use think\Request;
use think\Session;

/**
 * Class Auth
 * @package app\admin\library
 * @author ^2_3^
 */
class Auth extends \fast\Auth
{

    protected $_error = ''; //错误信息；
    protected $requestUri = '';
    protected $breadcrumb = [];
    protected $logined = false; //登录状态；

    /**
     * Auth constructor.
     * @author ^2_3^
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * (魔术方法)获取属性数值
     * @param $name
     * @return mixed
     * @author ^2_3^王尔贝
     */
    public function __get($name)
    {
        return Session::get('admin.' . $name);
    }

    /**
     * 管理员登录
     *
     * @param   string $username 用户名；
     * @param   string $password 密码；
     * @param   int $keeptime 有效时长；
     * @return  boolean
     * @author ^2_3^
     */
    public function login($username, $password, $keeptime = 0)
    {
        $admin = Admin::get(['username' => $username]);
        if (!$admin) {
            $this->setError('Username is incorrect');
            return false;
        }

        if ($admin['status'] == 'hidden') {
            $this->setError('Admin is forbidden');
            return false;
        }

        if (
            Config::get('fastadmin.login_failure_retry')
            && $admin->loginfailure >= 10
            && time() - $admin->updatetime < 86400
        ) {
            $this->setError('Please try again after 1 day');
            return false;
        }

        if ($admin->password != md5(md5($password) . $admin->salt)) {
            //// 记录登录失败次数
            $admin->loginfailure++;
            $admin->save();
            $this->setError('Password is incorrect');
            return false;
        }

        //// 成功登录处理
        $admin->loginfailure = 0;
        $admin->logintime = time();
        $admin->token = Random::uuid();
        $admin->save();
        Session::set("admin", $admin->toArray());
        $this->keeplogin($keeptime);
        return true;
    }

    /**
     * 注销登录
     * @author ^2_3^王尔贝
     */
    public function logout()
    {
        $admin = Admin::get(intval($this->id));
        if (!$admin) {
            return true;
        }
        $admin->token = '';
        $admin->save();
        Session::delete("admin");
        Cookie::delete("keeplogin");
        return true;
    }

    /**
     * 自动登录
     * @return boolean
     * @author ^2_3^王尔贝
     */
    public function autologin()
    {
        $keeplogin = Cookie::get('keeplogin');
        if (!$keeplogin) {
            return false;
        }

        list($id, $keeptime, $expiretime, $key) = explode('|', $keeplogin);
        if ($id && $keeptime && $expiretime && $key && $expiretime > time()) {
            $admin = Admin::get($id);
            if (!$admin || !$admin->token) {
                return false;
            }

            //token有变更
            if ($key != md5(md5($id) . md5($keeptime) . md5($expiretime) . $admin->token)) {
                return false;
            }

            //// 登录状态检查通过，设置后台人员数据；
            Session::set("admin", $admin->toArray());
            //刷新自动登录的时效
            $this->keeplogin($keeptime);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 刷新保持登录的Cookie
     *
     * @param   int $keeptime 保持登录时长；
     * @return  boolean 是否成功；
     * @author ^2_3^
     */
    protected function keeplogin($keeptime = 0)
    {
        if ($keeptime) {
            $expiretime = time() + $keeptime;
            $key = md5(md5($this->id) . md5($keeptime) . md5($expiretime) . $this->token);
            $data = [$this->id, $keeptime, $expiretime, $key];
            Cookie::set('keeplogin', implode('|', $data), 86400 * 30);
            return true;
        }
        return false;
    }

    /**
     * 检查权限
     * @param array|string $name
     * @param string $uid
     * @param string $relation
     * @param string $mode
     * @return bool
     * @author ^2_3^王尔贝
     */
    public function check($name, $uid = '', $relation = 'or', $mode = 'url')
    {
        return parent::check($name, $this->id, $relation, $mode);
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组(如是否不需要登录)
     *
     * @param array $arr 需要验证权限的数组
     * @return bool
     * @author ^2_3^
     */
    public function match($arr = [])
    {
        $request = Request::instance();
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return FALSE;
        }

        // 递归转换元素
        $arr = array_map('strtolower', $arr);

        // 是否存在
        if (in_array(strtolower($request->action()), $arr) || in_array('*', $arr)) {
            return TRUE;
        }

        // 没找到匹配
        return FALSE;
    }

    /**
     * 检测是否登录
     *
     * @return boolean
     * @author ^2_3^
     */
    public function isLogin()
    {
        if ($this->logined) {
            return true;
        }
        $admin = Session::get('admin');
        if (!$admin) {
            return false;
        }
        //判断是否同一时间同一账号只能在一个地方登录
        if (Config::get('fastadmin.login_unique')) {
            $my = Admin::get($admin['id']);
            if (!$my || $my['token'] != $admin['token']) {
                return false;
            }
        }
        $this->logined = true;
        return true;
    }

    /**
     * 获取当前请求的URI
     * @return string
     * @author ^2_3^
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * 设置当前请求的URI
     * @param string $uri
     * @author ^2_3^
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
    }

    /**
     * 根据用户id获取用户组,返回值为数组；
     * @param null $uid
     * @return array
     * @author ^2_3^王尔贝
     */
    public function getGroups($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;
        return parent::getGroups($uid);
    }

    /**
     * @param null $uid
     * @return array
     * @author ^2_3^王尔贝
     */
    public function getRuleList($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;
        return parent::getRuleList($uid);
    }

    /**
     * 获取用户信息
     * @param null $uid
     * @return mixed|null|static
     * @author ^2_3^王尔贝
     */
    public function getUserInfo($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;

        return $uid != $this->id ? Admin::get(intval($uid)) : Session::get('admin');
    }

    /**
     * 用户所属规则ID
     * @param null $uid
     * @return array
     * @author ^2_3^王尔贝
     */
    public function getRuleIds($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;
        return parent::getRuleIds($uid);
    }

    /**
     * 判断是否超级管理员
     * @return bool
     * @author ^2_3^王尔贝
     */
    public function isSuperAdmin()
    {
        return in_array('*', $this->getRuleIds()) ? TRUE : FALSE;
    }

    /**
     * 获取管理员所属于的分组ID
     * @param int $uid
     * @return array
     * @author ^2_3^
     */
    public function getGroupIds($uid = null)
    {
        $groups = $this->getGroups($uid);

        $groupIds = [];
        foreach ($groups as $K => $v) {
            $groupIds[] = (int)$v['group_id'];
        }
        return $groupIds;
    }

    /**
     * 取出当前管理员所拥有权限的分组
     * @param boolean $withself 是否包含当前所在的分组
     * @return array
     * @author ^2_3^
     */
    public function getChildrenGroupIds($withself = false)
    {
        //// 取出当前管理员所有的分组
        $groups = $this->getGroups();
        $groupIds = [];
        foreach ($groups as $k => $v) {
            $groupIds[] = $v['id'];
        }

        // 所有正常分组
        $groupList = \app\admin\model\AuthGroup::where(['status' => 'normal'])->select();
        $objList = [];
        foreach ($groups as $K => $v) {
            // 超级管理员
            if ($v['rules'] === '*') {
                $objList = $groupList;
                break;
            }

            // 取出包含自己的所有子节点
            $childrenList = Tree::instance()->init($groupList)->getChildren($v['id'], true);
            $obj = Tree::instance()->init($childrenList)->getTreeArray($v['pid']);
            $objList = array_merge($objList, Tree::instance()->getTreeList($obj));
        }

        $childrenGroupIds = [];
        foreach ($objList as $k => $v) {
            $childrenGroupIds[] = $v['id'];
        }
        if (!$withself) {
            $childrenGroupIds = array_diff($childrenGroupIds, $groupIds);
        }
        return $childrenGroupIds;
    }

    /**
     * 取出当前管理员所拥有权限的管理员
     * @param boolean $withself 是否包含自身
     * @return array
     * @author ^2_3^
     */
    public function getChildrenAdminIds($withself = false)
    {
        $childrenAdminIds = [];

        if (!$this->isSuperAdmin()) {
            // 名下所有分组
            $groupIds = $this->getChildrenGroupIds(false);

            $authGroupList = \app\admin\model\AuthGroupAccess::
            field('uid,group_id')
                ->where('group_id', 'in', $groupIds)
                ->select();

            foreach ($authGroupList as $k => $v) {
                $childrenAdminIds[] = $v['uid'];
            }

        } else {
            //超级管理员拥有所有人的权限
            $childrenAdminIds = Admin::column('id');
        }

        if ($withself) {
            if (!in_array($this->id, $childrenAdminIds)) {
                $childrenAdminIds[] = $this->id;
            }

        } else {
            $childrenAdminIds = array_diff($childrenAdminIds, [$this->id]);
        }
        return $childrenAdminIds;
    }

    /**
     * 获得面包屑导航
     * @param string $path
     * @return array
     * @author ^2_3^
     */
    public function getBreadCrumb($path = '')
    {
        if ($this->breadcrumb || !$path) {
            return $this->breadcrumb;
        }

        $path_rule_id = 0;
        foreach ($this->rules as $rule) {
            $path_rule_id = $rule['name'] == $path ? $rule['id'] : $path_rule_id;
        }

        if ($path_rule_id) {
            $this->breadcrumb = Tree::instance()->init($this->rules)->getParents($path_rule_id, true);
            foreach ($this->breadcrumb as $k => &$v) {
                $v['url'] = url($v['name']);
                $v['title'] = __($v['title']);
            }
        }
        return $this->breadcrumb;
    }

    /**
     * 获取左侧和顶部菜单栏
     *
     * @param array $params URL对应的badge(徽章)数据；
        array(4) {
            ["dashboard"] => string(3) "hot"
            ["addon"] => array(3) {
                [0] => string(3) "new"
                [1] => string(3) "red"
                [2] => string(5) "badge"
            }
            ["auth/rule"] => string(6) "菜单"
            ["general"] => array(2) {
                [0] => string(3) "new"
                [1] => string(6) "purple"
            }
        }
     *
     * @param string $fixedPage 默认页(值如dashboard)；
     * @return array
     * @author ^2_3^
     */
    public function getSidebar($params = [], $fixedPage = 'dashboard')
    {
        //// 颜色
        $colorArr = ['red', 'green', 'yellow', 'blue', 'teal', 'orange', 'purple']; //purple紫色;
        $colorNums = count($colorArr);

        // 徽章列表
        $badgeList = [];

        // 模块
        $module = request()->module();

        // 生成菜单的badge(数字/文字、颜色、类名)
//        array(4) {
//            ["dashboard"] => string(3) "hot"
//            ["addon"] => array(3) {
//                [0] => string(3) "new"
//                [1] => string(3) "red"
//                [2] => string(5) "badge"
//            }
//            ["auth/rule"] => string(6) "菜单"
//            ["general"] => array(2) {
//                [0] => string(3) "new"
//                [1] => string(6) "purple"
//            }
//        }
        foreach ($params as $k => $v) {
            // 控制器名
            $url = $k;

            if (is_array($v)) {
                // 数字或文本(徽章显示)
                $nums = isset($v[0]) ? $v[0] : 0;
                // 徽章颜色
                $color = isset($v[1]) ? $v[1] : $colorArr[(is_numeric($nums) ? $nums : strlen($nums)) % $colorNums];
                // 徽章类名
                $class = isset($v[2]) ? $v[2] : 'label';

            } else {
                $nums = $v;
                $color = $colorArr[(is_numeric($nums) ? $nums : strlen($nums)) % $colorNums];
                $class = 'label';
            }

            //必须nums大于0才显示
            if ($nums) {
                $badgeList[$url] = '<small class="' . $class . ' pull-right bg-' . $color . '">' . $nums . '</small>';
            }
        }

        // 读取管理员当前拥有的权限节点
        $userRule = $this->getRuleList();

        $selected = $referer = [];

        // 来源
        $refererUrl = Session::get('referer');

        //// 拼音对象
        $pinyin = new \Overtrue\Pinyin\Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
        // 必须将结果集转换为数组
        $ruleList = collection(
            \app\admin\model\AuthRule::where('status', 'normal')
                ->where('ismenu', 1)
                ->order('weigh', 'desc')
                ->cache("__menu__")
                ->select()
        )->toArray();
        foreach ($ruleList as $k => &$v) {
            // 删除没有权限菜单
            if (!in_array($v['name'], $userRule)) {
                unset($ruleList[$k]);
                continue;
            }

            $v['icon'] = $v['icon'] . ' fa-fw';
            $v['url'] = '/' . $module . '/' . $v['name'];
            $v['badge'] = isset($badgeList[$v['name']]) ? $badgeList[$v['name']] : '';
            $v['py'] = $pinyin->abbr($v['title'], '');
            $v['pinyin'] = $pinyin->permalink($v['title'], '');
            $v['title'] = __($v['title']);
            // 默认选择
            $selected = $v['name'] == $fixedPage ? $v : $selected;
            // 来源
            $referer = url($v['url']) == $refererUrl ? $v : $referer;
        }

        if ($selected == $referer) {
            $referer = [];
        }

        $selected && $selected['url'] = url($selected['url']);
        $referer && $referer['url'] = url($referer['url']);

        $select_id = $selected ? $selected['id'] : 0;

        $menu = $nav = '';
        //// 是否启用多级菜单导航
        if (Config::get('fastadmin.multiplenav')) {
            // 顶级菜单
            $topList = [];
            foreach ($ruleList as $index => $item) {
                if (!$item['pid']) {
                    $topList[] = $item;
                }
            }

            // 父级id
            $selectParentIds = [];
            $tree = Tree::instance();
            $tree->init($ruleList);
            if ($select_id) {
                $selectParentIds = $tree->getParentsIds($select_id, true);
            }

            foreach ($topList as $index => $item) {
                // 构建子树结构
                $childList = Tree::instance()->getTreeMenu(
                    $item['id'],
                    '<li class="@class" pid="@pid">
                                <a href="@url@addtabs" addtabs="@id" url="@url" py="@py" pinyin="@pinyin">
                                    <i class="@icon"></i> 
                                    <span>@title</span> 
                                    <span class="pull-right-container">@caret @badge</span>
                                </a> @childlist
                            </li>',
                    $select_id,
                    '',
                    'ul',
                    'class="treeview-menu"'
                );

                $current = in_array($item['id'], $selectParentIds);
                $url = $childList ? 'javascript:;' : url($item['url']);
                $addtabs = $childList || !$url ?
                    "" : (stripos($url, "?") !== false ? "&" : "?") . "ref=addtabs";

                $childList = str_replace(
                    '" pid="' . $item['id'] . '"',
                    ' treeview ' . ($current ? '' : 'hidden') . '" pid="' . $item['id'] . '"',
                    $childList
                );

                $nav .= '<li class="' . ($current ? 'active' : '') . '">
                            <a href="' . $url . $addtabs . '" addtabs="' . $item['id'] . '" url="' . $url . '">
                                <i class="' . $item['icon'] . '"></i> 
                                <span>' . $item['title'] . '</span> 
                                <span class="pull-right-container"> </span>
                            </a> 
                        </li>';

                $menu .= $childList;
            }

        } else {
            // 构造菜单数据
            Tree::instance()->init($ruleList);
            $menu = Tree::instance()->getTreeMenu(
                0,
                '<li class="@class">
                            <a href="@url@addtabs" addtabs="@id" url="@url" py="@py" pinyin="@pinyin">
                                <i class="@icon"></i> 
                                <span>@title</span> 
                                <span class="pull-right-container">@caret @badge</span>
                            </a> @childlist
                       </li>',
                $select_id,
                '',
                'ul',
                'class="treeview-menu"'
            );

            if ($selected) {
                $nav .= '<li role="presentation" id="tab_' . $selected['id'] .
                                '" class="' . ($referer ? '' : 'active') . '">
                            <a href="#con_' . $selected['id'] . '" node-id="' . $selected['id'] .
                                '" aria-controls="' . $selected['id'] . '" role="tab" data-toggle="tab">
                                <i class="' . $selected['icon'] . ' fa-fw"></i> 
                                <span>' . $selected['title'] . '</span> 
                            </a>
                        </li>';
            }

            if ($referer) {
                $nav .= '
                    <li role="presentation" id="tab_' . $referer['id'] . '" class="active">
                        <a href="#con_' . $referer['id'] . '" node-id="' . $referer['id'] .
                            '" aria-controls="' . $referer['id'] . '" role="tab" data-toggle="tab">
                            <i class="' . $referer['icon'] . ' fa-fw"></i> 
                            <span>' . $referer['title'] . '</span> 
                        </a> 
                        <i class="close-tab fa fa-remove"></i>
                    </li>
                ';
            }
        }

        return [$menu, $nav, $selected, $referer];
    }

    /**
     * 设置错误信息
     *
     * @param string $error 错误信息
     * @return Auth
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
     * @author ^2_3^
     */
    public function getError()
    {
        return $this->_error ? __($this->_error) : '';
    }

}
