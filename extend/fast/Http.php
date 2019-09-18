<?php

namespace fast;

/**
 * Http类
 * @author ^2_3^
 */
class Http
{

    /**
     * 发送一个POST请求
     * @author ^2_3^
     */
    public static function post($url, $params = [], $options = [])
    {
        $req = self::sendRequest($url, $params, 'POST', $options);
        return $req['ret'] ? $req['msg'] : '';
    }

    /**
     * 发送一个GET请求
     * @author ^2_3^
     */
    public static function get($url, $params = [], $options = [])
    {
        $req = self::sendRequest($url, $params, 'GET', $options);
        return $req['ret'] ? $req['msg'] : '';
    }

    /**
     * CURL发送Request请求,含POST和REQUEST
     * @param string $url 请求的链接
     * @param mixed $params 传递的参数
     * @param string $method 请求的方法
     * @param mixed $options CURL的参数(配置选项)
     * @return array
     * @author ^2_3^
     */
    public static function sendRequest($url, $params = [], $method = 'POST', $options = [])
    {
        // 请求方法
        $method = strtoupper($method);

        // 协议(https)
        $protocol = substr($url, 0, 5);
        // 请求参数字串
        $query_string = is_array($params) ? http_build_query($params) : $params;

        ////1/ 初始化curl
        $ch = curl_init();

        //// 默认配置选项
        $defaults = [];
        // GET请求
        if ('GET' == $method)
        {
            $geturl = $query_string ? $url . (stripos($url, "?") !== FALSE ? "&" : "?") . $query_string : $url;
            $defaults[CURLOPT_URL] = $geturl;
        }
        // 其他请求
        else
        {
            $defaults[CURLOPT_URL] = $url;
            // POST请求
            if ($method == 'POST')
            {
                $defaults[CURLOPT_POST] = 1;
            }
            // 其他请求
            else
            {
                $defaults[CURLOPT_CUSTOMREQUEST] = $method;
            }
            // 请求字串
            $defaults[CURLOPT_POSTFIELDS] = $query_string;
        }

        // 启用时会将头文件的信息作为数据流输出
        $defaults[CURLOPT_HEADER] = FALSE;
        // 用户代理
        $defaults[CURLOPT_USERAGENT] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36";
        // 是否允许重定向
        $defaults[CURLOPT_FOLLOWLOCATION] = TRUE;
        // TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        $defaults[CURLOPT_RETURNTRANSFER] = TRUE;
        // 尝试连接时等待的秒数
        $defaults[CURLOPT_CONNECTTIMEOUT] = 3;
        // 允许 cURL 函数执行的最长秒数
        $defaults[CURLOPT_TIMEOUT] = 3;

        // 设置 HTTP 头字段的数组,disable 100-continue
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        if ('https' == $protocol)
        {
            $defaults[CURLOPT_SSL_VERIFYPEER] = FALSE;
            $defaults[CURLOPT_SSL_VERIFYHOST] = FALSE;
        }

        ////2/ 设置选项
        curl_setopt_array($ch, (array) $options + $defaults);

        ////3/ 执行请求(获取返回字串)
        $ret = curl_exec($ch);
        ////4-0/ 错误信息
        $err = curl_error($ch);

        // 请求失败
        if (FALSE === $ret || !empty($err))
        {
            ////4-1/ 错误代码
            $errno = curl_errno($ch);
            ////4-2/ 请求信息
            $info = curl_getinfo($ch);

            ////5／ 关闭请求
            curl_close($ch);

            return [
                'ret'   => FALSE,
                'errno' => $errno,
                'msg'   => $err,
                'info'  => $info,
            ];
        }
        ////5／ 关闭请求
        curl_close($ch);
        return [
            'ret' => TRUE,
            'msg' => $ret,
        ];
    }

    /**
     * 异步发送请求
     * @param string $url 请求的链接;
     * @param mixed $params 请求的参数;
     * @param string $method 请求的方法;
     * @return boolean TRUE;
     * @author ^2_3^
     */
    public static function sendAsyncRequest($url, $params = [], $method = 'POST')
    {
        ////1/ 请求方法
        $method = strtoupper($method);
        $method = $method == 'POST' ? 'POST' : 'GET';

        ////2/ 处理查询字串
        if (is_array($params))
        {
            //// 处理参数数组
            $post_params = [];
            foreach ($params as $k => &$v)
            {
                if (is_array($v)){
                    $v = implode(',', $v);
                }
                $post_params[] = $k . '=' . urlencode($v);
            }
            $post_string = implode('&', $post_params);

        }else
        {
            $post_string = $params;
        }

        ////3/ 解析url，构造查询参数
        $parts = parse_url($url);
        if ($method == 'GET' && $post_string)
        {
            $parts['query'] = isset($parts['query']) ? $parts['query'] . '&' . $post_string : $post_string;
            $post_string = '';
        }
        $parts['query'] = isset($parts['query']) && $parts['query'] ? '?' . $parts['query'] : '';

        ////4/ 创建socket链接，获得连接句柄
        // fsockopen — 打开一个网络连接或者一个Unix套接字连接;
        // fsockopen (
        //  string $hostname
        //  [, int $port = -1
        //  [, int &$errno
        //  [, string &$errstr
        //  [, float $timeout = ini_get("default_socket_timeout") ]]]]
        // )
        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 3);
        if (!$fp){
            return FALSE;
        }

        ////5/ 发送请求
        stream_set_timeout($fp, 3); //设置超时时间;
        $out = "{$method} {$parts['path']}{$parts['query']} HTTP/1.1\r\n";
        $out.= "Host: {$parts['host']}\r\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out.= "Content-Length: " . strlen($post_string) . "\r\n";
        $out.= "Connection: Close\r\n\r\n";
        if ($post_string !== ''){
            $out .= $post_string;
        }
        fwrite($fp, $out);

        //不用关心服务器返回结果
        //echo fread($fp, 1024);
//        $result = fread($fp, 1024);
//        file_put_contents('^2_3^.txt', 'sendAsyncRequest('.time().') '.
//            \GuzzleHttp\json_encode($result)."\r\n",FILE_APPEND);

        ////6/ 关闭链接
        fclose($fp);
        return TRUE;
    }

    /**
     * 发送文件到客户端
     * @param string $file
     * @param bool $delaftersend
     * @param bool $exitaftersend
     * @author ^2_3^
     */
    public static function sendToBrowser($file, $delaftersend = true, $exitaftersend = true)
    {
        if (file_exists($file) && is_readable($file))
        {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            // basename — 返回路径中的文件名部分;
            header('Content-Disposition: attachment;filename = ' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check = 0, pre-check = 0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));

            // ob_clean — 清空（擦掉）输出缓冲区;
            ob_clean();
            // flush — 刷新输出缓冲;
            flush();
            // readfile — 输出文件;
            readfile($file);

            if ($delaftersend)
            {
                // 删除文件
                unlink($file);
            }

            if ($exitaftersend)
            {
                // 退出
                exit;
            }
        }
    }

}
