<?php

namespace app\admin\validate;

use think\Validate;

/**
 * 管理员验证器
 * Class Admin
 * @package app\admin\validate
 * @author ^2_3^
 */
class Admin extends Validate
{

    /**
     * 验证规则
     */
    protected $rule = [
        'username' => 'require|max:50|unique:admin',
        'nickname' => 'require',
        'password' => 'require',
        'email'    => 'require|email|unique:admin,email',
    ];

    /**
     * 提示消息
     */
    protected $message = [
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['username', 'email', 'nickname', 'password'],
        'edit' => ['username', 'email', 'nickname'],
    ];

    /**
     * Admin constructor.
     * @param array $rules
     * @param array $message
     * @param array $field
     * @author ^2_3^
     */
    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'username' => __('Username'),
            'nickname' => __('Nickname'),
            'password' => __('Password'),
            'email'    => __('Email'),
        ];
        parent::__construct($rules, $message, $field);
    }

}
