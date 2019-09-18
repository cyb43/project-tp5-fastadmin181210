<?php

use app\common\model\Category;
use fast\Form;
use fast\Tree;
use think\Db;

// ^2_3^admin模块公共函数

if (!function_exists('build_select')) {

    /**
     * 生成下拉列表
     * @param string $name
     * @param mixed $options
     * @param mixed $selected
     * @param mixed $attr
     * @return string
     * @author ^2_3^
     */
    function build_select($name, $options, $selected = [], $attr = [])
    {
        $options = is_array($options) ? $options : explode(',', $options);
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        return Form::select($name, $options, $selected, $attr);
    }
}

if (!function_exists('build_radios')) {

    /**
     * 生成单选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     * @author ^2_3^
     */
    function build_radios($name, $list = [], $selected = null)
    {
        $html = [];
        // key() 返回一键；
        $selected = is_null($selected) ? key($list) : $selected;
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        foreach ($list as $k => $v) {
            $html[] = sprintf(Form::label("{$name}-{$k}", "%s {$v}"),
                Form::radio($name, $k, in_array($k, $selected), ['id' => "{$name}-{$k}"]));
        }
        return '<div class="radio">' . implode(' ', $html) . '</div>';
    }
}

if (!function_exists('build_checkboxs')) {

    /**
     * 生成复选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     * @author ^2_3^
     */
    function build_checkboxs($name, $list = [], $selected = null)
    {
        $html = [];

        $selected = is_null($selected) ? [] : $selected;
        $selected = is_array($selected) ? $selected : explode(',', $selected);

        foreach ($list as $k => $v) {
            $html[] = sprintf(
                Form::label("{$name}-{$k}", "%s {$v}"),
                Form::checkbox($name, $k, in_array($k, $selected), ['id' => "{$name}-{$k}"])
            );
        }
        return '<div class="checkbox">' . implode(' ', $html) . '</div>';
    }
}


if (!function_exists('build_category_select')) {

    /**
     * 生成分类下拉列表框
     * @param string $name 控制名称;
     * @param string $type 分类类型(default/page/article/test);
     * @param mixed $selected 选中项;
     * @param array $attr 扩展属性;
     * @param array $header 头部选项(前置列表前边);
     * @return string
     * @author ^2_3^
     */
    function build_category_select($name, $type, $selected = null, $attr = [], $header = [])
    {
        // 树型实例
        $tree = Tree::instance();
        // 初始数据
        $tree->init(
            Category::getCategoryArray($type),
            'pid'
        );

        // 获取分类列表
        $categorylist = $tree->getTreeList(
            $tree->getTreeArray(0),
            'name'
        );

        //// 列表数据
        $categorydata = $header ? $header : [];
        foreach ($categorylist as $k => $v) {
            $categorydata[$v['id']] = $v['name'];
        }

        // 合并属性
        $attr = array_merge(['id' => "c-{$name}", 'class' => 'form-control selectpicker'], $attr);

        return build_select($name, $categorydata, $selected, $attr);
    }
}

if (!function_exists('build_toolbar')) {

    /**
     * 生成表格操作按钮栏
     * @param array $btns 按钮组
     * @param array $attr 按钮属性值
     * @return string
     * @author ^2_3^
     */
    function build_toolbar($btns = NULL, $attr = [])
    {
        // 权限认证对象
        $auth = \app\admin\library\Auth::instance();

        //// 控制器
        $controller = str_replace('.', '/', strtolower(think\Request::instance()->controller()));

        $btns = $btns ? $btns : ['refresh', 'add', 'edit', 'del', 'import'];
        $btns = is_array($btns) ? $btns : explode(',', $btns);

        $index = array_search('delete', $btns);
        if ($index !== FALSE) {
            $btns[$index] = 'del';
        }

        $btnAttr = [//$href, $class, $icon, $text, $title
            'refresh' => ['javascript:;', 'btn btn-primary btn-refresh', 'fa fa-refresh', '', __('Refresh')],
            'add'     => ['javascript:;', 'btn btn-success btn-add', 'fa fa-plus', __('Add'), __('Add')],
            'edit'    => ['javascript:;', 'btn btn-success btn-edit btn-disabled disabled', 'fa fa-pencil', __('Edit'), __('Edit')],
            'del'     => ['javascript:;', 'btn btn-danger btn-del btn-disabled disabled', 'fa fa-trash', __('Delete'), __('Delete')],
            'import'  => ['javascript:;', 'btn btn-danger btn-import', 'fa fa-upload', __('Import'), __('Import')],
        ];
        $btnAttr = array_merge($btnAttr, $attr);

        $html = [];
        foreach ($btns as $k => $v) {
            //如果未定义或没有权限
            if (!isset($btnAttr[$v]) || ($v !== 'refresh' && !$auth->check("{$controller}/{$v}"))) {
                continue;
            }

            list($href, $class, $icon, $text, $title) = $btnAttr[$v];
            $extend = $v == 'import' ?
                'id="btn-import-file" data-url="ajax/upload" data-mimetype="csv,xls,xlsx" data-multiple="false"' : '';
            $html[] = '<a href="' . $href . '" class="' . $class . '" title="' . $title . '" ' . $extend .
                '><i class="' . $icon . '"></i> ' . $text . '</a>';
        }
        return implode(' ', $html);
    }
}

if (!function_exists('build_heading')) {

    /**
     * 生成页面Heading提示
     *
     * @param string $path 指定的path
     * @return string
     * @author ^2_3^
     */
    function build_heading($path = NULL, $container = TRUE)
    {
        $title = $content = '';

        if (is_null($path)) {
            $action = request()->action();
            $controller = str_replace('.', '/', request()->controller());
            $path = strtolower($controller . ($action && $action != 'index' ? '/' . $action : ''));
        }

        // 根据当前的URI自动匹配父节点的标题和备注
        // (权限)节点标题和备注
        $data = Db::name('auth_rule')->where('name', $path)->field('title,remark')->find();
        if ($data) {
            $title = __($data['title']);
            $content = __($data['remark']);
        }

        if (!$content)
            return '';

        $result = '<div class="panel-lead"><em>' . $title . '</em>' . $content . '</div>';
        if ($container) {
            $result = '<div class="panel-heading">' . $result . '</div>';
        }

        return $result;
    }

}
