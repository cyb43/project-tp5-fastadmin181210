<?php

namespace app\admin\library\traits;

/**
 * Trait Backend
 * @package app\admin\library\traits
 * @author ^2_3^
 */
trait Backend
{

    /**
     * 查看
     * @author ^2_3^[完成]
     */
    public function index()
    {
        // 设置过滤方法(strip_tags - 从字符串中去除 HTML 和 PHP 标记)
        $this->request->filter(['strip_tags']);

        // 是否为 Ajax 请求
        if ($this->request->isAjax()) {
            // 如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 回收站
     * @author ^2_3^
     */
    public function recyclebin()
    {
        // 设置过滤方法
        $this->request->filter(['strip_tags']);

        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->onlyTrashed() //只返回软删除数据；
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->onlyTrashed()
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     * @author ^2_3^
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            if ($params) {
                // 数据限制
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }

                try {
                    // 是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validate($validate);
                    }

                    // 过滤post数组中的非数据表字段数据
                    $result = $this->model->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();

                    } else {
                        $this->error($this->model->getError());
                    }

                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());

                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     * @author ^2_3^[完成]
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        //// 数据限制
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }

        if ($this->request->isPost()) {
            // 编辑数据
            $params = $this->request->post("row/a");

            if ($params) {
                try {
                    // 是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }

                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();

                    } else {
                        $this->error($row->getError());
                    }

                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());

                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        // 显示编辑页面
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 删除
     * @author ^2_3^
     */
    public function del($ids = "")
    {
        if ($ids) {
            $pk = $this->model->getPk();

            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
            }

            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            foreach ($list as $k => $v) {
                $count += $v->delete();
            }

            if ($count) {
                $this->success();

            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 真实删除
     * @author ^2_3^
     */
    public function destroy($ids = "")
    {
        $pk = $this->model->getPk();

        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
        }

        if ($ids) {
            $this->model->where($pk, 'in', $ids);
        }
        $count = 0;
        $list = $this->model->onlyTrashed()->select(); //只返回软删除数据；

        //// 真实删除
        foreach ($list as $k => $v) {
            $count += $v->delete(true);
        }

        if ($count) {
            $this->success();

        } else {
            $this->error(__('No rows were deleted'));
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 还原
     * @author ^2_3^
     */
    public function restore($ids = "")
    {
        $pk = $this->model->getPk();

        // 限制数据
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }

        if ($ids) {
            $this->model->where($pk, 'in', $ids);
        }
        $count = 0;
        $list = $this->model->onlyTrashed()->select();
        foreach ($list as $index => $item) {
            $count += $item->restore(); //恢复数据；
        }

        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }

    /**
     * 批量更新
     * @author ^2_3^
     */
    public function multi($ids = "")
    {
        $ids = $ids ? $ids : $this->request->param("ids");

        if ($ids) {
            if ($this->request->has('params')) {
                // 解析变量
                parse_str($this->request->post("params"), $values);

                if (!$this->auth->isSuperAdmin()) {
                    $values = array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
                }

                if ($values) {
                    // 数据限制
                    $adminIds = $this->getDataLimitAdminIds();
                    if (is_array($adminIds)) {
                        $this->model->where($this->dataLimitField, 'in', $adminIds);
                    }

                    $count = 0;
                    $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
                    foreach ($list as $index => $item) {
                        $count += $item->allowField(true)->isUpdate(true)->save($values); //显式指定更新数据操作；
                    }

                    if ($count) {
                        $this->success();

                    } else {
                        $this->error(__('No rows were updated'));
                    }

                } else {
                    $this->error(__('You have no permission'));
                }
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 导入
     * @author ^2_3^
     */
    protected function import()
    {
        // 导入文件
        $file = $this->request->request('file');
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }

        // 文件验证
        $filePath = ROOT_PATH . DS . 'public' . DS . $file;
        if (!is_file($filePath)) {
            $this->error(__('No results were found'));
        }

        //// Excel2007文件读取
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            //// 切换Excel5
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                /// 切换CSV
                $PHPReader = new \PHPExcel_Reader_CSV();
                if (!$PHPReader->canRead($filePath)) {
                    $this->error(__('Unknown data format'));
                }
            }
        }

        // 导入文件首行类型,默认是注释,如果需要使用字段名称请使用name
        $importHeadType = isset($this->importHeadType) ? $this->importHeadType : 'comment';

        // 表名名称
        $table = $this->model->getQuery()->getTable();
        // 数据库名称
        $database = \think\Config::get('database.database');
        $fieldArr = [];

        //// 数据库表所有字段(字段名称、字段备注)
        $list = db()->query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$table, $database]);
        foreach ($list as $k => $v) {
            // 备注注释微首行
            if ($importHeadType == 'comment') {
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];

            } else {
                // 字段名称首行
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }

        //// 加载文件
        $PHPExcel = $PHPReader->load($filePath); //加载文件；
        $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表；
        // 列数
        $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号；
        // 行数
        $allRow = $currentSheet->getHighestRow(); //取得一共有多少行；
        $maxColumnNumber = \PHPExcel_Cell::columnIndexFromString($allColumn);
        for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
            for ($currentColumn = 0; $currentColumn < $maxColumnNumber; $currentColumn++) {
                $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                $fields[] = $val;
            }
        }

        $insert = [];
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            //// 一行数据
            $values = [];
            for ($currentColumn = 0; $currentColumn < $maxColumnNumber; $currentColumn++) {
                $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                $values[] = is_null($val) ? '' : $val;
            }

            $row = [];
            $temp = array_combine($fields, $values); //合并字段名称和字段数值成为新数组；
            foreach ($temp as $k => $v) {
                if (isset($fieldArr[$k]) && $k !== '') {
                    $row[$fieldArr[$k]] = $v;
                }
            }

            if ($row) {
                $insert[] = $row;
            }
        }

        if (!$insert) {
            $this->error(__('No rows were updated'));
        }

        //// 保存数据
        try {
            $this->model->saveAll($insert);

        } catch (\think\exception\PDOException $exception) {
            $this->error($exception->getMessage());

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success();
    }

}
