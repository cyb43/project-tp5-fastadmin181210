<?php

namespace app\admin\controller\example;

use app\common\controller\Backend;

/**
 * 百度地图
 *
 * @icon fa fa-map
 * @remark 可以搜索百度位置，调用百度地图的相关API;
 * @author ^2_3^
 */
class Baidumap extends Backend
{

    protected $model = null;

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();

        // 管理员日志
        $this->model = model('AdminLog');
    }

    /**
     * 查找地图
     * @author ^2_3^
     */
    public function map()
    {
        return $this->view->fetch();
    }

    /**
     * 搜索列表
     * @author ^2_3^
     */
    public function selectpage()
    {
        // 区域列表
        $this->model = model('Area');
        return parent::selectpage();
    }

}
