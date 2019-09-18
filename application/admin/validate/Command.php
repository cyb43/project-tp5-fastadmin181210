<?php

namespace app\admin\validate;

use think\Validate;

/**
 * Class Command
 * @package app\admin\validate
 * @author ^2_3^
 */
class Command extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];

}
