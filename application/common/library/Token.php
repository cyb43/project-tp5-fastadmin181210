<?php

namespace app\common\library;

use app\common\library\token\Driver;
use think\App;
use think\Config;
use think\Log;

/**
 * Token操作类
 *
 * @author ^2_3^
 */
class Token
{
    /**
     * @var array Token实例
     *
     * @author ^2_3^
     */
    public static $instance = [];

    /**
     * @var object 操作句柄
     *
     * @author ^2_3^
     */
    public static $handler;

    /**
     * 连接Token驱动
     * @access public
     * @param  array $options 配置数组(token令牌配置信息)
     * @param  bool|string $name Token连接标识,true强制重新连接
     * @return Driver(token令牌驱动)
     *
     * @author ^2_3^
     */
    public static function connect(array $options = [], $name = false)
    {
        // +----------------------------------------------------------------------
        // | Token设置
        // +----------------------------------------------------------------------
//        'token'                  => [
//            // 驱动方式
//            'type'     => 'Mysql',
//            // 缓存前缀
//            'key'      => 'i3d6o32wo8fvs1fvdpwens',
//            // 加密方式
//            'hashalgo' => 'ripemd160',
//            // 缓存有效期 0表示永久缓存
//            'expire'   => 0,
//        ],
        $type = !empty($options['type']) ? $options['type'] : 'File';

        if (false === $name) {
            $name = md5(serialize($options));
        }

        //// true强制重新连接
        if (true === $name || !isset(self::$instance[$name])) {
            $class = false === strpos($type, '\\') ?
                '\\app\\common\\library\\token\\driver\\' . ucwords($type) :
                $type;

            // 记录初始化信息
            App::$debug && Log::record('[ TOKEN^2_3^ ] INIT ' . $type, 'info');

            if (true === $name) {
                return new $class($options);
            }

            self::$instance[$name] = new $class($options);
        }

        return self::$instance[$name];
    }

    /**
     * 自动初始化Token
     * @access public
     * @param  array $options 配置数组
     * @return Driver(返回token驱动)
     *
     * @author ^2_3^
     */
    public static function init(array $options = [])
    {
        if (is_null(self::$handler)) {

            if (empty($options) && 'complex' == Config::get('token.type')) {
                $default = Config::get('token.default');

                // 获取默认Token配置，并连接
                $options = Config::get('token.' . $default['type']) ?: $default;

            } elseif (empty($options)) {
                // +----------------------------------------------------------------------
                // | Token设置
                // +----------------------------------------------------------------------
//                'token'                  => [
//                    // 驱动方式
//                    'type'     => 'Mysql',
//                    // 缓存前缀
//                    'key'      => 'i3d6o32wo8fvs1fvdpwens',
//                    // 加密方式
//                    'hashalgo' => 'ripemd160',
//                    // 缓存有效期 0表示永久缓存
//                    'expire'   => 0,
//                ],
                $options = Config::get('token');
            }

            self::$handler = self::connect($options);
        }

        return self::$handler;
    }

    /**
     * 判断Token是否可用(check别名)
     * @access public
     * @param  string $token Token标识
     * @return bool
     *
     * @author ^2_3^
     */
    public static function has($token, $user_id)
    {
        return self::check($token, $user_id);
    }

    /**
     * 判断Token是否可用
     * @param string $token Token标识
     * @return bool
     *
     * @author ^2_3^
     */
    public static function check($token, $user_id)
    {
        return self::init()->check($token, $user_id);
    }

    /**
     * 读取Token
     * @access public
     * @param  string $token Token标识
     * @param  mixed $default 默认值
     * @return mixed
     *
     * @author ^2_3^
     */
    public static function get($token, $default = false)
    {
        return self::init()->get($token, $default);
    }

    /**
     * 写入Token
     * @access public
     * @param  string $token Token标识
     * @param  mixed $user_id 存储数据
     * @param  int|null $expire 有效时间,0为永久
     * @return boolean
     *
     * @author ^2_3^
     */
    public static function set($token, $user_id, $expire = null)
    {
        return self::init()->set($token, $user_id, $expire);
    }

    /**
     * 删除Token(delete别名)
     * @access public
     * @param  string $token Token标识
     * @return boolean
     *
     * @author ^2_3^
     */
    public static function rm($token)
    {
        return self::delete($token);
    }

    /**
     * 删除Token
     * @param string $token 标签名
     * @return bool
     *
     * @author ^2_3^
     */
    public static function delete($token)
    {
        return self::init()->delete($token);
    }

    /**
     * 清除Token
     * @access public
     * @param  string $token Token标记
     * @return boolean
     *
     * @author ^2_3^
     */
    public static function clear($user_id = null)
    {
        return self::init()->clear($user_id);
    }

}
