<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 首页接口
 * @author ^2_3^
 */
class Index extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     * @author ^2_3^
     */
    public function index()
    {
        $this->success('请求成功');
    }

}
