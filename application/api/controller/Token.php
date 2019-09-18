<?php

namespace app\api\controller;

use app\common\controller\Api;
use fast\Random;

/**
 * Token接口
 *
 * @author ^2_3^
 */
class Token extends Api
{

    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 检测Token是否过期
     * @author ^2_3^
     */
    public function check()
    {
        $token = $this->auth->getToken();

        // 获取token相关信息(token及对应uid)
        $tokenInfo = \app\common\library\Token::get($token);
        $this->success('', ['token' => $tokenInfo['token'], 'expires_in' => $tokenInfo['expires_in']]);
    }

    /**
     * 刷新Token
     * @author ^2_3^
     */
    public function refresh()
    {
        //// 删除源Token
        $token = $this->auth->getToken();
        \app\common\library\Token::delete($token);

        // 创建新Token
        $token = Random::uuid();
        \app\common\library\Token::set($token, $this->auth->id, 2592000); //30天;

        //// token信息
        $tokenInfo = \app\common\library\Token::get($token);
        $this->success('', ['token' => $tokenInfo['token'], 'expires_in' => $tokenInfo['expires_in']]);
    }

}
