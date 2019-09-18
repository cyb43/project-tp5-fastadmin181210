<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据；
 * @author ^2_3^
 */
class Dashboard extends Backend
{

    /**
     * 查看
     * @author ^2_3^
     */
    public function index()
    {

//        dump('Dashboard');exit();

        $seventtime = \fast\Date::unixtime('day', -7);

        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }

        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';

        // ROOT_PATH，框架应用根目录；
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');

        $this->view->assign([
            'totaluser'        => 0, //总会员数;
            'totalviews'       => 0, //总访问数;
            'totalorder'       => 0, //总订单数;
            'totalorderamount' => 0, //总金额;
            'todayuserlogin'   => 0, //今日登录;
            'todayusersignup'  => 0, //今日注册;
            'todayorder'       => 0, //今日订单;
            'unsettleorder'    => 0, //未处理订单;
            'sevendnu'         => '0.00%', //七日新增;
            'sevendau'         => '0.00%', //七日活跃;

            'paylist'          => $paylist,
            'createlist'       => $createlist,
            'addonversion'     => $addonVersion, //FastAdmin插件版本;
            'uploadmode'       => $uploadmode //上传模式;
        ]);

        return $this->view->fetch();
    }

}
