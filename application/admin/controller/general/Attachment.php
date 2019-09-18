<?php

namespace app\admin\controller\general;

use app\common\controller\Backend;

/**
 * 附件管理
 *
 * @icon fa fa-circle-o
 * @remark 主要用于管理上传到又拍云的数据或上传至本服务的上传数据
 * @author ^2_3^
 */
class Attachment extends Backend
{

    /**
     * @var \app\common\model\Attachment
     */
    protected $model = null;

    /**
     * 初始化
     * @author ^2_3^
     */
    public function _initialize()
    {
        parent::_initialize();

        // 附件模型
        $this->model = model('Attachment');
    }

    /**
     * 查看
     * @author ^2_3^
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);

        if ($this->request->isAjax()) {
            $mimetypeQuery = [];

            // 过滤字段(filename:filevalue)
            $filter = $this->request->request('filter');
            $filterArr = (array)json_decode($filter, TRUE);

            if (
                isset($filterArr['mimetype']) &&
                stripos($filterArr['mimetype'], ',') !== false
            ) {
                //// 清空filter参数mimetype值(用空值数组合并覆盖)
                // BUG(2019-07-06发现)
                // mimetype参数为空后，在下面(buildparam())创建查询条件时被设置为`mimetype` = ''，将查不到数据；
//                $this->request->get(
//                    ['filter' => json_encode(array_merge($filterArr, ['mimetype' => '']))]
//                );
                //// BUG修复(2019-07-06) 去除mimetype参数，覆盖filter参数
                $filter_get = $filterArr;
                unset($filter_get['mimetype']);
                $this->request->get(
                    ['filter' => json_encode($filter_get)]
                );

                $mimetypeQuery = function ($query) use ($filterArr) {
                    $mimetypeArr = explode(',', $filterArr['mimetype']);
                    foreach ($mimetypeArr as $index => $item) {
                        $query->whereOr('mimetype', 'like', '%' . $item . '%');
                    }

                };

            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $this->model
                ->where($mimetypeQuery)
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($mimetypeQuery)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            // 处理图片地址路径
            $cdnurl = preg_replace("/\/(\w+)\.php$/i", '', $this->request->root());
            foreach ($list as $k => &$v) {
                $v['fullurl'] = ($v['storage'] == 'local' ?
                        $cdnurl : $this->view->config['upload']['cdnurl']) . $v['url'];
            }
            unset($v);

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 选择附件
     * @author ^2_3^
     */
    public function select()
    {
        if ($this->request->isAjax()) {
            return $this->index();
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     * @author ^2_3^
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $this->error();
        }
        return $this->view->fetch();
    }

    /**
     * 删除附件
     * @param array $ids
     * @author ^2_3^
     */
    public function del($ids = "")
    {
        if ($ids) {
            //// 添加 行为闭包处理
            \think\Hook::add('upload_delete', function ($params) {
                $attachmentFile = ROOT_PATH . '/public' . $params['url'];
                if (is_file($attachmentFile)) {
                    @unlink($attachmentFile); //删除文件;
                }
            });

            // 查询插件模型
            $attachmentlist = $this->model->where('id', 'in', $ids)->select();
            foreach ($attachmentlist as $attachment) {
                // 监听upload_delete删除行为
                \think\Hook::listen("upload_delete", $attachment);

                // 删除插件记录
                $attachment->delete();
            }
            $this->success();
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

}
