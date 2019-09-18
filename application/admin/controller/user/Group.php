<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;

/**
 * 会员组管理
 *
 * @icon fa fa-users
 * @author ^2_3^
 */
class Group extends Backend
{

    /**
     * @var \app\admin\model\UserGroup
     */
    protected $model = null;

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();

        // 会员分组模型
        $this->model = model('UserGroup');

        // 状态列表
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * @return mixed
     * @author ^2_3^
     */
    public function add()
    {
        $nodeList = \app\admin\model\UserRule::getTreeList();
        $this->assign("nodeList", $nodeList);
        return parent::add();
    }

    /**
     * @param null $ids
     * @return mixed
     * @author ^2_3^
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row){
            $this->error(__('No Results were found'));
        }

        $rules = explode(',', $row['rules']);
        $nodeList = \app\admin\model\UserRule::getTreeList($rules);
        $this->assign("nodeList", $nodeList);

        return parent::edit($ids);
    }

}
