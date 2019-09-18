<?php

namespace app\admin\controller\auth;

use app\admin\model\AuthGroup;
use app\common\controller\Backend;

/**
 * 管理员日志
 *
 * @icon fa fa-users
 * @remark 管理员可以查看自己所拥有的权限的管理员日志
 * @author ^2_3^
 */
class Adminlog extends Backend
{

    /**
     * @var \app\admin\model\AdminLog
     */
    protected $model = null;

    protected $childrenGroupIds = [];
    protected $childrenAdminIds = [];

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();

        // 管理员日志模型
        $this->model = model('AdminLog');

        // 子管理员ID
        $this->childrenAdminIds = $this->auth->getChildrenAdminIds(true);
        // 子分组ID
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds(
            $this->auth->isSuperAdmin() ? true : false
        );

        // 分组信息
        $groupName = AuthGroup::where('id', 'in', $this->childrenGroupIds)
                ->column('id,name');
        $this->view->assign('groupdata', $groupName);
    }

    /**
     * 查看
     * @author ^2_3^
     */
    public function index()
    {
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $this->model
                    ->where($where)
                    ->where('admin_id', 'in', $this->childrenAdminIds)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->where($where)
                    ->where('admin_id', 'in', $this->childrenAdminIds)
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
        if (!$row){
            $this->error(__('No Results were found'));
        }

        $this->view->assign("row", $row->toArray());
        return $this->view->fetch();
    }

    /**
     * 添加
     * @author ^2_3^
     * @internal
     */
    public function add()
    {
        $this->error();
    }

    /**
     * 编辑
     * @internal
     * @author ^2_3^
     */
    public function edit($ids = NULL)
    {
        $this->error();
    }

    /**
     * 删除
     * @author ^2_3^
     */
    public function del($ids = "")
    {
        if ($ids)
        {
            $childrenGroupIds = $this->childrenGroupIds;

            $adminList = $this->model
                ->where('id', 'in', $ids)
                ->where('admin_id', 'in', function($query) use($childrenGroupIds) {
                        $query->name('auth_group_access')->field('uid');
                    })->select();

            if ($adminList)
            {
                $deleteIds = [];

                foreach ($adminList as $k => $v)
                {
                    $deleteIds[] = $v->id;
                }

                if ($deleteIds)
                {
                    $this->model->destroy($deleteIds);
                    $this->success();
                }
            }
        }
        $this->error();
    }

    /**
     * 批量更新
     * @internal
     * @author ^2_3^
     */
    public function multi($ids = "")
    {
        // 管理员禁止批量操作
        $this->error();
    }

    /**
     * 动态列表
     * @return \think\response\Json
     * @author ^2_3^
     */
    public function selectpage()
    {
        return parent::selectpage();
    }

}
