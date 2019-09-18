<?php

namespace app\common\library\token\driver;

use app\common\library\token\Driver;

/**
 * Token操作类
 *
 * @author ^2_3^
 */
class Mysql extends Driver
{

    /**
     * 默认配置
     * @var array
     */
    protected $options = [
        'table'      => 'user_token',
        'expire'     => 2592000, //30天;
        'connection' => [],
    ];


    /**
     * 构造函数
     * @param array $options 参数
     * @access public
     *
     * @author ^2_3^
     */
    public function __construct($options = [])
    {
        // 合并配置选项
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        //// 数据库操作句柄
        if ($this->options['connection']) {
            $this->handler = \think\Db::connect($this->options['connection'])->name($this->options['table']);

        } else {
            $this->handler = \think\Db::name($this->options['table']);
        }
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
    public function set($token, $user_id, $expire = null)
    {
        // 过期时间
        $expiretime = !is_null($expire) && $expire !== 0 ? time() + $expire : 0;

        // 加密token
        $token = $this->getEncryptedToken($token);

        // 插入数据
        $this->handler->insert(['token' => $token, 'user_id' => $user_id, 'createtime' => time(), 'expiretime' => $expiretime]);

        return TRUE;
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
        // 后期令牌信息
        $data = $this->handler->where('token', $this->getEncryptedToken($token))->find();

        if ($data) {
            if (!$data['expiretime'] || $data['expiretime'] > time()) {
                //返回未加密的token给客户端使用
                $data['token'] = $token;

                //返回剩余有效时间
                $data['expires_in'] = $this->getExpiredIn($data['expiretime']);
                return $data;

            } else {
                self::delete($token);
            }
        }
        return [];
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
        $data = $this->get($token);
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
        $this->handler->where('token', $this->getEncryptedToken($token))->delete();
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
        $this->handler->where('user_id', $user_id)->delete();
        return true;
    }

}
