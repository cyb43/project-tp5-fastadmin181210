<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;

/**
 * 管理员日志表 模型
 * Class AdminLog
 * @package app\admin\model
 * @author ^2_3^
 */
class AdminLog extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    //自定义日志标题
    protected static $title = '';
    //自定义日志内容
    protected static $content = '';

    public static function setTitle($title)
    {
        self::$title = $title;
    }

    public static function setContent($content)
    {
        self::$content = $content;
    }

    /**
     * 日志
     * @param string $title
     * @author ^2_3^
     */
    public static function record($title = '')
    {
        // 权限认证实例
        $auth = Auth::instance();

        $admin_id = $auth->isLogin() ? $auth->id : 0;
        $username = $auth->isLogin() ? $auth->username : __('Unknown');

        $content = self::$content;
        if (!$content)
        {
            // 获取当前请求的所有变量（经过过滤）;
            $content = request()->param();
            foreach ($content as $k => $v)
            {
                // 去除长串值或密码信息
                if (is_string($v) && strlen($v) > 200 || stripos($k, 'password') !== false)
                {
                    unset($content[$k]);
                }
            }
        }

        $title = self::$title;
        if (!$title)
        {
            $title = [];
            $breadcrumb = Auth::instance()->getBreadcrumb();

            foreach ($breadcrumb as $k => $v)
            {
                $title[] = $v['title'];
            }
            $title = implode(' ', $title);
        }

        self::create([
            'title'     => $title,
            'content'   => !is_scalar($content) ? json_encode($content) : $content,
            'url'       => request()->url(),
            'admin_id'  => $admin_id,
            'username'  => $username,
            'useragent' => request()->server('HTTP_USER_AGENT'),
            'ip'        => request()->ip()
        ]);
    }

    /**
     * 模型关联
     * @return $this
     * @author ^2_3^王尔贝
     */
    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id')->setEagerlyType(0);
    }

}
