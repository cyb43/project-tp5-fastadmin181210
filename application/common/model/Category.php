<?php

namespace app\common\model;

use think\Model;

/**
 * 分类模型
 * @author ^2_3^
 */
class Category Extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'type_text',
        'flag_text',
    ];

    /**
     * 初始化
     * @author ^2_3^
     */
    protected static function init()
    {
        // 添加后更新权重
        self::afterInsert(function ($row) {
            $row->save(['weigh' => $row['id']]);
        });
    }

    /**
     * 标志
     * @param $value
     * @param $data
     * @return string
     * @author ^2_3^
     */
    public function setFlagAttr($value, $data)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * 读取分类类型
     * @return array
     * @author ^2_3^
     */
    public static function getTypeList()
    {
//        'categorytype' =>
//        array (
//            'default' => 'Default',
//            'page' => 'Page',
//            'article' => 'Article',
//            'test' => 'Test',
//        ),
        $typeList = config('site.categorytype');
        foreach ($typeList as $k => &$v)
        {
            $v = __($v);
        }
        return $typeList;
    }

    /**
     * 栏目类型
     * @param $value
     * @param $data
     * @return mixed|string
     * @author ^2_3^
     */
    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['type'];
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    /**
     * 获取标志列表
     * @return array
     * @author ^2_3^
     */
    public function getFlagList()
    {
        return ['hot' => __('Hot'), 'index' => __('Index'), 'recommend' => __('Recommend')];
    }

    /**
     * 标志文本
     * @param $value
     * @param $data
     * @return string
     * @author ^2_3^
     */
    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : $data['flag'];
        $valueArr = explode(',', $value);
        $list = $this->getFlagList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    /**
     * 读取分类列表
     * @param string $type      指定类型
     * @param string $status    指定状态
     * @return array
     *
     * @author ^2_3^
     */
    public static function getCategoryArray($type = NULL, $status = NULL)
    {
        $list = collection(
            self::where(function($query) use($type, $status) {
                    if (!is_null($type))
                    {
                        $query->where('type', '=', $type);
                    }
                    if (!is_null($status))
                    {
                        $query->where('status', '=', $status);
                    }
                })->order('weigh', 'desc')->select()
        )->toArray();
        return $list;
    }

}
