<?php

namespace app\api\library;

use Exception;
use think\exception\Handle;

/**
 * 自定义API模块的错误显示
 * @author ^2_3^
 */
class ExceptionHandle extends Handle
{

    /**
     * 渲染
     * @param Exception $e
     * @return \think\Response|\think\response\Json
     * @author ^2_3^
     */
    public function render(Exception $e)
    {
        // 在生产环境下返回code信息
        if (!\think\Config::get('app_debug'))
        {
            $statuscode = $code = 500;
            $msg = 'An error occurred';

            // 验证异常
            if ($e instanceof \think\exception\ValidateException)
            {
                $code = 0;
                $statuscode = 200;
                $msg = $e->getError();
            }

            // Http异常
            if ($e instanceof \think\exception\HttpException)
            {
                $statuscode = $code = $e->getStatusCode();
            }

            return json(['code' => $code, 'msg' => $msg, 'time' => time(), 'data' => null], $statuscode);
        }

        //其它交由系统处理
        return parent::render($e);
    }

}
