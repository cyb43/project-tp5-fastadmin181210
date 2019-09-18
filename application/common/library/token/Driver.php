<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace app\common\library\token;

/**
 * Token基础抽象类
 *
 * @author ^2_3^
 */
abstract class Driver
{
    // 数据库(mysql/redis)操作句柄
    protected $handler = null;
    protected $options = [];

    /**
     * 存储Token
     * @param   string $token Token标识(令牌)
     * @param   int $user_id 会员ID
     * @param   int $expire 过期时长,0表示无限,单位秒
     * @return bool
     *
     * @author ^2_3^
     */
    abstract function set($token, $user_id, $expire = 0);

    /**
     * 获取Token内的信息
     * @param   string $token
     * @return  array
     *
     * @author ^2_3^
     */
    abstract function get($token);

    /**
     * 判断Token是否可用
     * @param   string $token Token
     * @param   int $user_id 会员ID
     * @return  boolean
     *
     * @author ^2_3^
     */
    abstract function check($token, $user_id);

    /**
     * 删除Token
     * @param   string $token
     * @return  boolean
     *
     * @author ^2_3^
     */
    abstract function delete($token);

    /**
     * 删除指定用户的所有Token
     * @param   int $user_id
     * @return  boolean
     *
     * @author ^2_3^
     */
    abstract function clear($user_id);

    /**
     * 返回句柄对象，可执行其它高级方法
     *
     * @access public
     * @return object
     *
     * @author ^2_3^
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * 获取加密后的Token
     * @param string $token Token标识
     * @return string
     *
     * @author ^2_3^
     */
    protected function getEncryptedToken($token)
    {
        //// 获取token标识配置
        // +----------------------------------------------------------------------
        // | Token设置
        // +----------------------------------------------------------------------
//        'token' => [
//            // 驱动方式
//            'type'     => 'Mysql',
//            // 缓存前缀
//            'key'      => 'i3d6o32wo8fvs1fvdpwens',
//            // 加密方式
//            'hashalgo' => 'ripemd160',
//            // 缓存有效期 0表示永久缓存
//            'expire'   => 0,
//        ],
        $config = \think\Config::get('token');


        return hash_hmac($config['hashalgo'], $token, $config['key']);
    }

    /**
     * 获取过期剩余时长
     * @param $expiretime
     * @return float|int|mixed
     *
     * @author ^2_3^
     */
    protected function getExpiredIn($expiretime)
    {
        return $expiretime ? max(0, $expiretime - time()) : 365 * 86400;
    }
}
