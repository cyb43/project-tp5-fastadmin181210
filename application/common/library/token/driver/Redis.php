<?php

namespace app\common\library\token\driver;

use app\common\library\token\Driver;

/**
 * Token操作类
 *
 * @author ^2_3^
 */
class Redis extends Driver
{

    // 配置选项
    protected $options = [
        'host'        => '127.0.0.1',
        'port'        => 6379,
        'password'    => '',
        'select'      => 0,
        'timeout'     => 0,
        'expire'      => 0,
        'persistent'  => false,
        'userprefix'  => 'up:',
        'tokenprefix' => 'tp:',
    ];

    /**
     * 构造函数
     * @param array $options 缓存参数
     * @throws \BadFunctionCallException
     * @access public
     *
     * @author ^2_3^
     */
    public function __construct($options = [])
    {
        //// 检测 redis扩展插件
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');
        }

        // 合并选项
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        //// redis实例
        $this->handler = new \Redis;
        if ($this->options['persistent']) {
            $this->handler->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], 'persistent_id_' . $this->options['select']);

        } else {
            $this->handler->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
        }

        //// redis密码验证
        if ('' != $this->options['password']) {
            $this->handler->auth($this->options['password']);
        }

        //// redis数据库选择
        if (0 != $this->options['select']) {
            $this->handler->select($this->options['select']);
        }
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

        return $this->options['tokenprefix'] . hash_hmac($config['hashalgo'], $token, $config['key']);
    }

    /**
     * 获取会员的key
     * @param $user_id
     * @return string
     *
     * @author ^2_3^
     */
    protected function getUserKey($user_id)
    {
        return $this->options['userprefix'] . $user_id;
    }

    /**
     * 存储Token
     * @param   string $token Token
     * @param   int $user_id 会员ID
     * @param   int $expire 过期时长,0表示无限,单位秒
     * @return bool
     *
     * @author ^2_3^
     */
    public function set($token, $user_id, $expire = 0)
    {
        // 过期时间
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }

        // 过期时间 转换格式
        if ($expire instanceof \DateTime) {
            $expire = $expire->getTimestamp() - time();
        }

        // 加密token
        $key = $this->getEncryptedToken($token);

        // 缓存设置
        if ($expire) {
            $result = $this->handler->setex($key, $expire, $user_id);

        } else {
            $result = $this->handler->set($key, $user_id);
        }

        // 写入会员关联的token
        $this->handler->sAdd($this->getUserKey($user_id), $key);

        return $result;
    }

    /**
     * 获取Token内的信息
     * @param   string $token
     * @return  array
     *
     * @author ^2_3^
     */
    public function get($token)
    {
        // 加密token
        $key = $this->getEncryptedToken($token);

        // 用户id
        $value = $this->handler->get($key);
        if (is_null($value) || false === $value) {
            return [];
        }

        //获取有效期
        $expire = $this->handler->ttl($key);
        $expire = $expire < 0 ? 365 * 86400 : $expire;
        $expiretime = time() + $expire;
        $result = ['token' => $token, 'user_id' => $value, 'expiretime' => $expiretime, 'expired_in' => $expire];

        return $result;
    }

    /**
     * 判断Token是否可用
     * @param   string $token Token
     * @param   int $user_id 会员ID
     * @return  boolean
     *
     * @author ^2_3^
     */
    public function check($token, $user_id)
    {
        $data = self::get($token);
        return $data && $data['user_id'] == $user_id ? true : false;
    }

    /**
     * 删除Token
     * @param   string $token
     * @return  boolean
     *
     * @author ^2_3^
     */
    public function delete($token)
    {
        $data = $this->get($token);
        if ($data) {
            $key = $this->getEncryptedToken($token);
            $user_id = $data['user_id'];
            // 删除键
            $this->handler->del($key);
            // 删除集合元素
            $this->handler->sRem($this->getUserKey($user_id), $key);
        }
        return true;

    }

    /**
     * 删除指定用户的所有Token
     * @param   int $user_id
     * @return  boolean
     *
     * @author ^2_3^
     */
    public function clear($user_id)
    {
        $keys = $this->handler->sMembers($this->getUserKey($user_id));
        $this->handler->del($this->getUserKey($user_id));
        $this->handler->del($keys);
        return true;
    }

}
