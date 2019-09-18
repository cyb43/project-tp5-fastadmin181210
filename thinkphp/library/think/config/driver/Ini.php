<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\config\driver;

/**
 * 解析ini配置文件
 * Class Ini
 * @package think\config\driver
 * @author ^2_3^
 */
class Ini
{
    public function parse($config)
    {
        if (is_file($config)) {
            return parse_ini_file($config, true);

        } else {
            return parse_ini_string($config, true);
        }
    }
}
