<?php

namespace app\admin\controller\auth;

use app\common\controller\Backend;
use fast\Tree;
use think\Cache;

/**
 * 规则管理
 *
 * @icon fa fa-list
 * @author ^2_3^
 * @remark 规则通常对应一个控制器的方法,同时左侧的菜单栏数据也从规则中体现,通常建议通过控制台进行生成规则节点；
 */
class Rule extends Backend
{

    /**
     * @var \app\admin\model\AuthRule
     */
    protected $model = null;
    protected $rulelist = [];
    protected $multiFields = 'ismenu,status';

    /**
     * 初始化
     * @author ^2_3^王尔贝
     */
    public function _initialize()
    {
        parent::_initialize();

        $this->model = model('AuthRule');

        // 必须将结果集转换为数组
        $ruleList = collection($this->model->order('weigh', 'desc')->select())->toArray();
        foreach ($ruleList as $k => &$v)
        {
            $v['title'] = __($v['title']);
            $v['remark'] = __($v['remark']);
        }
        unset($v);

        //// 树形结构
        Tree::instance()->init($ruleList);
        // 二维数组
        $this->rulelist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'title');
        // 菜单数组
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
     *
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
     * 添加
     * @author ^2_3^
     */
    public function add()
    {
        if ($this->request->isPost())
        {
            // strip_tags — 从字符串中去除 HTML 和 PHP 标记
            $params = $this->request->post("row/a", [], 'strip_tags');

            if ($params)
            {
                // 规则验证
                if (!$params['ismenu'] && !$params['pid'])
                {
                    $this->error(__('The non-menu rule must have parent'));
                }

                $result = $this->model->validate()->save($params);
                if ($result === FALSE)
                {
                    $this->error($this->model->getError());
                }

                // 清除"__menu__"缓存
                Cache::rm('__menu__');
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
        $row = $this->model->get(['id' => $ids]);

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            // 提交数据
            $params = $this->request->post("row/a", [], 'strip_tags');

            if ($params)
            {
                // 规则验证
                if (!$params['ismenu'] && !$params['pid'])
                {
                    $this->error(__('The non-menu rule must have parent'));
                }

                //// 针对name做唯一验证
                $ruleValidate = \think\Loader::validate('AuthRule');
                $ruleValidate->rule([
                    'name' => 'require|format|unique:AuthRule,name,' . $row->id,
                ]);
                $result = $row->validate()->save($params);

                if ($result === FALSE)
                {
                    $this->error($row->getError());
                }

                //// 清除缓存
                Cache::rm('__menu__');
                $this->success();
            }
            $this->error();
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
            //// 获取关联ID
            $delIds = [];
            foreach (explode(',', $ids) as $k => $v)
            {
                $delIds = array_merge($delIds, Tree::instance()->getChildrenIds($v, TRUE));
            }
            $delIds = array_unique($delIds);

            //// 删除数据
            $count = $this->model->where('id', 'in', $delIds)->delete();
            if ($count)
            {
                Cache::rm('__menu__');
                $this->success();
            }
        }
        $this->error();
    }

}
