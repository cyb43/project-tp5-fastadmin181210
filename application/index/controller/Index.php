<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\Token;

/**
 * 前台模块
 * Class Index
 * @package app\index\controller
 * @author ^2_3^
 */
class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 首页
     * @return string
     * @author ^2_3^
     */
    public function index()
    {
        return $this->view->fetch();
    }

    /**
     * @return \think\response\Jsonp
     * @author ^2_3^
     */
    public function news()
    {
        $newslist = [];
        return jsonp(
            ['newslist' => $newslist, 'new' => count($newslist), 'url' => 'https://www.fastadmin.net?ref=news']
        );
    }

}
