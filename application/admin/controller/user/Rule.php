<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use fast\Tree;

/**
 * 会员规则(路径)管理
 *
 * @icon fa fa-circle-o
 * @author ^2_3^
 */
class Rule extends Backend
{


    /**
     * @var \app\admin\model\UserRule
     */
    protected $model = null;
    protected $rulelist = [];
    protected $multiFields = 'ismenu,status';

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();

        // 会员规则模型
        $this->model = model('UserRule');

        // 状态列表
        $this->view->assign("statusList", $this->model->getStatusList());

        // 必须将结果集转换为数组
        $ruleList = collection(
            $this->model->order('weigh', 'desc')->select()
        )->toArray();
        foreach ($ruleList as $k => &$v)
        {
            $v['title'] = __($v['title']);
            $v['remark'] = __($v['remark']);
        }
        unset($v);

        //// 规则列表
        Tree::instance()->init($ruleList);
        $this->rulelist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'title');
        $ruledata = [0 => __('None')];
        foreach ($this->rulelist as $k => &$v)
        {
            if (!$v['ismenu'])
                continue;
            $ruledata[$v['id']] = $v['title'];
        }
        $this->view->assign('ruledata', $ruledata);
    }

    /**
     * 查看
     * @author ^2_3^
     */
    public function index()
    {
        if ($this->request->isAjax())
        {
            $list = $this->rulelist;
            $total = count($this->rulelist);

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 删除
     * @author ^2_3^
     */
    public function del($ids = "")
    {
        if ($ids)
        {
            $delIds = [];
            foreach (explode(',', $ids) as $k => $v)
            {
                $delIds = array_merge($delIds, Tree::instance()->getChildrenIds($v, TRUE));
            }
            $delIds = array_unique($delIds);

            $count = $this->model->where('id', 'in', $delIds)->delete();
            if ($count)
            {
                $this->success();
            }
        }
        $this->error();
    }

}
