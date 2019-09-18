<?php

namespace app\admin\model;

use fast\Tree;
use think\Model;

/**
 * 会员规则
 * Class UserRule
 * @package app\admin\model
 * @author ^2_3^
 */
class UserRule extends Model
{

    // 表名
    protected $name = 'user_rule';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'status_text'
    ];

    /**
     * 初始化
     * @author ^2_3^
     */
    protected static function init()
    {
        // 插入之后,更新权重
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    /**
     * 获取状态列表
     * @return array
     * @author ^2_3^
     */
    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    /**
     * 获取状态文本
     * @param $value
     * @param $data
     * @return mixed|string
     * @author ^2_3^王尔贝
     */
    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    /**
     * 获取树形列表
     * @param array $selected
     * @return array
     * @author ^2_3^
     */
    public static function getTreeList($selected = [])
    {
        // 正常数据
        $ruleList = collection(
            self::where('status', 'normal')
                ->order('weigh desc,id desc')
                ->select()
            )->toArray();

        $nodeList = [];
        Tree::instance()->init($ruleList);
        $ruleList = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
        $hasChildrens = [];
        foreach ($ruleList as $k => $v)
        {
            if ($v['haschild'])
                $hasChildrens[] = $v['id'];
        }
        foreach ($ruleList as $k => $v) {
            $state = array('selected' => in_array($v['id'], $selected) && !in_array($v['id'], $hasChildrens));
            $nodeList[] = array('id' => $v['id'], 'parent' => $v['pid'] ? $v['pid'] : '#',
                'text' => __($v['title']), 'type' => 'menu', 'state' => $state);
        }
        return $nodeList;
    }

}
