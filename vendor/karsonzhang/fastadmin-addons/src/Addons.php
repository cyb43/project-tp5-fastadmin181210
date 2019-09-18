<?php

namespace think;

use think\Config;
use think\View;

/**
 * 插件基类
 * Class Addons
 * @author Byron Sampson <xiaobo.sun@qq.com>
 * @package think\addons
 * @author ^2_3^
 */
abstract class Addons
{

    // 视图实例对象
    protected $view = null;
    // 当前错误信息
    protected $error;
    // 插件目录
    public $addons_path = '';
    // 插件配置作用域
    protected $configRange = 'addonconfig';
    // 插件信息作用域
    protected $infoRange = 'addoninfo';

    /**
     * 架构函数
     * @access public
     * @author ^2_3^
     */
    public function __construct()
    {
        // 插件名称
        $name = $this->getName();

        // 获取当前插件目录
        $this->addons_path = ADDON_PATH . $name . DS;

        // +----------------------------------------------------------------------
        // | 模板设置
        // +----------------------------------------------------------------------
//        'template'               => [
//            // 模板引擎类型 支持 php think 支持扩展
//            'type'         => 'Think',
//            // 模板路径
//            'view_path'    => '',
//            // 模板后缀
//            'view_suffix'  => 'html',
//            // 模板文件名分隔符
//            'view_depr'    => DS,
//            // 模板引擎普通标签开始标记
//            'tpl_begin'    => '{',
//            // 模板引擎普通标签结束标记
//            'tpl_end'      => '}',
//            // 标签库标签开始标记
//            'taglib_begin' => '{',
//            // 标签库标签结束标记
//            'taglib_end'   => '}',
//            'tpl_cache'    => true,
//        ],
        //// 视图输出字符串内容替换,留空则会自动进行计算 ^2_3^
//        'view_replace_str'       => [
//            '__PUBLIC__' => '',
//            '__ROOT__'   => '',
//            '__CDN__'    => '',
//        ],
        // 初始化视图模型
        $config = ['view_path' => $this->addons_path];
        $config = array_merge(Config::get('template'), $config);
        $this->view = new View($config, Config::get('view_replace_str'));

        // 控制器初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }

    /**
     * 读取基础配置信息
     * @param string $name
     * @return array
     * @author ^2_3^
     */
    final public function getInfo($name = '')
    {
        // 获取插件名称
        if (empty($name)) {
            $name = $this->getName();
        }

        // 获取插件缓存信息
        $info = Config::get($name, $this->infoRange);
        if ($info) {
            return $info;
        }

        // 读取插件信息文件
        $info_file = $this->addons_path . 'info.ini'; //插件信息文件;
        if (is_file($info_file)) {
            // 解析*.ini文件
            $info = Config::parse($info_file, '', $name, $this->infoRange);
            $info['url'] = addon_url($name);
        }
        Config::set($name, $info, $this->infoRange); //设置缓存;

        return $info ? $info : [];
    }

    /**
     * 获取插件的配置数组
     * @param string $name 可选模块名
     * @return array
     * @author ^2_3^
     */
    final public function getConfig($name = '')
    {
        // 获取插件
        if (empty($name)) {
            $name = $this->getName();
        }

        // 获取插件配置缓存
        $config = Config::get($name, $this->configRange);
        if ($config) {
            return $config;
        }

        // 解析配置文件
        $config_file = $this->addons_path . 'config.php';
        if (is_file($config_file)) {
            $temp_arr = include $config_file; //包含配置数组;
            foreach ($temp_arr as $key => $value) {
                $config[$value['name']] = $value['value'];
            }
            unset($temp_arr);
        }
        // 设置插件配置信息
        Config::set($name, $config, $this->configRange);

        return $config;
    }

    /**
     * 设置配置数据
     * @param $name
     * @param array $value
     * @return array
     * @author ^2_3^
     */
    final public function setConfig($name = '', $value = [])
    {
        // 插件名称
        if (empty($name)) {
            $name = $this->getName();
        }

        $config = $this->getConfig($name);
        $config = array_merge($config, $value); //合并配置;
        Config::set($name, $config, $this->configRange);

        return $config;
    }

    /**
     * 设置插件信息数据
     * @param $name
     * @param array $value
     * @return array
     * @author ^2_3^
     */
    final public function setInfo($name = '', $value = [])
    {
        // 插件名称
        if (empty($name)) {
            $name = $this->getName();
        }

        $info = $this->getInfo($name);
        $info = array_merge($info, $value); //合并信息;
        Config::set($name, $info, $this->infoRange);

        return $info;
    }

    /**
     * 获取完整配置列表
     * @param string $name
     * @return array
     * @author ^2_3^
     */
    final public function getFullConfig($name = '')
    {
        $fullConfigArr = [];

        // 插件名称
        if (empty($name)) {
            $name = $this->getName();
        }

        $config_file = $this->addons_path . 'config.php';
        if (is_file($config_file)) {
            $fullConfigArr = include $config_file;
        }

        return $fullConfigArr;
    }

    /**
     * 获取当前模块名
     * @return string
     * @author ^2_3^
     */
    final public function getName()
    {
        $data = explode('\\', get_class($this));
        return strtolower(array_pop($data));
    }

    /**
     * 检查基础配置信息是否完整
     * @return bool
     * @author ^2_3^
     */
    final public function checkInfo()
    {
        $info = $this->getInfo();

        $info_check_keys = ['name', 'title', 'intro', 'author', 'version', 'state'];
        foreach ($info_check_keys as $value) {
            if (!array_key_exists($value, $info)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 加载模板和页面输出,可以返回输出内容
     * @access public
     * @param string $template 模板文件名或者内容
     * @param array $vars 模板输出变量
     * @param array $replace 替换内容
     * @param array $config 模板参数
     * @return mixed
     * @throws \Exception
     * @author ^2_3^
     */
    public function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        if (!is_file($template)) {
            $template = '/' . $template;
        }
        // 关闭模板布局
        $this->view->engine->layout(false);

        echo $this->view->fetch($template, $vars, $replace, $config);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array $vars 模板输出变量
     * @param array $replace 替换内容
     * @param array $config 模板参数
     * @return mixed
     * @author ^2_3^
     */
    public function display($content, $vars = [], $replace = [], $config = [])
    {
        // 关闭模板布局
        $this->view->engine->layout(false);

        echo $this->view->display($content, $vars, $replace, $config);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array $vars 模板输出变量
     * @return mixed
     * @author ^2_3^
     */
    public function show($content, $vars = [])
    {
        // 关闭模板布局
        $this->view->engine->layout(false);

        echo $this->view->fetch($content, $vars, [], [], true);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     * @author ^2_3^
     */
    public function assign($name, $value = '')
    {
        $this->view->assign($name, $value);
    }

    /**
     * 获取当前错误信息
     * @return mixed
     * @author ^2_3^
     */
    public function getError()
    {
        return $this->error;
    }

    //必须实现安装
    abstract public function install();

    //必须卸载插件方法
    abstract public function uninstall();
}
