define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    // ^2_3^
    var Controller = {
        // 首页
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/attachment/index',
                    add_url: 'general/attachment/add',
                    edit_url: 'general/attachment/edit',
                    del_url: 'general/attachment/del',
                    multi_url: 'general/attachment/multi',
                    table: 'attachment'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: 'id',
                columns: [
                    [
                        {field: 'state', checkbox: true,},
                        {field: 'id', title: __('Id')},
                        // addClass: "selectpage" 动态下拉框
                        {field: 'admin_id', title: __('Admin_id'), visible: false, addClass: "selectpage",
                            extend: "data-source='auth/admin/index' data-field='nickname'"},
                        {field: 'user_id', title: __('User_id'), visible: false, addClass: "selectpage",
                            extend: "data-source='user/user/index' data-field='nickname'"},
                        {field: 'url', title: __('Preview'), formatter: Controller.api.formatter.thumb, operate: false},
                        {field: 'url', title: __('Url'), formatter: Controller.api.formatter.url},
                        // sortable: true，可点击标题排序；
                        {field: 'imagewidth', title: __('Imagewidth'), sortable: true},
                        {field: 'imageheight', title: __('Imageheight'), sortable: true},
                        {field: 'imagetype', title: __('Imagetype'), formatter: Table.api.formatter.search},
                        {field: 'storage', title: __('Storage'), formatter: Table.api.formatter.search},
                        {field: 'filesize', title: __('Filesize'), operate: 'BETWEEN', sortable: true},
                        {field: 'mimetype', title: __('Mimetype'), formatter: Table.api.formatter.search},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ],
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

        },
        // 选择附件方法(弹窗列表)
        select: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/attachment/select',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: 'id',
                columns: [
                    [
                        {field: 'state', checkbox: true,},
                        {field: 'id', title: __('Id')},
                        {field: 'admin_id', title: __('Admin_id'), visible: false},
                        {field: 'user_id', title: __('User_id'), visible: false},
                        // 预览
                        {field: 'url', title: __('Preview'), formatter: Controller.api.formatter.thumb, operate: false},
                        {field: 'imagewidth', title: __('Imagewidth'), operate: false},
                        {field: 'imageheight', title: __('Imageheight'), operate: false},
                        // Mime类型
                        {
                            field: 'mimetype', title: __('Mimetype'), operate: 'LIKE %...%',
                            process: function (value, arg) {
                                return value.replace(/\*/g, '%');
                            }
                        },
                        {field: 'createtime', title: __('Createtime'), formatter: Table.api.formatter.datetime,
                            operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {
                            field: 'operate', title: __('Operate'), events: {
                                'click .btn-chooseone': function (e, value, row, index) {
                                    var multiple = Backend.api.query('multiple');
                                    multiple = multiple == 'true' ? true : false;
                                    Fast.api.close({url: row.url, multiple: false});
                                },
                            }, formatter: function () {
                                return '<a href="javascript:;" class="btn btn-danger btn-chooseone btn-xs">' +
                                    '<i class="fa fa-check"></i> ' + __('Choose') + '</a>';
                            }
                        }
                    ]
                ]
            });

            // 选中多个
            $(document).on("click", ".btn-choose-multi", function () {
                var urlArr = new Array();
                // 获取选中状态
                $.each(table.bootstrapTable("getAllSelections"), function (i, j) {
                    urlArr.push(j.url);
                });

                var multiple = Backend.api.query('multiple');
                multiple = multiple == 'true' ? true : false;
                Fast.api.close({url: urlArr.join(","), multiple: true});
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            // 为表单绑定事件
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            // 格式化
            formatter: {
                // 预览图
                thumb: function (value, row, index) {
                    if (row.mimetype.indexOf("image") > -1) {
                        // 又拍云存储处理
                        var style = row.storage == 'upyun' ? '!/fwfh/120x90' : '';
                        return '<a href="' + row.fullurl + '" target="_blank">' +
                            '<img src="' + row.fullurl + style + '" alt="" style="max-height:90px;max-width:120px">' +
                            '</a>';

                    } else {
                        // 默认图像
                        return
                            '<a href="' + row.fullurl + '" target="_blank">' +
                                '<img src="https://tool.fastadmin.net/icon/' + row.imagetype + '.png" alt="">' +
                            '</a>';
                    }
                },
                // 物理路径
                url: function (value, row, index) {
                    return '<a href="' + row.fullurl + '" target="_blank" class="label bg-green">' + value + '</a>';
                },
            }
        }

    };
    return Controller;
});