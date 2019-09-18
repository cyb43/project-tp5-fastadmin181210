<?php

namespace app\admin\controller;

use app\admin\model\AuthGroup;
use app\common\controller\Backend;
use fast\Date;
use fast\Http;
use fast\Pinyin;
use fast\Random;
use fast\Rsa;
use fast\Tree;

/**
 * 测试管理
 *
 * @icon fa fa-circle-o
 * @author ^2_3^
 */
class Test extends Backend
{

    protected $noNeedLogin = ['req4async', 'async'];

    /**
     * Test模型对象
     * @var \app\admin\model\Test
     */
    protected $model = null;

    /**
     * 初始化
     * @author ^2_3^王尔贝
     */
    public function _initialize()
    {
        parent::_initialize();

        $this->model = new \app\admin\model\Test;

        $this->view->assign("weekList", $this->model->getWeekList()); //星期列表;
        $this->view->assign("flagList", $this->model->getFlagList()); //标志列表;
        $this->view->assign("genderdataList", $this->model->getGenderdataList()); //性别列表;
        $this->view->assign("hobbydataList", $this->model->getHobbydataList()); //爱好列表;
        $this->view->assign("statusList", $this->model->getStatusList()); //状态列表;
        $this->view->assign("stateList", $this->model->getStateList()); //状态列表;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi(批量更新)五个基础方法、destroy/restore(还原)/recyclebin三个回收站方法，
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑，
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改。
     */

    /**
     * 异步请求
     * @author ^2_3^王尔贝
     */
    public function req4async()
    {
//        file_put_contents('^2_3^.txt', 'req4async_start('.time().")\r\n", FILE_APPEND);
        // 服务器异步请求
        $result = Http::sendAsyncRequest('http://project-tp5-fastadmin181210.test/admin/test/async');
//        file_put_contents('^2_3^.txt', 'req4async_end('.time().")\r\n", FILE_APPEND);
//        file_put_contents('^2_3^.txt', 'req4async_result('.\GuzzleHttp\json_encode($result).")\r\n",
//            FILE_APPEND);

        $this->success('已经发送服务器异步请求');
    }
    /**
     * 异步请求
     * @author ^2_3^王尔贝
     */
    public function async()
    {
//        file_put_contents('^2_3^.txt', 'async_start('.time().")\r\n", FILE_APPEND);
        $all_arr = $this->request->request();

        //sleep(10);
        $times = 60 * 10;
        for ( $i=0; $i< $times; $i++) {
            if( $i == 60 ) {
                break;
            }

            sleep(1);

//            $num = $i + 1;
//            file_put_contents('^2_3^.txt',"已停止{$num}秒;"."\r\n", FILE_APPEND);
        }

        $data = [
            'sleep' => 10,
            'name' => 'cyb',
            'params' => $all_arr
        ];
//        file_put_contents('^2_3^.txt', 'async_end('.time().")\r\n", FILE_APPEND);

        $this->success('成功', null, $data);
    }

}
