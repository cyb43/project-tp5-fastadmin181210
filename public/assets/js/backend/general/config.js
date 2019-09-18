define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    //^2_3^
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/config/index',
                    add_url: 'general/config/add',
                    edit_url: 'general/config/edit',
                    del_url: 'general/config/del',
                    multi_url: 'general/config/multi',
                    table: 'config',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                        {field: 'intro', title: __('Intro')},
                        {field: 'group', title: __('Group')},
                        {field: 'type', title: __('Type')},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            $("form.edit-form").data("validator-options", {
                display: function (elem) {
                    return $(elem).closest('tr').find("td:first").text();
                }
            });
            Form.api.bindevent($("form.edit-form"));

            // 不可见的元素不验证
            $("form#add-form").data("validator-options", {ignore: ':hidden'});

            // 为表单绑定事件，将自动绑定上传/富文本/下拉框/selectpage/表单验证等功能，FastAdmin中最常用的方法；
            // Form.api.bindevent(form, success, error, submit);
            Form.api.bindevent($("form#add-form"), null, function (ret) {
                location.reload();
            });

            //// 元素显示/隐藏切换
            // 切换显示隐藏变量字典列表
            $(document).on("change", "form#add-form select[name='row[type]']", function (e) {
                $("#add-content-container").toggleClass("hide",
                    ['select', 'selects', 'checkbox', 'radio'].indexOf($(this).val()) > -1 ? false : true);
            });

            // 添加向发件人发送测试邮件按钮和方法
            $('input[name="row[mail_from]"]').parent().next().append(
                '<a class="btn btn-info testmail">' + __('Send a test message') + '</a>');
            $(document).on("click", ".testmail", function () {
                var that = this;

                Layer.prompt({title: __('Please input your email'), formType: 0}, function (value, index) {
                    Backend.api.ajax({
                        url: "general/config/emailtest?receiver=" + value,
                        data: $(that).closest("form").serialize()
                    });
                });

            });

            // 删除配置选项
            /*
            <!-- 删除配置选项 -->
            <a href="javascript:;" class="btn-delcfg text-muted" data-name="{$item.name}">
                <i class="fa fa-times"></i>
            </a>
            */
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
            }
        }
    };
    return Controller;
});