<?php

namespace app\admin\controller\example;

use app\common\controller\Backend;

/**
 * 多表格示例
 *
 * @icon fa fa-table
 * @remark 当一个页面上存在多个Bootstrap-table时该如何控制按钮和表格;
 * @author ^2_3^
 */
class Multitable extends Backend
{

    protected $model = null;

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 查看
     * @author ^2_3^
     */
    public function index()
    {
        // 加载语言包
        $this->loadlang('general/attachment');
        $this->loadlang('general/crontab');

        return $this->view->fetch();
    }

    /**
     * table1
     * @return string|\think\response\Json
     * @author ^2_3^
     */
    public function table1()
    {
        // 附件模型
        $this->model = model('Attachment');

        // 设置过滤方法
        $this->request->filter(['strip_tags']);

        if ($this->request->isAjax())
        {
            // 如果发送的来源是Selectpage(动态列表)，则转发到Selectpage方法
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }

            // 查询条件
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

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
        return $this->view->fetch('index');
    }

    /**
     * table2
     * @return string|\think\response\Json
     * @author ^2_3^
     */
    public function table2()
    {
        // 管理员日志
        $this->model = model('AdminLog');

        // 设置过滤方法
        $this->request->filter(['strip_tags']);

        if ($this->request->isAjax())
        {
            // 如果发送的来源是Selectpage(动态列表数据请求)，则转发到Selectpage方法
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }

            // 查询条件
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

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
        return $this->view->fetch('index');
    }

}
