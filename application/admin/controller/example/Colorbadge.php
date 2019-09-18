<?php

namespace app\admin\controller\example;

use app\common\controller\Backend;

/**
 * 彩色角标
 *
 * @icon fa fa-table
 * @remark 在JS端控制角标的显示与隐藏,请注意左侧菜单栏角标的数值变化
 * @author ^2_3^
 */
class Colorbadge extends Backend
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

}
