<?php

namespace app\admin\controller\general;

use app\common\controller\Backend;
use app\common\library\Email;
use app\common\model\Config as ConfigModel;
use think\Exception;
use think\Log;

/**
 * 系统配置
 *
 * @icon fa fa-cogs
 * @remark 可以在此增改系统的变量和分组,也可以自定义分组和变量,如果需要删除请从数据库中删除
 * @author ^2_3^
 */
class Config extends Backend
{

    /**
     * @var \app\common\model\Config
     */
    protected $model = null;

    protected $noNeedRight = ['check'];

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Config');
    }

    /**
     * 查看
     * @author ^2_3^
     */
    public function index()
    {
        $siteList = [];

        // 配置分组列表
        $groupList = ConfigModel::getGroupList();
        foreach ($groupList as $k => $v) {
            $siteList[$k]['name'] = $k;
            $siteList[$k]['title'] = $v;
            $siteList[$k]['list'] = [];
        }

        // 按分组组装数据
        foreach ($this->model->all() as $k => $v) {
            if (!isset($siteList[$v['group']])) {
                continue;
            }

            $value = $v->toArray();
            $value['title'] = __($value['title']);

            if (in_array($value['type'], ['select', 'selects', 'checkbox', 'radio'])) {
                $value['value'] = explode(',', $value['value']);
            }

            $value['content'] = json_decode($value['content'], TRUE);
            $siteList[$v['group']]['list'][] = $value;
        }

        $index = 0;
        foreach ($siteList as $k => &$v) {
            $v['active'] = !$index ? true : false; //默认首项设置为激活状态；
            $index++;
        }

        $this->view->assign('siteList', $siteList); //系统配置分组；
        $this->view->assign('typeList', ConfigModel::getTypeList()); //类型列表；
        $this->view->assign('groupList', ConfigModel::getGroupList()); //分组列表；
        return $this->view->fetch();
    }

    /**
     * 添加
     * @author ^2_3^
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a"); //参数数组;

            if ($params) {
                // 处理数组
                foreach ($params as $k => &$v) {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }

                try {

                    // 根据数据列表处理content值
                    if (in_array($params['type'], ['select', 'selects', 'checkbox', 'radio', 'array'])) {
                        $params['content'] = json_encode(ConfigModel::decode($params['content']),
                            JSON_UNESCAPED_UNICODE);

                    } else {
                        $params['content'] = '';
                    }

                    //// 模型：添加数据；
                    $result = $this->model->create($params);
                    if ($result !== false) {
                        try {
                            $this->refreshFile();//刷新配置文件(从数据库写入配置文件)；

                        } catch (Exception $e) {
                            $this->error($e->getMessage());
                        }
                        $this->success();

                    } else {
                        $this->error($this->model->getError());
                    }

                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     * @param null $ids
     * @author ^2_3^
     */
    public function edit($ids = NULL)
    {
        if ($this->request->isPost()) {
            // 配置参数数据
            $row = $this->request->post("row/a");

            if ($row) {
                // 配置列表
                $configList = [];

                foreach ($this->model->all() as $v) {
                    if (isset($row[$v['name']])) {
                        $value = $row[$v['name']];

                        if (is_array($value) && isset($value['field'])) {
                            $value = json_encode(ConfigModel::getArrayData($value), JSON_UNESCAPED_UNICODE);

                        } else {
                            $value = is_array($value) ? implode(',', $value) : $value;
                        }
                        $v['value'] = $value;

                        $configList[] = $v->toArray();
                    }
                }

                // 过滤post数组中的非数据表字段数据
                $this->model->allowField(true)->saveAll($configList);
                try {
                    $this->refreshFile(); //刷新配置文件;

                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
    }

    /**
     * 选项配置(如果需要删除请从数据库中删除)
     * @param string $ids
     * @author ^2_3^
     */
    public function del($ids = "")
    {
        // 删除配置
        /*
        <!-- 删除配置选项 -->
        <a href="javascript:;" class="btn-delcfg text-muted" data-name="{$item.name}">
            <i class="fa fa-times"></i>
        </a>
        $(document).on("click", ".btn-delcfg", function () {
            var that = this;

            Layer.confirm(__('Are you sure you want to delete this item?'), {icon: 3, title:'提示'},
                    // yes
                    function (index) {
                        Backend.api.ajax({
                            url: "general/config/del?receiver=" + value,
                            // 获取data-name属性数值
                            data: {name: $(that).data("name")}

                        }, function () {
                            // 删除元素
                            $(that).closest("tr").remove();
                            // 关闭弹层
                            Layer.close(index);
                        });

                    });

        });
        */

        $name = $this->request->request('name');
        $config = ConfigModel::getByName($name);

        if ($config) {
            try {
                $config->delete();
                $this->refreshFile();

            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success();

        } else {
            $this->error(__('Invalid parameters'));
        }
    }

    /**
     * 刷新配置文件
     * @author ^2_3^
     */
    protected function refreshFile()
    {
        $config = [];
        foreach ($this->model->all() as $k => $v) {

            $value = $v->toArray();

            if (in_array($value['type'], ['selects', 'checkbox', 'images', 'files'])) {
                $value['value'] = explode(',', $value['value']);
            }

            if ($value['type'] == 'array') {
                $value['value'] = (array)json_decode($value['value'], TRUE);
            }

            $config[$value['name']] = $value['value'];
        }
        file_put_contents(APP_PATH . 'extra' . DS . 'site.php',
            '<?php' . "\n\nreturn " . var_export($config, true) . ";");
    }

    /**
     * 检测配置项是否存在
     * @internal
     * @author ^2_3^
     */
    public function check()
    {
        /*
        <!-- 变量名(动态检查) -->
        <div class="form-group">
            <label for="name" class="control-label col-xs-12 col-sm-2">{:__('Name')}:</label>
            <div class="col-xs-12 col-sm-4">
                <input type="text" class="form-control" id="name" name="row[name]" value=""
                       data-rule="required; length(3~30); remote(general/config/check)" />
            </div>
        </div>
        */
        $params = $this->request->post("row/a");
        if ($params) {

            $config = $this->model->get($params);
            if (!$config) {
                return $this->success();
            } else {
                return $this->error(__('Name already exist'));
            }

        } else {
            return $this->error(__('Invalid parameters'));
        }
    }

    /**
     * 发送测试邮件
     * @internal
     * @author ^2_3^
     */
    public function emailtest()
    {
        /*
        // 添加向发件人发送测试邮件按钮和方法
        $('input[name="row[mail_from]"]').parent().next().append(
            '<a class="btn btn-info testmail">' + __('Send a test message') + '</a>');
        $(document).on("click", ".testmail", function () {
            var that = this;

            Layer.prompt({title: __('Please input your email'), formType: 0}, function (value, index) {
                Backend.api.ajax({
                        url: "general/config/emailtest?receiver=" + value,
                        data: $(that).closest("form").serialize() //邮件配置信息;
                });
            });

        });
        */

        $row = $this->request->post('row/a');

        // 合并配置
        \think\Config::set('site', array_merge(\think\Config::get('site'), $row));

        $receiver = $this->request->request("receiver");

        //// 发送邮件
        $email = new Email;
        $result = $email
            ->to($receiver)
            ->subject(__("This is a test mail"))
            ->message('<div style="min-height:550px; padding: 100px 55px 200px;">' .
                __('This is a test mail content') . '</div>')
            ->send();
        if ($result) {
            $this->success();

        } else {
            $this->error($email->getError());
        }
    }

}
