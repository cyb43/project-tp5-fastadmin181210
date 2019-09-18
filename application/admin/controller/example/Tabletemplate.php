<?php

namespace app\admin\controller\example;

use app\common\controller\Backend;

/**
 * 表格模板示例
 *
 * @icon fa fa-table
 * @remark 可以通过使用表格模板将表格中的行渲染成一样的展现方式，基于此功能可以任意定制自己想要的展示列表;
 * @author ^2_3^
 */
class Tabletemplate extends Backend
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
    }

    /**
     * 查看
     * @author ^2_3^
     */
    public function index()
    {
        if ($this->request->isAjax())
        {
            // 查询条件
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(NULL);

            // 数量
            $total = $this->model
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            // 列表
            $list = $this->model
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
    
    /**
     * 详情
     * @author ^2_3^
     */
    public function detail($ids)
    {
        $row = $this->model->get(['id' => $ids]);

        if (!$row)
        {
            $this->error(__('No Results were found'));
        }

        $this->view->assign("row", $row->toArray());

        return $this->view->fetch();
    }

}
