<?php

namespace app\admin\controller\auth;

use app\admin\model\AuthGroup;
use app\common\controller\Backend;
use fast\Tree;

/**
 * 管理员角色分组
 *
 * @icon fa fa-group
 * @remark 角色组可以有多个,角色有上下级层级关系,如果子角色有角色组和管理员的权限则可以派生属于自己组别下级的角色组或管理员。
 * @author ^2_3^
 */
class Group extends Backend
{

    /**
     * @var \app\admin\model\AuthGroup
     */
    protected $model = null;

    //当前登录管理员所有子组别
    protected $childrenGroupIds = [];
    //当前组别列表数据
    protected $groupdata = [];

    //无需要权限判断的方法
    protected $noNeedRight = ['roletree'];

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();

        // 管理员角色组模型
        $this->model = model('AuthGroup');

        // 当前管理员所有子组别
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds(true);

        // 分组信息
        $groupList = collection(
            AuthGroup::where('id', 'in', $this->childrenGroupIds)->select()
        )->toArray();

        Tree::instance()->init($groupList);
        $result = [];
        if ($this->auth->isSuperAdmin())
        {
            $result = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0));
        }
        else
        {
            $groups = $this->auth->getGroups();
            foreach ($groups as $m => $n)
            {
                $result = array_merge($result,
                    Tree::instance()->getTreeList(Tree::instance()->getTreeArray($n['pid'])));
            }
        }

        $groupName = [];
        foreach ($result as $k => $v)
        {
            $groupName[$v['id']] = $v['name'];
        }

        $this->groupdata = $groupName;
        $this->assignconfig("admin", ['id' => $this->auth->id, 'group_ids' => $this->auth->getGroupIds()]);

        $this->view->assign('groupdata', $this->groupdata);
    }

    /**
     * 查看(只显示名下角色组)
     * @author ^2_3^
     */
    public function index()
    {
        if ($this->request->isAjax())
        {
            // 根据主键获取多个数据
            $list = AuthGroup::all(array_keys($this->groupdata));
            $list = collection($list)->toArray();

            $groupList = [];
            foreach ($list as $k => $v)
            {
                $groupList[$v['id']] = $v;
            }

            $list = [];
            foreach ($this->groupdata as $k => $v)
            {
                if (isset($groupList[$k]))
                {
                    $groupList[$k]['name'] = $v;
                    $list[] = $groupList[$k];
                }
            }

            $total = count($list);
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     * @author ^2_3^
     */
    public function add()
    {
        if ($this->request->isPost())
        {
            // 添加数据
            $params = $this->request->post("row/a", [], 'strip_tags');
            $params['rules'] = explode(',', $params['rules']); //规则;

            // 父组检测
            if (!in_array($params['pid'], $this->childrenGroupIds))
            {
                $this->error(__('The parent group can not be its own child'));
            }

            // 父组模型
            $parentmodel = model("AuthGroup")->get($params['pid']);
            if (!$parentmodel)
            {
                $this->error(__('The parent group can not found'));
            }

            // 父级别的规则节点
            $parentrules = explode(',', $parentmodel->rules);

            // 当前组别的规则节点
            $currentrules = $this->auth->getRuleIds();

            $rules = $params['rules'];

            //// 规则节点范围
            // 如果父组不是超级管理员则需要过滤规则节点,不能超过父组别的权限
            $rules = in_array('*', $parentrules) ? $rules : array_intersect($parentrules, $rules);
            // 如果当前组别不是超级管理员则需要过滤规则节点,不能超当前组别的权限
            $rules = in_array('*', $currentrules) ? $rules : array_intersect($currentrules, $rules);

            $params['rules'] = implode(',', $rules);
            if ($params)
            {
                // 添加数据
                $this->model->create($params);
                $this->success();
            }
            $this->error();
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     * @author ^2_3^
     */
    public function edit($ids = NULL)
    {
        // 编辑模型
        $row = $this->model->get(['id' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            // 编辑参数
            $params = $this->request->post("row/a", [], 'strip_tags');

            if (!in_array($params['pid'], $this->childrenGroupIds))
            {
                $this->error(__('The parent group can not be its own child'));
            }

            $params['rules'] = explode(',', $params['rules']);

            $parentmodel = model("AuthGroup")->get($params['pid']);
            if (!$parentmodel)
            {
                $this->error(__('The parent group can not found'));
            }

            // 父级别的规则节点
            $parentrules = explode(',', $parentmodel->rules);

            // 当前组别的规则节点
            $currentrules = $this->auth->getRuleIds();

            $rules = $params['rules'];

            // 如果父组不是超级管理员则需要过滤规则节点,不能超过父组别的权限
            $rules = in_array('*', $parentrules) ? $rules : array_intersect($parentrules, $rules);
            // 如果当前组别不是超级管理员则需要过滤规则节点,不能超当前组别的权限
            $rules = in_array('*', $currentrules) ? $rules : array_intersect($currentrules, $rules);

            $params['rules'] = implode(',', $rules);
            if ($params)
            {
                // 保存数据
                $row->save($params);
                $this->success();
            }
            $this->error();
            return;
        }

        $this->view->assign("row", $row);
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
            // 删除参数
            $ids = explode(',', $ids);

            // 名下所有分组
            $grouplist = $this->auth->getGroups();
            $group_ids = array_map(function($group) {
                return $group['id'];
            }, $grouplist);

            // 移除掉当前管理员所在组别
            $ids = array_diff($ids, $group_ids);

            // 循环判断每一个组别是否可删除
            $grouplist = $this->model->where('id', 'in', $ids)->select();
            $groupaccessmodel = model('AuthGroupAccess');
            foreach ($grouplist as $k => $v)
            {
                // 当前组别下有管理员
                $groupone = $groupaccessmodel->get(['group_id' => $v['id']]);
                if ($groupone)
                {
                    $ids = array_diff($ids, [$v['id']]);
                    continue;
                }
                // 当前组别下有子组别
                $groupone = $this->model->get(['pid' => $v['id']]);
                if ($groupone)
                {
                    $ids = array_diff($ids, [$v['id']]);
                    continue;
                }
            }

            if (!$ids)
            {
                $this->error(__('You can not delete group that contain child group and administrators'));
            }

            $count = $this->model->where('id', 'in', $ids)->delete();
            if ($count)
            {
                $this->success();
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
        // 组别禁止批量操作
        $this->error();
    }

    /**
     * 读取角色权限树
     * 
     * @internal
     * @author ^2_3^
     */
    public function roletree()
    {
        // 加载语言包
        $this->loadlang('auth/group');

        // 分组模型
        $model = model('AuthGroup');

        // 当前分组ID
        $id = $this->request->post("id");
        // 父分组ID
        $pid = $this->request->post("pid");

        // 父组模型
        $parentGroupModel = $model->get($pid);

        // 当前模型
        $currentGroupModel = NULL;
        if ($id)
        {
            $currentGroupModel = $model->get($id);
        }

        if (($pid || $parentGroupModel) && (!$id || $currentGroupModel))
        {
            $id = $id ? $id : NULL;

            // 所有权限节点
            $ruleList = collection(
                model('AuthRule')->order('weigh', 'desc')->select()
            )->toArray();

            //读取父类角色所有节点列表
            $parentRuleList = [];
            if (in_array('*', explode(',', $parentGroupModel->rules)))
            {
                // 超级管理员
                $parentRuleList = $ruleList;
            }
            else
            {
                $parentRuleIds = explode(',', $parentGroupModel->rules);
                foreach ($ruleList as $k => $v)
                {
                    if (in_array($v['id'], $parentRuleIds))
                    {
                        $parentRuleList[] = $v;
                    }
                }
            }

            //当前所有正常规则列表
            Tree::instance()->init($parentRuleList);

            //读取当前角色下规则ID集合
            $adminRuleIds = $this->auth->getRuleIds();
            //是否是超级管理员
            $superadmin = $this->auth->isSuperAdmin();
            //当前拥有的规则ID集合
            $currentRuleIds = $id ? explode(',', $currentGroupModel->rules) : [];

            if (!$id || !in_array($pid, Tree::instance()->getChildrenIds($id, TRUE)))
            {
                $parentRuleList = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');

                $hasChildrens = [];
                foreach ($parentRuleList as $k => $v)
                {
                    if ($v['haschild'])
                        $hasChildrens[] = $v['id'];
                }

                $parentRuleIds = array_map(function($item) {
                    return $item['id'];
                }, $parentRuleList);

                $nodeList = [];
                foreach ($parentRuleList as $k => $v)
                {
                    if (!$superadmin && !in_array($v['id'], $adminRuleIds))
                        continue;

                    if ($v['pid'] && !in_array($v['pid'], $parentRuleIds))
                        continue;

                    // 是否选中
                    $state = array('selected' => in_array($v['id'], $currentRuleIds) &&
                        !in_array($v['id'], $hasChildrens));

                    // 节点树节点
                    $nodeList[] = array(
                        'id' => $v['id'],
                        'parent' => $v['pid'] ? $v['pid'] : '#',
                        'text' => __($v['title']),
                        'type' => 'menu',
                        'state' => $state
                    );
                }

                $this->success('', null, $nodeList);
            }
            else
            {
                $this->error(__('Can not change the parent to child'));
            }
        }
        else
        {
            $this->error(__('Group not found'));
        }
    }

}
