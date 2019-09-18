<?php

namespace app\common\model;

use think\Model;

/**
 * 配置模型
 * @author ^2_3^
 */
class Config extends Model
{

    // 表名,不含前缀
    protected $name = 'config';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    // 追加属性
    protected $append = [];

    /**
     * 读取配置类型
     * @return array
     * @author ^2_3^
     */
    public static function getTypeList()
    {
        $typeList = [
            'string'   => __('String'), //字符;
            'text'     => __('Text'), //文本;
            'editor'   => __('Editor'), //编辑器;
            'number'   => __('Number'), //数字;
            'date'     => __('Date'), //日期;
            'time'     => __('Time'), //时间;
            'datetime' => __('Datetime'), //日期时间;
            'select'   => __('Select'), //列表;
            'selects'  => __('Selects'), //列表(多选);
            'image'    => __('Image'), //图片;
            'images'   => __('Images'), //图片(多);
            'file'     => __('File'), //文件;
            'files'    => __('Files'), //文件(多);
            'switch'   => __('Switch'), //开关;
            'checkbox' => __('Checkbox'), //复选;
            'radio'    => __('Radio'), //单选;
            'array'    => __('Array'), //数组;
            'custom'   => __('Custom'), //Custom;
        ];
        return $typeList;
    }

    /**
     * getRegexList
     * @return array
     * @author ^2_3^
     */
    public static function getRegexList()
    {
        $regexList = [
            'required' => '必选',
            'digits'   => '数字',
            'letters'  => '字母',
            'date'     => '日期',
            'time'     => '时间',
            'email'    => '邮箱',
            'url'      => '网址',
            'qq'       => 'QQ号',
            'IDcard'   => '身份证',
            'tel'      => '座机电话',
            'mobile'   => '手机号',
            'zipcode'  => '邮编',
            'chinese'  => '中文',
            'username' => '用户名',
            'password' => '密码'
        ];
        return $regexList;
    }

    /**
     * 读取分类分组列表
     * @return array
     * @author ^2_3^
     */
    public static function getGroupList()
    {
        // 配置分组 application/extra/site.php
        $groupList = config('site.configgroup');
        foreach ($groupList as $k => &$v) {
            $v = __($v);
        }
        return $groupList;
    }

    /**
     * 获取数组
     * @param $data
     * @return array
     * @author ^2_3^
     */
    public static function getArrayData($data)
    {
        $fieldarr = $valuearr = [];
        $field = isset($data['field']) ? $data['field'] : [];
        $value = isset($data['value']) ? $data['value'] : [];
        foreach ($field as $m => $n) {
            if ($n != '') {
                $fieldarr[] = $field[$m];
                $valuearr[] = $value[$m];
            }
        }
        return $fieldarr ? array_combine($fieldarr, $valuearr) : [];
    }

    /**
     * 将字符串解析成键值数组
     * @param string $text
     * @return array
     * @author ^2_3^
     */
    public static function decode($text, $split = "\r\n")
    {
        $content = explode($split, $text);
        $arr = [];
        foreach ($content as $k => $v) {
            if (stripos($v, "|") !== false) {
                $item = explode('|', $v);
                $arr[$item[0]] = $item[1];
            }
        }
        return $arr;
    }

    /**
     * 将键值数组转换为字符串
     * @param array $array
     * @return string
     * @author ^2_3^
     */
    public static function encode($array, $split = "\r\n")
    {
        $content = '';
        if ($array && is_array($array)) {
            $arr = [];
            foreach ($array as $k => $v) {
                $arr[] = "{$k}|{$v}";
            }
            $content = implode($split, $arr);
        }
        return $content;
    }

    /**
     * 本地上传配置信息
     * @return array
     * @author ^2_3^
     */
    public static function upload()
    {
        // application/extra/upload.php
        $uploadcfg = config('upload');

        $upload = [
            'cdnurl'    => $uploadcfg['cdnurl'],
            'uploadurl' => $uploadcfg['uploadurl'],
            'bucket'    => 'local',
            'maxsize'   => $uploadcfg['maxsize'],
            'mimetype'  => $uploadcfg['mimetype'],
            'multipart' => [],
            'multiple'  => $uploadcfg['multiple'],
        ];
        return $upload;
    }

}
