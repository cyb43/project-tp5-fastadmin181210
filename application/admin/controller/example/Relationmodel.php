<?php

namespace app\admin\controller\example;

use app\common\controller\Backend;

/**
 * 关联模型
 *
 * @icon fa fa-table
 * @remark 当使用到关联模型时需要重载index方法;
 * @author ^2_3^
 */
class Relationmodel extends Backend
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
     * 查看
     * @author ^2_3^
     */
    public function index()
    {
        // 设置模型关联
        $this->relationSearch = true;
        // 快速搜索时执行查找的字段
        $this->searchFields = "admin.username,id";

        if ($this->request->isAjax())
        {
            // 查询条件
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            // 数量
            $total = $this->model
                    ->with("admin")
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            // 列表
            $list = $this->model
                    ->with("admin")
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

}
