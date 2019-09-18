define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    // ^2_3^
    var Controller = {
        // index方法
        index: function () {
            // 初始化表格参数配置
            Table.api.init();
            
            // 绑定事件_tab切换刷新表格数据
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                // tab页(first/second)
                var panel = $($(this).attr("href"));

                // 存在
                if (panel.size() > 0) {
                    Controller.table[panel.attr("id")].call(this);

                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }

                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });
            
            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
        },
        // table方法
        table: {
            // 第一个表格方法
            first: function () {
                // 表格1
                var table1 = $("#table1");

                // 表格配置
                table1.bootstrapTable({
                    url: 'example/multitable/table1',
                    // 工具栏
                    toolbar: '#toolbar1',
                    sortName: 'id',
                    search: false, //是否显示快速搜索框;
                    columns: [
                        [
                            // 如设置state值，则选中行
                            {field: 'state', checkbox: true, },
                            {field: 'id', title: 'ID'},
                            {field: 'url', title: __('Url'), formatter: Table.api.formatter.url},
                            {field: 'imagewidth', title: __('Imagewidth')},
                            {field: 'imageheight', title: __('Imageheight')},
                            {field: 'mimetype', title: __('Mimetype')},
                            // 操作列
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: table1,
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(table1);
            },
            // 第二个表格方法
            second: function () {
                // 表格2
                var table2 = $("#table2");

                table2.bootstrapTable({
                    url: 'example/multitable/table2',
                    extend: {
                        index_url: '',
                        add_url: '',
                        edit_url: '',
                        del_url: '',
                        multi_url: '',
                        table: '',
                    },
                    toolbar: '#toolbar2',
                    sortName: 'id',
                    search: false,
                    columns: [
                        [
                            {field: 'id', title: 'ID'},
                            {field: 'title', title: __('Title')},
                            {field: 'url', title: __('Url'), align: 'left', formatter: Table.api.formatter.url},
                            {field: 'ip', title: __('ip')},
                            {
                                field: 'createtime',
                                title: __('Createtime'),
                                formatter: Table.api.formatter.datetime,
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                sortable: true //允许排序操作;
                            },
                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table2);
            }
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
        }
    };

    return Controller;
});