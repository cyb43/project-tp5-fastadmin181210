<?php

namespace think\addons;

use fast\Http;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use think\Db;
use think\Exception;
use ZipArchive;

/**
 * 插件服务
 * @package think\addons
 *
 * @author ^2_3^
 */
class Service
{

    /**
     * 远程下载插件
     *
     * @param   string $name 插件名称
     * @param   array $extend 扩展参数
     * @return  string 临时文件路径名称
     * @throws  AddonException
     * @throws  Exception
     * @author ^2_3^
     */
    public static function download($name, $extend = [])
    {
        //// 临时目录
        $addonTmpDir = RUNTIME_PATH . 'addons' . DS;
        if (!is_dir($addonTmpDir)) {
            @mkdir($addonTmpDir, 0755, true);
        }

        // 临时文件
        $tmpFile = $addonTmpDir . $name . ".zip";
        // 请求配置
        $options = [
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'X-REQUESTED-WITH: XMLHttpRequest'
            ]
        ];

        //// 请求文件 https://api.fastadmin.net
        $ret = Http::sendRequest(
            self::getServerUrl() . '/addon/download',
            array_merge(['name' => $name], $extend),
            'GET',
            $options
        );

        if ($ret['ret']) {

            if (substr($ret['msg'], 0, 1) == '{') {
                $json = (array)json_decode($ret['msg'], true);

                //// 如果传回的是一个下载链接,则再次下载
                if ($json['data'] && isset($json['data']['url'])) {
                    array_pop($options);

                    $ret = Http::sendRequest($json['data']['url'], [], 'GET', $options);
                    if (!$ret['ret']) {
                        //下载返回错误，抛出异常
                        throw new AddonException($json['msg'], $json['code'], $json['data']);
                    }

                } else {
                    // 下载返回错误，抛出异常
                    throw new AddonException($json['msg'], $json['code'], $json['data']);
                }
            }

            if ($write = fopen($tmpFile, 'w')) {
                fwrite($write, $ret['msg']);
                fclose($write);
                return $tmpFile;
            }
            throw new Exception("没有权限写入临时文件");
        }
        throw new Exception("无法下载远程文件");
    }

    /**
     * 解压插件
     *
     * @param   string $name 插件名称
     * @return  string 插件目录
     * @throws  Exception
     * @author ^2_3^
     */
    public static function unzip($name)
    {
        // 临时文件路径名称
        $file = RUNTIME_PATH . 'addons' . DS . $name . '.zip';

        // 插件目录
        $dir = ADDON_PATH . $name . DS;


        //// ZipArchive,一个用 Zip 压缩的文件存档;
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            if ($zip->open($file) !== TRUE) {
                throw new Exception('Unable to open the zip file');
            }

            // 解压(提取)到指定目录
            if (!$zip->extractTo($dir)) {
                $zip->close();
                throw new Exception('Unable to extract the file');
            }
            $zip->close();
            return $dir;
        }
        throw new Exception("无法执行解压操作，请确保ZipArchive安装正确");
    }

    /**
     * 备份插件
     * @param string $name 插件名称
     * @return bool
     * @throws Exception
     * @author ^2_3^
     */
    public static function backup($name)
    {
        // 备份文件
        $file = RUNTIME_PATH . 'addons' . DS . $name . '-backup-' . date("YmdHis") . '.zip';

        // 插件目录
        $dir = ADDON_PATH . $name . DS;

        //// 压缩文件
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            // 创建归档文件
            $zip->open($file, ZipArchive::CREATE);
            // 获取文件
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                $filePath = $fileinfo->getPathName();

                $localName = str_replace($dir, '', $filePath);

                if ($fileinfo->isFile()) {
                    $zip->addFile($filePath, $localName);

                } elseif ($fileinfo->isDir()) {
                    $zip->addEmptyDir($localName);
                }
            }

            $zip->close();
            return true;
        }
        throw new Exception("无法执行压缩操作，请确保ZipArchive安装正确");
    }

    /**
     * 检测插件是否完整
     *
     * @param   string $name 插件名称
     * @return  boolean
     * @throws  Exception
     * @author ^2_3^
     */
    public static function check($name)
    {
        if (!$name || !is_dir(ADDON_PATH . $name)) {
            throw new Exception('Addon not exists');
        }

        $addonClass = get_addon_class($name);
        if (!$addonClass) {
            throw new Exception("插件主启动程序不存在");
        }

        $addon = new $addonClass();
        // 检查基础配置信息是否完整
        if (!$addon->checkInfo()) {
            throw new Exception("配置文件不完整");
        }
        return true;
    }

    /**
     * 是否有冲突
     *
     * @param   string $name 插件名称
     * @return  boolean
     * @throws  AddonException
     * @author  ^2_3^
     */
    public static function noconflict($name)
    {
        // 检测冲突文件
        $list = self::getGlobalFiles($name, true);
        if ($list) {
            //发现冲突文件，抛出异常
            throw new AddonException("发现冲突文件", -3, ['conflictlist' => $list]);
        }
        return true;
    }

    /**
     * 导入SQL
     *
     * @param   string $name 插件名称
     * @return  boolean
     * @author ^2_3^
     */
    public static function importsql($name)
    {
        // 安装sql文件
        $sqlFile = ADDON_PATH . $name . DS . 'install.sql';

        if (is_file($sqlFile)) {
            // 读取文件信息
            $lines = file($sqlFile);

            // 临时行数据
            $templine = '';
            foreach ($lines as $line) {
                //// 忽略注释行及空行
                if (
                    substr($line, 0, 2) == '--' ||
                    $line == '' ||
                    substr($line, 0, 2) == '/*'
                ){
                    continue;
                }

                $templine .= $line;

                //// 是否sql语句结束
                if (substr(trim($line), -1, 1) == ';') {
                    // 设置表前缀
                    $templine = str_ireplace('__PREFIX__', config('database.prefix'), $templine);
                    // 处理插入语句
                    $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);

                    // 执行语句
                    try {
                        Db::getPdo()->exec($templine);

                    } catch (\PDOException $e) {
                        //$e->getMessage();
                    }

                    // 清空
                    $templine = '';
                }
            }
        }
        return true;
    }

    /**
     * 刷新插件缓存文件
     *
     * @return  boolean
     * @throws  Exception
     *
     * @author ^2_3^
     */
    public static function refresh()
    {
        // 刷新addons.js
        $addons = get_addon_list();

        $bootstrapArr = [];
        foreach ($addons as $name => $addon) {
            $bootstrapFile = ADDON_PATH . $name . DS . 'bootstrap.js';
            if ($addon['state'] && is_file($bootstrapFile)) {
                $bootstrapArr[] = file_get_contents($bootstrapFile);
            }
        }

        $addonsFile = ROOT_PATH . str_replace("/", DS, "public/assets/js/addons.js");
        if ($handle = fopen($addonsFile, 'w')) {
            $tpl = <<<EOD
define([], function () {
    {__JS__}
});
EOD;
            fwrite($handle, str_replace("{__JS__}", implode("\n", $bootstrapArr), $tpl));
            fclose($handle);

        } else {
            throw new Exception("addons.js文件没有写入权限");
        }

        // application/extra/addons.php
        $file = APP_PATH . 'extra' . DS . 'addons.php';

        // 获得插件自动加载的配置
        $config = get_addon_autoload_config(true);
        if ($config['autoload']){
            return;
        }

        if (!is_really_writable($file)) {
            throw new Exception("addons.php文件没有写入权限");
        }

        if ($handle = fopen($file, 'w')) {
            fwrite($handle, "<?php\n\n" . "return " . var_export($config, TRUE) . ";");
            fclose($handle);

        } else {
            throw new Exception("文件没有写入权限");
        }
        return true;
    }

    /**
     * 安装插件
     *
     * @param  string $name 插件名称
     * @param  boolean $force 是否覆盖
     * @param  array $extend 扩展参数
     *  $extend = [
            'uid'       => $uid,
            'token'     => $token,
            'version'   => $version,
            'faversion' => $faversion
        ];
     * @return boolean
     * @throws Exception
     * @throws AddonException
     * @author ^2_3^
     */
    public static function install($name, $force = false, $extend = [])
    {
        if (!$name || (is_dir(ADDON_PATH . $name) && !$force)) {
            throw new Exception('Addon already exists');
        }

        // 远程下载插件(临时文件路径名称)
        $tmpFile = Service::download($name, $extend);

        // 解压插件(解压到插件目录/addons)
        $addonDir = Service::unzip($name);

        // 移除临时文件
        @unlink($tmpFile);

        try {
            // 检查插件是否完整(检查基础配置信息是否完整就行)
            Service::check($name);

            if (!$force) {
                // 是否有冲突
                Service::noconflict($name);
            }

        } catch (AddonException $e) {
            // 移除插件目录
            @rmdirs($addonDir);
            throw new AddonException($e->getMessage(), $e->getCode(), $e->getData());

        } catch (Exception $e) {
            @rmdirs($addonDir);
            throw new Exception($e->getMessage());
        }

        // 复制文件(资源文件)
        $sourceAssetsDir = self::getSourceAssetsDir($name);
        $destAssetsDir = self::getDestAssetsDir($name);
        if (is_dir($sourceAssetsDir)) {
            copydirs($sourceAssetsDir, $destAssetsDir);
        }

        //// 获取检测的全局文件夹目录('application', 'public')
        /// 复制文件
        foreach (self::getCheckDirs() as $k => $dir) {
            if (is_dir($addonDir . $dir)) {
                copydirs($addonDir . $dir, ROOT_PATH . $dir);
            }
        }

        try {
            // 默认启用该插件
            $info = get_addon_info($name);
            if (!$info['state']) { //禁用;
                $info['state'] = 1;

                // 写入基本信息文件
                set_addon_info($name, $info);
            }

            // 执行安装脚本
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();
                $addon->install(); //执行插件安装(如添加菜单规则);
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 导入(执行插件安装sql文件)
        Service::importsql($name);

        // 刷新
        Service::refresh();
        return true;
    }

    /**
     * 卸载插件
     *
     * @param   string $name
     * @param   boolean $force 是否强制卸载
     * @return  boolean
     * @throws  Exception
     * @author ^2_3^
     */
    public static function uninstall($name, $force = false)
    {
        if (!$name || !is_dir(ADDON_PATH . $name)) {
            throw new Exception('Addon not exists');
        }

        if (!$force) {
            // 是否有冲突
            Service::noconflict($name);
        }

        // 移除插件基础资源目录
        $destAssetsDir = self::getDestAssetsDir($name);
        if (is_dir($destAssetsDir)) {
            rmdirs($destAssetsDir);
        }

        // 移除插件全局资源文件
        if ($force) {
            $list = Service::getGlobalFiles($name);
            foreach ($list as $k => $v) {
                @unlink(ROOT_PATH . $v);
            }
        }

        // 执行卸载脚本
        try {
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();
                $addon->uninstall();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 移除插件目录
        rmdirs(ADDON_PATH . $name);

        // 刷新
        Service::refresh();
        return true;
    }

    /**
     * 启用
     * @param   string $name 插件名称
     * @param   boolean $force 是否强制覆盖
     * @return  boolean
     * @author ^2_3^
     */
    public static function enable($name, $force = false)
    {
        if (!$name || !is_dir(ADDON_PATH . $name)) {
            throw new Exception('Addon not exists');
        }

        if (!$force) {
            // 是否有冲突
            Service::noconflict($name);
        }

        // 插件目录
        $addonDir = ADDON_PATH . $name . DS;

        // 复制文件
        $sourceAssetsDir = self::getSourceAssetsDir($name);
        $destAssetsDir = self::getDestAssetsDir($name);
        if (is_dir($sourceAssetsDir)) {
            copydirs($sourceAssetsDir, $destAssetsDir);
        }
        foreach (self::getCheckDirs() as $k => $dir) {
            if (is_dir($addonDir . $dir)) {
                copydirs($addonDir . $dir, ROOT_PATH . $dir);
            }
        }

        //执行启用脚本
        try {
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();
                if (method_exists($class, "enable")) {
                    $addon->enable();
                }
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $info = get_addon_info($name);
        $info['state'] = 1;
        unset($info['url']);
        set_addon_info($name, $info);

        // 刷新
        Service::refresh();
        return true;
    }

    /**
     * 禁用
     *
     * @param   string $name 插件名称
     * @param   boolean $force 是否强制禁用
     * @return  boolean
     * @throws  Exception
     * @author ^2_3^
     */
    public static function disable($name, $force = false)
    {
        if (!$name || !is_dir(ADDON_PATH . $name)) {
            throw new Exception('Addon not exists');
        }

        if (!$force) {
            // 是否有冲突
            Service::noconflict($name);
        }

        // 移除插件基础资源目录
        $destAssetsDir = self::getDestAssetsDir($name);
        if (is_dir($destAssetsDir)) {
            rmdirs($destAssetsDir);
        }

        // 移除插件全局资源文件
        $list = Service::getGlobalFiles($name);
        foreach ($list as $k => $v) {
            @unlink(ROOT_PATH . $v);
        }

        $info = get_addon_info($name);
        $info['state'] = 0;
        unset($info['url']);

        set_addon_info($name, $info);

        // 执行禁用脚本
        try {
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();

                if (method_exists($class, "disable")) {
                    $addon->disable();
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 刷新
        Service::refresh();
        return true;
    }

    /**
     * 升级插件
     *
     * @param   string $name 插件名称
     * @param   array $extend 扩展参数
     * @author ^2_3^
     */
    public static function upgrade($name, $extend = [])
    {
        // 插件基本信息
        $info = get_addon_info($name);
        if ($info['state']) {
            throw new Exception(__('Please disable addon first'));
        }

        // 插件配置信息
        $config = get_addon_config($name);
        if ($config) {
            //备份配置
        }

        // 备份插件文件，压缩成*.zip文件
        Service::backup($name);

        // 远程下载插件
        $tmpFile = Service::download($name, $extend);

        // 解压插件
        $addonDir = Service::unzip($name);

        // 移除临时文件
        @unlink($tmpFile);

        if ($config) {
            // 还原配置
            set_addon_config($name, $config);
        }

        // 导入
        Service::importsql($name);

        // 执行升级脚本
        try {
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();

                if (method_exists($class, "upgrade")) {
                    $addon->upgrade();
                }
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 刷新
        Service::refresh();

        return true;
    }

    /**
     * 获取插件在全局的文件
     *
     * @param   string $name 插件名称
     * @param   $onlyconflict 是否检查冲突
     * @return  array
     * @author  ^2_3^
     */
    public static function getGlobalFiles($name, $onlyconflict = false)
    {
        // 有冲突文件列表
        $list = [];

        // 插件目录
        $addonDir = ADDON_PATH . $name . DS;

        // 扫描插件目录是否有覆盖的文件(是否有'application', 'public')
        foreach (self::getCheckDirs() as $k => $dir) {
            // 检查目录
            $checkDir = ROOT_PATH . DS . $dir . DS;
            if (!is_dir($checkDir)){
                continue;
            }

            //// 检测到存在插件外目录
            if (is_dir($addonDir . $dir)) {
                // 匹配出所有的文件
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        $addonDir . $dir,
                        RecursiveDirectoryIterator::SKIP_DOTS
                    ),
                    RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $fileinfo) {
                    if ($fileinfo->isFile()) {
                        $filePath = $fileinfo->getPathName();
                        $path = str_replace($addonDir, '', $filePath);

                        if ($onlyconflict) {
                            $destPath = ROOT_PATH . $path;
                            if (is_file($destPath)) {
                                //// 文件大小、md5值不同则指明有冲突
                                if (
                                    filesize($filePath) != filesize($destPath) ||
                                    md5_file($filePath) != md5_file($destPath)
                                ) {
                                    $list[] = $path;
                                }
                            }

                        } else {
                            $list[] = $path;
                        }
                    }
                }
            }
        }

        return $list;
    }

    /**
     * 获取插件源资源文件夹
     * @param   string $name 插件名称
     * @return  string 插件源资源文件夹路径
     * @author ^2_3^
     */
    protected static function getSourceAssetsDir($name)
    {
        return ADDON_PATH . $name . DS . 'assets' . DS;
    }

    /**
     * 获取插件目标资源文件夹
     * @param   string $name 插件名称
     * @return  string 返回插件目标资源文件夹
     * @author ^2_3^
     */
    protected static function getDestAssetsDir($name)
    {
        $assetsDir = ROOT_PATH . str_replace("/", DS, "public/assets/addons/{$name}/");
        if (!is_dir($assetsDir)) {
            mkdir($assetsDir, 0755, true);
        }
        return $assetsDir;
    }

    /**
     * 获取远程服务器
     * @return  string
     * @author ^2_3^
     */
    protected static function getServerUrl()
    {
        // https://api.fastadmin.net
        return config('fastadmin.api_url');
    }

    /**
     * 获取检测的全局文件夹目录
     * @return  array
     * @author ^2_3^
     */
    protected static function getCheckDirs()
    {
        return [
            'application',
            'public'
        ];
    }

}
