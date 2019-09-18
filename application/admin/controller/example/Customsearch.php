<?php

namespace app\admin\controller\example;

use app\common\controller\Backend;

/**
 * 自定义搜索
 *
 * @icon fa fa-search
 * @remark 自定义列表的搜索
 * @author ^2_3^
 */
class Customsearch extends Backend
{

    protected $model = null;

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();

        $this->model = model('AdminLog');
        $ipList = $this->model->whereTime('createtime', '-37 days')
            ->group("ip")
            ->column("ip,ip as name");

        //file_put_contents('^2_3^.txt', $this->model->getLastSql()."\r\n", FILE_APPEND);
        $this->view->assign("ipList", $ipList);
    }

}
