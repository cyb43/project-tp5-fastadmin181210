<?php

namespace app\common\library;

use think\Hook;

/**
 * 邮箱验证码类
 *
 * @author ^2_3^
 */
class Ems
{

    /**
     * 验证码有效时长
     * @var int 
     */
    protected static $expire = 120; //秒;

    /**
     * 最大允许检测的次数
     * @var int 
     */
    protected static $maxCheckNums = 10;

    /**
     * 获取最后一次邮箱发送的数据
     *
     * @param   int       $email   邮箱
     * @param   string    $event    事件
     * @return  Ems
     *
     * @author ^2_3^
     */
    public static function get($email, $event = 'default')
    {
        // 最新验证码信息
        $ems = \app\common\model\Ems::
                where(['email' => $email, 'event' => $event])
                ->order('id', 'DESC')
                ->find();

        Hook::listen('ems_get', $ems, null, true);
        return $ems ? $ems : NULL;
    }

    /**
     * 发送验证码
     *
     * @param   int       $email    邮箱
     * @param   int       $code     验证码,为空时将自动生成4位数字
     * @param   string    $event    事件
     * @return  boolean
     *
     * @author ^2_3^
     */
    public static function send($email, $code = NULL, $event = 'default')
    {
        // 验证码
        $code = is_null($code) ? mt_rand(1000, 9999) : $code;

        $time = time();
        $ip = request()->ip();

        // 添加数据
        $ems = \app\common\model\Ems::create(
            ['event' => $event, 'email' => $email, 'code' => $code, 'ip' => $ip, 'createtime' => $time]
        );

        // 验证码生成行为ems_send
        $result = Hook::listen('ems_send', $ems, null, true);
        if (!$result)
        {
            $ems->delete();
            return FALSE;
        }

        return TRUE;
    }

    /**
     * 发送通知
     * 
     * @param   mixed     $email   邮箱,多个以,分隔
     * @param   string    $msg      消息内容
     * @param   string    $template 消息模板
     * @return  boolean
     *
     * @author ^2_3^
     */
    public static function notice($email, $msg = '', $template = NULL)
    {
        $params = [
            'email'    => $email,
            'msg'      => $msg,
            'template' => $template
        ];

        $result = Hook::listen('ems_notice', $params, null, true);
        return $result ? TRUE : FALSE;
    }

    /**
     * 校验验证码
     *
     * @param   int       $email     邮箱
     * @param   int       $code       验证码
     * @param   string    $event      事件
     * @return  boolean
     *
     * @author ^2_3^
     */
    public static function check($email, $code, $event = 'default')
    {
        // 验证吗有效期
        $time = time() - self::$expire;

        // 验证码模型
        $ems = \app\common\model\Ems::where(['email' => $email, 'event' => $event])
                ->order('id', 'DESC')
                ->find();

        if ($ems)
        {
            if ($ems['createtime'] > $time && $ems['times'] <= self::$maxCheckNums)
            {
                $correct = $code == $ems['code'];
                if (!$correct)
                {
                    $ems->times = $ems->times + 1;
                    $ems->save();
                    return FALSE;
                }
                else
                {
                    $result = Hook::listen('ems_check', $ems, null, true);
                    return TRUE;
                }
            }
            else
            {
                // 过期则清空该邮箱验证码
                self::flush($email, $event);
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * 清空指定邮箱验证码
     *
     * @param   int       $email     邮箱
     * @param   string    $event      事件
     * @return  boolean
     *
     * @author ^2_3^
     */
    public static function flush($email, $event = 'default')
    {
        \app\common\model\Ems::
                where(['email' => $email, 'event' => $event])
                ->delete();

        Hook::listen('ems_flush');
        return TRUE;
    }

}
