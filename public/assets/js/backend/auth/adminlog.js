define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    // ^2_3^
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'auth/adminlog/index',
                    add_url: '',
                    edit_url: '',
                    del_url: 'auth/adminlog/del',
                    multi_url: 'auth/adminlog/multi',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID', operate: false},
                        // 点击搜索
                        {field: 'username', title: __('Username'), formatter: Table.api.formatter.search},
                        // 模糊搜索
                        {field: 'title', title: __('Title'), operate: 'LIKE %...%', placeholder: '模糊搜索'},
                        // 以链接渲染
                        {field: 'url', title: __('Url'), align: 'left', formatter: Table.api.formatter.url},
                        {field: 'ip', title: __('IP'), events: Table.api.events.ip,
                            formatter: Table.api.formatter.search},
                        // 自定义渲染
                        {field: 'browser', title: __('Browser'), operate: false,
                            formatter: Controller.api.formatter.browser},
                        // 时间范围搜索
                        {field: 'createtime', title: __('Create time'), formatter: Table.api.formatter.datetime,
                            operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        // 操作列
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            // 自定义按钮(添加详情按钮)
                            buttons: [{
                                    name: 'detail',
                                    text: __('Detail'),
                                    icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'auth/adminlog/detail'
                                }],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            // 自定义渲染，自定义用户代理渲染输出
            formatter: {
                browser: function (value, row, index) {
                    return '<a class="btn btn-xs btn-browser">' + row.useragent.split(" ")[0] + '</a>';
                },
            },
        }
    };
    return Controller;
});