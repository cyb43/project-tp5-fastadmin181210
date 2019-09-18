<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Http;
use think\addons\AddonException;
use think\addons\Service;
use think\Cache;
use think\Config;
use think\Exception;

/**
 * 插件管理
 *
 * @icon fa fa-circle-o
 * @remark 可在线安装、卸载、禁用、启用插件，同时支持添加本地插件。
 * FastAdmin已上线插件商店，你可以发布你的免费或付费插件。
 * <a href="https://www.fastadmin.net/store.html" target="_blank">https://www.fastadmin.net/store.html</a>
 *
 * @author ^2_3^
 */
class Addon extends Backend
{

    protected $model = null;

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 查看
     * @author ^2_3^
     */
    public function index()
    {
        // 获取本地插件列表
        /*
        array(4) {
          ["command"] => array(8) {
            ["name"] => string(7) "command"
            ["title"] => string(12) "在线命令"
            ["intro"] => string(48) "可在线执行FastAdmin的命令行相关命令"
            ["author"] => string(6) "Karson"
            ["website"] => string(24) "http://www.fastadmin.net"
            ["version"] => string(5) "1.0.5"
            ["state"] => string(1) "1"
            ["url"] => string(15) "/addons/command"
          }
          ["database"] => array(8) {
            ["name"] => string(8) "database"
            ["title"] => string(15) "数据库管理"
            ["intro"] => string(21) "数据库管理插件"
            ["author"] => string(6) "Karson"
            ["website"] => string(24) "http://www.fastadmin.net"
            ["version"] => string(5) "1.0.3"
            ["state"] => string(1) "1"
            ["url"] => string(16) "/addons/database"
          }
          ["example"] => array(8) {
            ["name"] => string(7) "example"
            ["title"] => string(12) "开发示例"
            ["intro"] => string(27) "FastAdmin多个开发示例"
            ["author"] => string(6) "Karson"
            ["website"] => string(24) "http://www.fastadmin.net"
            ["version"] => string(5) "1.0.7"
            ["state"] => string(1) "1"
            ["url"] => string(15) "/addons/example"
          }
          ["summernote"] => array(8) {
            ["name"] => string(10) "summernote"
            ["title"] => string(28) "Summernote富文本编辑器"
            ["intro"] => string(40) "修改后台默认编辑器为Summernote"
            ["author"] => string(6) "Karson"
            ["website"] => string(24) "http://www.fastadmin.net"
            ["version"] => string(5) "1.0.3"
            ["state"] => string(1) "1"
            ["url"] => string(18) "/addons/summernote"
          }
        }
        */
        $addons = get_addon_list();
        foreach ($addons as $k => &$v) {
            // 获取配置信息
            $config = get_addon_config($v['name']);
            $v['config'] = $config ? 1 : 0;

            // 插件配置信息
//            if( $v['config'] ) {
//                $v['config_infos'] = $config;
//            }
        }
        $this->assignconfig(['addons' => $addons]);

        /*
        array(4) {
          ["command"] => array(9) {
            ["name"] => string(7) "command"
            ["title"] => string(12) "在线命令"
            ["intro"] => string(48) "可在线执行FastAdmin的命令行相关命令"
            ["author"] => string(6) "Karson"
            ["website"] => string(24) "http://www.fastadmin.net"
            ["version"] => string(5) "1.0.5"
            ["state"] => string(1) "1"
            ["url"] => string(15) "/addons/command"
            ["config"] => int(0)
          }
          ["database"] => array(10) {
            ["name"] => string(8) "database"
            ["title"] => string(15) "数据库管理"
            ["intro"] => string(21) "数据库管理插件"
            ["author"] => string(6) "Karson"
            ["website"] => string(24) "http://www.fastadmin.net"
            ["version"] => string(5) "1.0.3"
            ["state"] => string(1) "1"
            ["url"] => string(16) "/addons/database"
            ["config"] => int(1)
            ["config_infos"] => array(3) {
              ["backupDir"] => string(8) "../data/"
              ["backupIgnoreTables"] => string(12) "fa_admin_log"
              ["__tips__"] => string(117) "请做好数据库离线备份工作，建议此插件仅用于开发阶段，项目正式上线建议卸载此插件"
            }
          }
          ["example"] => array(10) {
            ["name"] => string(7) "example"
            ["title"] => string(12) "开发示例"
            ["intro"] => string(27) "FastAdmin多个开发示例"
            ["author"] => string(6) "Karson"
            ["website"] => string(24) "http://www.fastadmin.net"
            ["version"] => string(5) "1.0.7"
            ["state"] => string(1) "1"
            ["url"] => string(15) "/addons/example"
            ["config"] => int(1)
            ["config_infos"] => array(4) {
              ["title"] => string(12) "示例标题"
              ["theme"] => string(7) "default"
              ["domain"] => string(0) ""
              ["rewrite"] => array(4) {
                ["index/index"] => string(9) "/example$"
                ["demo/index"] => string(18) "/example/d/[:name]"
                ["demo/demo1"] => string(19) "/example/d1/[:name]"
                ["demo/demo2"] => string(19) "/example/d2/[:name]"
              }
            }
          }
          ["summernote"] => &array(9) {
            ["name"] => string(10) "summernote"
            ["title"] => string(28) "Summernote富文本编辑器"
            ["intro"] => string(40) "修改后台默认编辑器为Summernote"
            ["author"] => string(6) "Karson"
            ["website"] => string(24) "http://www.fastadmin.net"
            ["version"] => string(5) "1.0.3"
            ["state"] => string(1) "1"
            ["url"] => string(18) "/addons/summernote"
            ["config"] => int(0)
          }
        }
        */

        return $this->view->fetch();
    }

    /**
     * 配置
     * @author ^2_3^
     */
    public function config($ids = NULL)
    {
        $name = $this->request->get("name");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', $ids ? 'id' : 'name'));
        }

        // 判断是否存在插件目录
        if (!is_dir(ADDON_PATH . $name)) {
            $this->error(__('Directory not found'));
        }

        // 获取插件信息
        $info = get_addon_info($name);
        // 获取插件完全配置
        $config = get_addon_fullconfig($name);
        if (!$info){
            $this->error(__('No Results were found'));
        }

        // 提交配置信息
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            if ($params) {
                foreach ($config as $k => &$v) {
                    //// 合并配置
                    if (isset($params[$v['name']])) {
                        // 配置类型
                        if ($v['type'] == 'array') {
                            $params[$v['name']] = is_array($params[$v['name']]) ?
                                $params[$v['name']] : (array)json_decode($params[$v['name']], true);
                            $value = $params[$v['name']];

                        } else {
                            $value = is_array($params[$v['name']]) ?
                                implode(',', $params[$v['name']]) : $params[$v['name']];
                        }

                        $v['value'] = $value;
                    }
                }

                try {
                    //更新配置文件
                    set_addon_fullconfig($name, $config);
                    Service::refresh();
                    $this->success();

                } catch (Exception $e) {
                    $this->error(__($e->getMessage()));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $tips = [];
        foreach ($config as $index => &$item) {
            if ($item['name'] == '__tips__') {
                $tips = $item;
                unset($config[$index]);
            }
        }

        $this->view->assign("addon", ['info' => $info, 'config' => $config, 'tips' => $tips]);
        $configFile = ADDON_PATH . $name . DS . 'config.html';
        $viewFile = is_file($configFile) ? $configFile : '';
        return $this->view->fetch($viewFile);
    }

    /**
     * 安装
     * @author ^2_3^
     */
    public function install()
    {
        // 插件名称
        $name = $this->request->post("name");
        // 是否强制
        $force = (int)$this->request->post("force");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }

        try {
            $uid = $this->request->post("uid");
            $token = $this->request->post("token");
            $version = $this->request->post("version");
            $faversion = $this->request->post("faversion");
            $extend = [
                'uid'       => $uid,
                'token'     => $token,
                'version'   => $version,
                'faversion' => $faversion
            ];

            // 执行插件类安装方法
            Service::install($name, $force, $extend);

            //// 获取插件基本信息
            $info = get_addon_info($name);
            $info['config'] = get_addon_config($name) ? 1 : 0;
            $info['state'] = 1;
            $this->success(__('Install successful'), null, ['addon' => $info]);

        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));

        } catch (Exception $e) {
            $this->error(__($e->getMessage()), $e->getCode());
        }
    }

    /**
     * 卸载
     * @author ^2_3^
     */
    public function uninstall()
    {
        // 插件名称
        $name = $this->request->post("name");
        // 是否强制
        $force = (int)$this->request->post("force");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }

        try {
            Service::uninstall($name, $force);//???
            $this->success(__('Uninstall successful'));

        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));

        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
    }

    /**
     * 禁用启用
     * @author ^2_3^
     */
    public function state()
    {
        // 插件名称
        $name = $this->request->post("name");
        // 操作
        $action = $this->request->post("action");
        // 是否强制覆盖
        $force = (int)$this->request->post("force");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }

        try {
            // 操作类型
            $action = $action == 'enable' ? $action : 'disable';

            // 调用启用、禁用的方法
            Service::$action($name, $force);

            // 清除菜单缓存
            Cache::rm('__menu__');

            $this->success(__('Operate successful'));

        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));

        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
    }

    /**
     * 本地上传(离线安装)
     * @author ^2_3^
     */
    public function local()
    {
        Config::set('default_return_type', 'json');

        // 插件压缩包
        $file = $this->request->file('file');

        // 临时目录
        $addonTmpDir = RUNTIME_PATH . 'addons' . DS;
        if (!is_dir($addonTmpDir)) {
            @mkdir($addonTmpDir, 0755, true);
        }


        $info = $file->rule('uniqid') //使用唯一名称规则;
            ->validate(['size' => 10240000, 'ext' => 'zip']) //验证文件;
            ->move($addonTmpDir); //移动到临时目录;

        if ($info) {
            $tmpName = substr($info->getFilename(), 0, stripos($info->getFilename(), '.'));

            // 插件临时目录(后面根据基本信息name属性修改)
            $tmpAddonDir = ADDON_PATH . $tmpName . DS;

            // 文件路径
            $tmpFile = $addonTmpDir . $info->getSaveName();

            try {
                // 解压插件包
                Service::unzip($tmpName);

                // 删除文件
                @unlink($tmpFile);

                //// 插件基本信息???
                $infoFile = $tmpAddonDir . 'info.ini';
                if (!is_file($infoFile)) {
                    throw new Exception(__('Addon info file was not found'));
                }

                //// 解析*.ini插件基本信息文件
                $config = Config::parse($infoFile, '', $tmpName);
                $name = isset($config['name']) ? $config['name'] : '';
                if (!$name) {
                    throw new Exception(__('Addon info file data incorrect'));
                }

                $newAddonDir = ADDON_PATH . $name . DS;
                if (is_dir($newAddonDir)) {
                    throw new Exception(__('Addon already exists'));
                }

                //重命名插件文件夹
                rename($tmpAddonDir, $newAddonDir);

                try {
                    // 默认禁用该插件
                    $info = get_addon_info($name);
                    if ($info['state']) {
                        $info['state'] = 0;
                        set_addon_info($name, $info);
                    }

                    // 执行插件的安装方法
                    $class = get_addon_class($name);
                    if (class_exists($class)) {
                        $addon = new $class();
                        $addon->install();
                    }

                    //导入SQL
                    Service::importsql($name);

                    $info['config'] = get_addon_config($name) ? 1 : 0;
                    $this->success(__('Offline installed tips'), null, ['addon' => $info]);

                } catch (Exception $e) {
                    @rmdirs($newAddonDir);
                    throw new Exception(__($e->getMessage()));
                }

            } catch (Exception $e) {
                @unlink($tmpFile);
                @rmdirs($tmpAddonDir);
                $this->error(__($e->getMessage()));
            }

        } else {
            // 上传失败获取错误信息
            $this->error(__($file->getError()));
        }
    }

    /**
     * 更新插件
     * @author ^2_3^
     */
    public function upgrade()
    {
        // 插件名称
        $name = $this->request->post("name");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }

        try {
            $uid = $this->request->post("uid");
            $token = $this->request->post("token");
            $version = $this->request->post("version");
            $faversion = $this->request->post("faversion");
            $extend = [
                'uid'       => $uid,
                'token'     => $token,
                'version'   => $version,
                'faversion' => $faversion
            ];

            // 调用更新的方法
            Service::upgrade($name, $extend);

            // 清除菜单缓存
            Cache::rm('__menu__');
            $this->success(__('Operate successful'));

        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));

        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
    }

    /**
     * 已装插件
     * @author ^2_3^
     */
    public function downloaded()
    {
        $offset = (int)$this->request->get("offset");
        $limit = (int)$this->request->get("limit");
        $filter = $this->request->get("filter");
        // 搜索数值
        $search = $this->request->get("search");
        $search = htmlspecialchars(strip_tags($search));

        // 在线插件
        $onlineaddons = Cache::get("onlineaddons");
        if (!is_array($onlineaddons)) {
            $onlineaddons = [];
            $result = Http::sendRequest(config('fastadmin.api_url') . '/addon/index');

            if ($result['ret']) {
                $json = json_decode($result['msg'], TRUE);

                //// 在线插件列表
                $rows = isset($json['rows']) ? $json['rows'] : [];
                foreach ($rows as $index => $row) {
                    $onlineaddons[$row['name']] = $row;
                }
            }

            // 缓存在线插件
            Cache::set("onlineaddons", $onlineaddons, 600);
        }

        // 过滤数组
        $filter = (array)json_decode($filter, true);

        // 获得插件列表
        $addons = get_addon_list();

        $list = []; //符合搜索条件的插件列表;
        foreach ($addons as $k => $v) {
            // 忽略不匹配插件
            if ($search && stripos($v['name'], $search) === FALSE && stripos($v['intro'], $search) === FALSE){
                continue;
            }

            if (isset($onlineaddons[$v['name']])) {
                $v = array_merge($v, $onlineaddons[$v['name']]);

            } else {
                $v['category_id'] = 0;
                $v['flag'] = '';
                $v['banner'] = '';
                $v['image'] = '';
                $v['donateimage'] = '';
                $v['demourl'] = '';
                $v['price'] = '0.00';
                $v['screenshots'] = [];
                $v['releaselist'] = [];
            }
            $v['url'] = addon_url($v['name']);
            $v['createtime'] = filemtime(ADDON_PATH . $v['name']);

            //// 插件分类过滤
            if ($filter && isset($filter['category_id']) &&
                is_numeric($filter['category_id']) && $filter['category_id'] != $v['category_id']) {
                continue;
            }

            $list[] = $v;
        }

        $total = count($list);
        if ($limit) {
            $list = array_slice($list, $offset, $limit);
        }
        $result = array("total" => $total, "rows" => $list);

        // 返回格式
        $callback = $this->request->get('callback') ? "jsonp" : "json";
        return $callback($result);
    }

}
