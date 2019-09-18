<?php

namespace app\common\library;

use app\admin\model\AuthRule;
use fast\Tree;
use think\Exception;
use think\exception\PDOException;

/**
 * 菜单
 * Class Menu
 * @package app\common\library
 * @author ^2_3^
 */
class Menu
{

    /**
     * 创建菜单
     * @param array $menu
     * @param mixed $parent 父类的name或pid
     *
     * @author ^2_3^
     */
    public static function create($menu, $parent = 0)
    {
        //// 父节点ID
        if (!is_numeric($parent))
        {
            $parentRule = AuthRule::getByName($parent);
            $pid = $parentRule ? $parentRule['id'] : 0;
        }
        else
        {
            $pid = $parent;
        }

        // array_flip — 交换数组中的键和值;
        // 允许字段
        $allow = array_flip(['file', 'name', 'title', 'icon', 'condition', 'remark', 'ismenu']);
        foreach ($menu as $k => $v)
        {
            // 是否有子菜单
            $hasChild = isset($v['sublist']) && $v['sublist'] ? true : false;

            // 允许字段交集
            $data = array_intersect_key($v, $allow);

            // 是否菜单
            $data['ismenu'] = isset($data['ismenu']) ? $data['ismenu'] : ($hasChild ? 1 : 0);
            // 图标
            $data['icon'] = isset($data['icon']) ? $data['icon'] : ($hasChild ? 'fa fa-list' : 'fa fa-circle-o');
            // 父节点
            $data['pid'] = $pid;
            // 状态
            $data['status'] = 'normal';

            try
            {
                // 创建菜单
                $menu = AuthRule::create($data);

                if ($hasChild)
                {
                    self::create($v['sublist'], $menu->id);
                }
            }
            catch (PDOException $e)
            {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * 删除菜单
     * @param string $name 规则name 
     * @return boolean
     *
     * @author ^2_3^
     */
    public static function delete($name)
    {
        $ids = self::getAuthRuleIdsByName($name);
        if (!$ids)
        {
            return false;
        }

        AuthRule::destroy($ids);
        return true;
    }

    /**
     * 启用菜单
     * @param string $name
     * @return boolean
     *
     * @author ^2_3^
     */
    public static function enable($name)
    {
        $ids = self::getAuthRuleIdsByName($name);
        if (!$ids)
        {
            return false;
        }

        AuthRule::where('id', 'in', $ids)->update(['status' => 'normal']);
        return true;
    }

    /**
     * 禁用菜单
     * @param string $name
     * @return boolean
     * @author ^2_3^
     */
    public static function disable($name)
    {
        $ids = self::getAuthRuleIdsByName($name);
        if (!$ids)
        {
            return false;
        }

        AuthRule::where('id', 'in', $ids)->update(['status' => 'hidden']);
        return true;
    }

    /**
     * 导出指定名称的菜单规则
     * @param string $name
     * @return array
     * @author ^2_3^
     */
    public static function export($name)
    {
        $ids = self::getAuthRuleIdsByName($name);
        if (!$ids)
        {
            return [];
        }

        $menuList = [];
        $menu = AuthRule::getByName($name);
        if ($menu)
        {
            $ruleList = collection(AuthRule::where('id', 'in', $ids)->select())->toArray();
            $menuList = Tree::instance()->init($ruleList)->getTreeArray($menu['id']);
        }
        return $menuList;
    }

    /**
     * 根据名称获取规则IDS
     * @param string $name
     * @return array
     *
     * @author ^2_3^
     */
    public static function getAuthRuleIdsByName($name)
    {
        $ids = [];
        $menu = AuthRule::getByName($name);
        if ($menu)
        {
            // 必须将结果集转换为数组
            $ruleList = collection(AuthRule::order('weigh', 'desc')->field('id,pid,name')->select())->toArray();
            // 构造菜单数据
            $ids = Tree::instance()->init($ruleList)->getChildrenIds($menu['id'], true);
        }
        return $ids;
    }

}
