<?php

namespace app\admin\behavior;

/**
 * 记录管理员操作日志
 * Class AdminLog
 * @package app\admin\behavior
 * @author ^2_3^
 */
class AdminLog
{

    public function run(&$params)
    {
        if (request()->isPost())
        {
            \app\admin\model\AdminLog::record();
        }
    }

}
