<?php

namespace app\admin\controller\example;

use app\common\controller\Backend;

/**
 * 表格完整示例
 *
 * @icon fa fa-table
 * @remark 在使用Bootstrap-table中的常用方式,更多使用方式可查看( http://bootstrap-table.wenzhixin.net.cn/zh-cn/ );
 *
 * @author ^2_3^
 */
class Bootstraptable extends Backend
{

    // 模型_管理员日志
    protected $model = null;

    // 无需登录
    protected $noNeedLogin = ['req4jsonp'];

    // 需登录不鉴权
    // start 启动(自定义按钮触发)
    // pause 停止(自定义按钮触发)
    // change 切换处理(自定义切换列触发处理)
    // detail 详情(自定义按钮触发)
    // cxselect 列表联动(分组/管理员)
    // searchlist 搜索下拉列表(普通搜索url列表请求)
    protected $noNeedRight = ['start', 'pause', 'change', 'detail', 'cxselect', 'searchlist'];

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
        if ($this->request->isAjax()) {
            // 构建查询条件
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(NULL);
            // 总量
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

            // json返回
            $result = array(
                "total" => $total, "rows" => $list, "extend" => ['money' => mt_rand(100000,999999), 'price' => 200]
            );

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * jsonp请求
     * @author ^2_3^王尔贝
     */
    public function req4jsonp()
    {
        // 所有参数
        $all = $this->request->request();
//        file_put_contents('^2_3^.txt',
//            '所有参数 '.\GuzzleHttp\json_encode($all)."\r\n", FILE_APPEND);

        // 回调函数名称
        $callback = $this->request->request('callback');
//        file_put_contents('^2_3^.txt',
//            '回调函数 '.\GuzzleHttp\json_encode($callback)."\r\n", FILE_APPEND);

        // json数据
        $data = json_encode( ['params' => $all] );
        echo $callback.'('.$data.')';

    }

    /**
     * 详情(自定义按钮触发查看详情页面)
     * @author ^2_3^
     */
    public function detail($ids)
    {
        // 选中的模型
        $row = $this->model->get(['id' => $ids]);

        if (!$row){
            $this->error(__('No Results were found'));
        }

        // ajax请求
        if ($this->request->isAjax()) {
            $this->success("Ajax请求成功", null, ['id' => $ids]);
        }

        $this->view->assign("row", $row->toArray());
        return $this->view->fetch();
    }

    /**
     * 启用(自定义按钮触发)
     * @author ^2_3^
     */
    public function start($ids = '')
    {
        $this->success("模拟启动成功");
    }

    /**
     * 暂停(自定义按钮触发)
     * @author ^2_3^
     */
    public function pause($ids = '')
    {
        $this->success("模拟暂停成功");
    }

    /**
     * 切换(自定义切换列触发处理)
     * @author ^2_3^
     */
    public function change($ids = '')
    {
        $this->success("模拟切换成功");
    }

    /**
     * 列表联动搜索(分组/管理员)
     * @author ^2_3^
     */
    public function cxselect()
    {
        // 类型(group/admin)
        $type = $this->request->get('type');
        // 分组ID
        $group_id = $this->request->get('group_id');

        // 返回列表
        $list = null;
        if ($group_id !== '') {
            if ($type == 'group') {
                //// 分组信息
                $groupIds = $this->auth->getChildrenGroupIds(true);
                $list = \app\admin\model\AuthGroup::where('id', 'in', $groupIds)
                    ->field('id as value, name')
                    ->select();

            } else {
                //// 管理员
                $adminIds = \app\admin\model\AuthGroupAccess::where('group_id', 'in', $group_id)
                    ->column('uid');
                $list = \app\admin\model\Admin::where('id', 'in', $adminIds)
                    ->field('id as value, username AS name')
                    ->select();
            }
        }
        $this->success('', null, $list);
    }

    /**
     * 搜索下拉列表(普通搜索url列表)
     * @author ^2_3^
     */
    public function searchlist()
    {
        $result = $this->model->limit(10)->select();

        $searchlist = [];
        foreach ($result as $key => $value) {
            $searchlist[] = ['id' => $value['url'], 'name' => $value['url']];
        }

        $data = ['searchlist' => $searchlist];
        $this->success('', null, $data);
    }

}
