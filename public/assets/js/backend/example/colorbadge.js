define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    // ^2_3^
    var Controller = {

        // 首页方法
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'example/colorbadge/index',
                    add_url: '',
                    edit_url: '',
                    del_url: 'example/colorbadge/del',
                    multi_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                // 列配置
                columns: [
                    [
                        // state如果设置，则选中
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID'},
                        {field: 'title', title: __('Title')},
                        {field: 'ip', title: __('IP')},
                        {field: 'createtime', title: __('Create time'), formatter: Table.api.formatter.datetime,
                            operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        // 操作列
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                // 当所有数据被加载时触发
                onLoadSuccess: function (data) {
                    //// 设置彩色小角标
                    // 在表格第一次加载成功后,刷新左侧菜单栏彩色小角标,支持一次渲染多个；
                    // 如果需要在进入后台即显示左侧的彩色小角标,请使用服务端渲染方式,详情修改application/admin/controller/Index.php
                    Backend.api.sidebar({
                        'example/colorbadge': data.total //记录条数
                    });

                    // 提示
                    Toastr.info("左侧角标已经刷新成功");
                }
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});