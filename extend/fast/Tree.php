<?php

namespace fast;

use think\Config;

/**
 * [^2_3^]通用的树型类
 * @author XiaoYao <476552238li@gmail.com>
 */
class Tree
{

    // 实例
    protected static $instance;

    // 默认配置
    protected $config = [];

    // 配置选项
    public $options = [];

    /**
     * 生成树型结构所需要的2维数组
     * @var array
     */
    public $arr = [];

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     * @var array
     */
    public $icon = array('│', '├', '└');
    public $nbsp = "&nbsp;";
    public $pidname = 'pid';

    /**
     * Tree constructor.
     * @param array $options
     * @author ^2_3^
     */
    public function __construct($options = [])
    {
        $config = Config::get('tree');
        if ( $config )
        {
            //// ?怀疑：此处赋值给$this->options为写错，改为$this->config；
            //$this->options = array_merge($this->config, $config);
            $this->config = array_merge($this->config, $config);
        }
        $this->options = array_merge($this->config, $options);
    }

    /**
     * [^2_3^]初始化实例
     * @access public
     * @param array $options 参数
     * @return Tree
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * [^2_3^]初始化方法，设置相关数据；
     * @param array 2维数组；
     * array(
     *      1 => array('id'=>'1','pid'=>0,'name'=>'一级栏目一'),
     *      2 => array('id'=>'2','pid'=>0,'name'=>'一级栏目二'),
     *      3 => array('id'=>'3','pid'=>1,'name'=>'二级栏目一'),
     *      4 => array('id'=>'4','pid'=>1,'name'=>'二级栏目二'),
     *      5 => array('id'=>'5','pid'=>2,'name'=>'二级栏目三'),
     *      6 => array('id'=>'6','pid'=>3,'name'=>'三级栏目一'),
     *      7 => array('id'=>'7','pid'=>3,'name'=>'三级栏目二')
     *      )
     * @author ^2_3^
     */
    public function init($arr = [], $pidname = NULL, $nbsp = NULL)
    {
        $this->arr = $arr;

        if (!is_null($pidname))
        {
            $this->pidname = $pidname;
        }

        if (!is_null($nbsp))
        {
            $this->nbsp = $nbsp;
        }

        return $this;
    }

    /**
     * [^2_3^]获取子级数组
     * @param int $myid 父级ID；
     * @return array
     * @author ^2_3^
     */
    public function getChild($myid)
    {
        $newarr = [];
        foreach ($this->arr as $value)
        {
            if (!isset($value['id']))
            {
                continue;
            }

            // 存在子级
            if ($value[$this->pidname] == $myid)
            {
                $newarr[$value['id']] = $value;
            }
        }
        return $newarr;
    }

    /**

     * [^2_3^]读取指定节点的所有孩子节点(递归调用)
     * @param int $myid 节点ID；
     * @param boolean $withself 是否包含自身；
     * @return array
     * @author ^2_3^
     */
    public function getChildren($myid, $withself = FALSE)
    {
        $newarr = [];

        foreach ($this->arr as $value)
        {
            if (!isset($value['id']))
            {
                continue;
            }

            // 获取下级数据
            if ($value[$this->pidname] == $myid)
            {
                $newarr[] = $value;

                // 递归处理 下下级数据
                $newarr = array_merge($newarr, $this->getChildren($value['id']));
            }
            else if ($withself && $value['id'] == $myid)
            {
                $newarr[] = $value;
            }
        }

        return $newarr;
    }

    /**
     * [^2_3^]读取指定节点的所有孩子节点ID
     * @param int $myid 节点ID；
     * @param boolean $withself 是否包含自身；
     * @return array
     * @author ^2_3^
     */
    public function getChildrenIds($myid, $withself = FALSE)
    {
        $childrenlist = $this->getChildren($myid, $withself);

        $childrenids = [];
        foreach ($childrenlist as $k => $v)
        {
            $childrenids[] = $v['id'];
        }
        return $childrenids;
    }

    /**
     * [^2_3^]得到当前位置父辈数组
     * @param int
     * @return array
     * @author ^2_3^
     */
    public function getParent($myid)
    {
        $pid = 0;
        $newarr = [];

        foreach ($this->arr as $value)
        {
            if (!isset($value['id']))
            {
                continue;
            }

            if ($value['id'] == $myid)
            {
                $pid = $value[$this->pidname];
                break;
            }
        }

        if ($pid)
        {
            foreach ($this->arr as $value)
            {
                if ($value['id'] == $pid)
                {
                    $newarr[] = $value;
                    break;
                }
            }
        }
        return $newarr;
    }

    /**
     * [^2_3^]获取当前位置所有父辈数组(递归向上获取父级)
     * @param int
     * @return array
     * @author ^2_3^
     */
    public function getParents($myid, $withself = FALSE)
    {
        $pid = 0;
        $newarr = [];

        foreach ($this->arr as $value)
        {
            if (!isset($value['id']))
            {
                continue;
            }

            // 匹配当前数
            if ($value['id'] == $myid)
            {
                // 是否包含自身
                if ($withself)
                {
                    $newarr[] = $value;
                }

                // 获取父ID
                $pid = $value[$this->pidname];
                break;
            }
        }

        if ($pid)
        {
            // 递归获取父级数据(包含自身)
            $arr = $this->getParents($pid, TRUE);
            $newarr = array_merge($arr, $newarr);
        }

        return $newarr;
    }

    /**
     * [^2_3^]读取指定节点所有父类节点ID
     * @param int $myid 当前节点ID；
     * @param boolean $withself 是否包含自身；
     * @return array
     * @author ^2_3^
     */
    public function getParentsIds($myid, $withself = FALSE)
    {
        // 父级列表
        $parentlist = $this->getParents($myid, $withself);

        $parentsids = [];
        foreach ($parentlist as $k => $v)
        {
            $parentsids[] = $v['id'];
        }
        return $parentsids;
    }

    /**
     * [^2_3^]树型结构Option
     * @param int $myid 表示获得这个ID下的所有子级
     * @param string $itemtpl 条目模板 如："<option value=@id @selected @disabled>@spacer@name</option>"
     * @param mixed $selectedids 被选中的ID，比如在做树型下拉框的时候需要用到；
     * @param mixed $disabledids 被禁用的ID，比如在做树型下拉框的时候需要用到；
     * @param string $itemprefix 每一项前缀；
     * @param string $toptpl 顶级栏目的模板；
     * @return string
     * @author ^2_3^
     */
    public function getTree($myid,
                            $itemtpl = "<option value=@id @selected @disabled>@spacer@name</option>",
                            $selectedids = '',
                            $disabledids = '',
                            $itemprefix = '',
                            $toptpl = '')
    {
        $ret = '';
        $number = 1;

        // 获取子级
        $childs = $this->getChild($myid);
        if ($childs)
        {
            // 子级数量
            $total = count($childs);

            foreach ($childs as $value)
            {
                $id = $value['id']; //当前项目ID；
                $j = $k = '';

                //// 末尾项目
                if ($number == $total)
                {
                    $j .= $this->icon[2];
                    $k = $itemprefix ? $this->nbsp : '';
                }
                else
                {
                    $j .= $this->icon[1];
                    $k = $itemprefix ? $this->icon[0] : '';
                }

                // 名称前置串
                $spacer = $itemprefix ? $itemprefix . $j : '';

                // 是否选中
                $selected = $selectedids &&
                    in_array($id, (is_array($selectedids) ? $selectedids : explode(',', $selectedids))) ?
                    'selected' : '';

                // 是否禁用
                $disabled = $disabledids &&
                    in_array($id, (is_array($disabledids) ? $disabledids : explode(',', $disabledids))) ?
                    'disabled' : '';

                // 合并子级(包含是否选中、禁用、名称前置串)
                $value = array_merge($value,
                    array('selected' => $selected, 'disabled' => $disabled, 'spacer' => $spacer));

                // 处理数组key字段，合并数组，用来替换模板变量
                $value = array_combine(
                    array_map(function($k) {
                            return '@' . $k;
                        }, array_keys($value)
                    ),
                    $value
                );

                //// 转换指定字符(key值为要转换的字串)
                $nstr = strtr(
                    ( ($value["@{$this->pidname}"] == 0 || $this->getChild($id) ) && $toptpl ? $toptpl : $itemtpl),
                    $value
                );
                $ret .= $nstr;

                //// 递归获取下级结构
                $ret .= $this->getTree($id, $itemtpl, $selectedids, $disabledids,
                    $itemprefix . $k . $this->nbsp, $toptpl);

                $number++;
            }
        }
        return $ret;
    }

    /**
     * [^2_3^]树型结构UL
     * @param int $myid 表示获得这个ID下的所有子级；
     * @param string $itemtpl 条目模板，如："<li value=@id @selected @disabled>@name @childlist</li>"；
     * @param string $selectedids 选中的ID；
     * @param string $disabledids 禁用的ID；
     * @param string $wraptag 子列表包裹标签；
     * @param string $wrapattr 子标签属性;
     * @return string
     * @author ^2_3^
     */
    public function getTreeUl($myid, $itemtpl, $selectedids = '', $disabledids = '', $wraptag = 'ul', $wrapattr = '')
    {
        $str = '';

        // 获取子级
        $childs = $this->getChild($myid);

        if ($childs)
        {
            foreach ($childs as $value)
            {
                $id = $value['id'];

                unset($value['child']);

                $selected = $selectedids &&
                    in_array($id, (is_array($selectedids) ? $selectedids : explode(',', $selectedids))) ?
                    'selected' : '';

                $disabled = $disabledids &&
                    in_array($id, (is_array($disabledids) ? $disabledids : explode(',', $disabledids))) ?
                    'disabled' : '';

                // 合并数组
                $value = array_merge($value, array('selected' => $selected, 'disabled' => $disabled));

                // 修改key值(以便后面数值替换)，重组数组；
                $value = array_combine(
                    array_map(function($k) {
                            return '@' . $k;
                        }, array_keys($value)
                    ),
                    $value
                );

                // 替换数值
                $nstr = strtr($itemtpl, $value);

                // 递归组装子级
                $childdata = $this->getTreeUl($id, $itemtpl, $selectedids, $disabledids, $wraptag, $wrapattr);

                $childlist = $childdata ? "<{$wraptag} {$wrapattr}>" . $childdata . "</{$wraptag}>" : "";

                $str .= strtr($nstr, array('@childlist' => $childlist));
            }
        }
        return $str;
    }

    /**
     * [^2_3^]菜单数据
     * @param int $myid
     * @param string $itemtpl
     * @param mixed $selectedids
     * @param mixed $disabledids
     * @param string $wraptag
     * @param string $wrapattr
     * @param int $deeplevel
     * @return string
     * @author ^2_3^
     */
    public function getTreeMenu($myid, $itemtpl, $selectedids = '', $disabledids = '', $wraptag = 'ul', $wrapattr = '',
                                $deeplevel = 0)
    {
        $str = ''; //返回字串；

        // 获取子级
        $childs = $this->getChild($myid);

        if ($childs)
        {
            foreach ($childs as $value)
            {
                $id = $value['id'];

                unset($value['child']);

                $selected = in_array($id, (is_array($selectedids) ? $selectedids : explode(',', $selectedids)))
                    ? 'selected' : '';

                $disabled = in_array($id, (is_array($disabledids) ? $disabledids : explode(',', $disabledids)))
                    ? 'disabled' : '';

                $value = array_merge($value, array('selected' => $selected, 'disabled' => $disabled));

                // 替换模板变量
                $value = array_combine(
                    array_map(function($k) {
                            return '@' . $k;
                        },
                        array_keys($value)
                    ),
                    $value
                );

                // 数组交集(根据key值)
                $bakvalue = array_intersect_key($value, array_flip(['@url', '@caret', '@class']));

                // 数组差集
                $value = array_diff_key($value, $bakvalue);

                $nstr = strtr($itemtpl, $value);

                $value = array_merge($value, $bakvalue);

                // 子级数据
                $childdata = $this->getTreeMenu($id, $itemtpl, $selectedids, $disabledids, $wraptag, $wrapattr,
                    $deeplevel + 1);

                $childlist = $childdata ? "<{$wraptag} {$wrapattr}>" . $childdata . "</{$wraptag}>" : "";

                $childlist = strtr($childlist, array('@class' => $childdata ? 'last' : ''));

                $value = array(
                    '@childlist' => $childlist,
                    '@url'       => $childdata || !isset($value['@url']) ? "javascript:;" : url($value['@url']),
                    '@addtabs'   => $childdata || !isset($value['@url']) ?
                        "" : (stripos($value['@url'], "?") !== false ? "&" : "?") . "ref=addtabs",
                    '@caret'     => ($childdata && (!isset($value['@badge']) || !$value['@badge']) ?
                        '<i class="fa fa-angle-left"></i>' : ''),
                    '@badge'     => isset($value['@badge']) ? $value['@badge'] : '',
                    '@class'     => ($selected ? ' active' : '') . ($disabled ? ' disabled' : '') .
                        ($childdata ? ' treeview' : ''),
                );

                $str .= strtr($nstr, $value);
            }
        }
        return $str;
    }

    /**
     * [^2_3^]特殊
     * @param integer $myid 要查询的ID；
     * @param string $itemtpl1 第一种HTML代码方式；
     * @param string $itemtpl2 第二种HTML代码方式；
     * @param mixed $selectedids 默认选中；
     * @param mixed $disabledids 禁用；
     * @param string $itemprefix 前缀；
     * @return string
     * @author ^2_3^
     */
    public function getTreeSpecial($myid, $itemtpl1, $itemtpl2, $selectedids = 0, $disabledids = 0, $itemprefix = '')
    {
        $ret = '';

        $number = 1;

        // 获取子级
        $childs = $this->getChild($myid);

        if ($childs)
        {
            $total = count($childs);

            foreach ($childs as $id => $value)
            {
                $j = $k = '';

                if ($number == $total)
                {
                    //// 末项
                    $j .= $this->icon[2];
                    $k = $itemprefix ? $this->nbsp : '';
                }
                else
                {
                    $j .= $this->icon[1];
                    $k = $itemprefix ? $this->icon[0] : '';
                }

                // 前置串
                $spacer = $itemprefix ? $itemprefix . $j : '';

                $selected = $selectedids &&
                    in_array($id, (is_array($selectedids) ? $selectedids : explode(',', $selectedids))) ?
                    'selected' : '';

                $disabled = $disabledids &&
                    in_array($id, (is_array($disabledids) ? $disabledids : explode(',', $disabledids))) ?
                    'disabled' : '';

                $value = array_merge($value,
                    array('selected' => $selected, 'disabled' => $disabled, 'spacer' => $spacer));

                $value = array_combine(
                    array_map(function($k) {
                            return '@' . $k;
                        }, array_keys($value)
                    ),
                    $value
                );

                $nstr = strtr(!isset($value['@disabled']) || !$value['@disabled'] ? $itemtpl1 : $itemtpl2, $value);

                $ret .= $nstr;

                $ret .= $this->getTreeSpecial($id, $itemtpl1, $itemtpl2, $selectedids, $disabledids,
                    $itemprefix . $k . $this->nbsp);

                $number++;
            }
        }
        return $ret;
    }

    /**
     * [^2_3^]获取树状数组
     * @param string $myid 要查询的ID；
     * @param string $itemprefix 前缀；
     * @return array
     * @author ^2_3^
     */
    public function getTreeArray($myid, $itemprefix = '')
    {
        // 子级
        $childs = $this->getChild($myid);

        $n = 0;
        $data = [];
        $number = 1;

        if ($childs)
        {
            $total = count($childs);

            foreach ($childs as $id => $value)
            {
                $j = $k = '';

                if ($number == $total)
                {
                    //// 结尾
                    $j .= $this->icon[2];
                    $k = $itemprefix ? $this->nbsp : '';
                }
                else
                {
                    // 非结尾
                    $j .= $this->icon[1];
                    $k = $itemprefix ? $this->icon[0] : '';
                }

                // 前置串
                $spacer = $itemprefix ? $itemprefix . $j : '';
                $value['spacer'] = $spacer;

                $data[$n] = $value;
                // 获取下级数据
                $data[$n]['childlist'] = $this->getTreeArray($id, $itemprefix . $k . $this->nbsp);

                $n++;
                $number++;
            }
        }
        return $data;
    }

    /**
     * [^2_3^]将getTreeArray的结果返回为二维数组；
     * @param array $data
     * @return array
     * @author ^2_3^
     */
    public function getTreeList($data = [], $field = 'name')
    {
        $arr = [];

        foreach ($data as $k => $v)
        {
            $childlist = isset($v['childlist']) ? $v['childlist'] : [];
            unset($v['childlist']);

            $v[$field] = $v['spacer'] . ' ' . $v[$field];
            $v['haschild'] = $childlist ? 1 : 0;

            if ($v['id'])
            {
                $arr[] = $v;
            }

            if ($childlist)
            {
                $arr = array_merge($arr, $this->getTreeList($childlist, $field));
            }
        }
        return $arr;
    }

}
