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

namespace think\cache\driver;

use think\cache\Driver;

/**
 * 文件类型缓存类
 * @author liu21st <liu21st@gmail.com> ^2_3^
 */
class File extends Driver
{
    /**
     * 配置选项
     * @var array
     * @author ^2_3^
     */
    protected $options = [
        'expire'        => 0,
        'cache_subdir'  => true,
        'prefix'        => '',
        'path'          => CACHE_PATH, // /runtime/cache;
        'data_compress' => false,
    ];

    protected $expire;

    /**
     * 构造函数
     * @param array $options
     * @author ^2_3^
     */
    public function __construct($options = [])
    {
        // 合并配置
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        // 处理缓存路径
        if (substr($this->options['path'], -1) != DS) {
            $this->options['path'] .= DS;
        }

        // 初始化
        $this->init();
    }

    /**
     * 初始化检查
     * @access private
     * @return boolean
     * @author ^2_3^
     */
    private function init()
    {
        // 创建项目缓存目录
        if (!is_dir($this->options['path'])) {
            if (mkdir($this->options['path'], 0755, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 取得变量的存储文件名
     * @access protected
     * @param  string $name 缓存变量名
     * @param  bool   $auto 是否自动创建目录
     * @return string
     * @author ^2_3^
     */
    protected function getCacheKey($name, $auto = false)
    {
        $name = md5($name);

        if ($this->options['cache_subdir']) {
            // 使用子目录
            $name = substr($name, 0, 2) . DS . substr($name, 2);
        }
        if ($this->options['prefix']) {
            $name = $this->options['prefix'] . DS . $name;
        }

        $filename = $this->options['path'] . $name . '.php';
        $dir      = dirname($filename);

        if ($auto && !is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $filename;
    }

    /**
     * 判断缓存是否存在
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     * @author ^2_3^
     */
    public function has($name)
    {
        return $this->get($name) ? true : false;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     * @author ^2_3^
     */
    public function get($name, $default = false)
    {
        // 文件名
        $filename = $this->getCacheKey($name);
        if (!is_file($filename)) {
            return $default;
        }

        $content      = file_get_contents($filename);
        $this->expire = null;

        if (false !== $content) {
            $expire = (int) substr($content, 8, 12);

            // 是否过期
            if (0 != $expire && time() > filemtime($filename) + $expire) {
                return $default;
            }

            $this->expire = $expire;
            $content      = substr($content, 32);

            // 解压
            if ($this->options['data_compress'] && function_exists('gzcompress')) {
                //启用数据压缩
                $content = gzuncompress($content);
            }

            // 反序列化
            $content = unserialize($content);
            return $content;

        } else {
            return $default;
        }
    }

    /**
     * 写入缓存
     * @access public
     * @param string            $name 缓存变量名
     * @param mixed             $value 存储数据
     * @param integer|\DateTime $expire 有效时间（秒）
     * @return boolean
     * @author ^2_3^
     */
    public function set($name, $value, $expire = null)
    {
        // 有效时间
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        if ($expire instanceof \DateTime) {
            $expire = $expire->getTimestamp() - time();
        }

        $filename = $this->getCacheKey($name, true);
        if ($this->tag && !is_file($filename)) {
            $first = true;
        }

        // 序列化
        $data = serialize($value);
        // 压缩数据
        if ($this->options['data_compress'] && function_exists('gzcompress')) {
            //数据压缩
            $data = gzcompress($data, 3);
        }

        $data   = "<?php\n//" . sprintf('%012d', $expire) . "\n exit();?>\n" . $data;

        $result = file_put_contents($filename, $data);
        if ($result) {
            isset($first) && $this->setTagItem($filename);
            clearstatcache();

            return true;
        } else {
            return false;
        }
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     * @author ^2_3^
     */
    public function inc($name, $step = 1)
    {
        if ($this->has($name)) {
            $value  = $this->get($name) + $step;
            $expire = $this->expire;

        } else {
            $value  = $step;
            $expire = 0;
        }

        return $this->set($name, $value, $expire) ? $value : false;
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     * @author ^2_3^
     */
    public function dec($name, $step = 1)
    {
        if ($this->has($name)) {
            $value  = $this->get($name) - $step;
            $expire = $this->expire;

        } else {
            $value  = -$step;
            $expire = 0;
        }

        return $this->set($name, $value, $expire) ? $value : false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     * @author ^2_3^
     */
    public function rm($name)
    {
        $filename = $this->getCacheKey($name);
        try {
            return $this->unlink($filename);

        } catch (\Exception $e) {
        }
    }

    /**
     * 清除缓存(清除标签)
     * @access public
     * @param string $tag 标签名
     * @return boolean
     * @author ^2_3^
     */
    public function clear($tag = null)
    {
        if ($tag) {
            // 指定标签清除
            $keys = $this->getTagItem($tag);
            foreach ($keys as $key) {
                $this->unlink($key);
            }
            $this->rm('tag_' . md5($tag));
            return true;
        }

        // glob — 寻找与模式匹配的文件路径;
        $files = (array) glob(
            $this->options['path'] . (
                $this->options['prefix'] ? $this->options['prefix'] . DS : ''
            ) . '*');

        foreach ($files as $path) {
            if (is_dir($path)) {
                $matches = glob($path . '/*.php');

                if (is_array($matches)) {
                    array_map('unlink', $matches);
                }

                rmdir($path);

            } else {
                unlink($path);
            }
        }

        return true;
    }

    /**
     * 判断文件是否存在后，删除
     * @param $path
     * @return bool
     * @author byron sampson <xiaobo.sun@qq.com> ^2_3^
     * @return boolean
     */
    private function unlink($path)
    {
        return is_file($path) && unlink($path);
    }

}
