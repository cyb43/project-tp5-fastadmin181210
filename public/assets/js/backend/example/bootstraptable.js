define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'],
    function ($, undefined, Backend, Table, Form, Template) {

    // ^2_3^
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'example/bootstraptable/index',
                    add_url: '',
                    edit_url: '',
                    del_url: 'example/bootstraptable/del',
                    multi_url: '',

                    // jsonp请求
                    jsonp_url : 'example/bootstraptable/req4jsonp',
                }
            });

            var table = $("#table");

            // 普通搜索_提交搜索前
            table.on('common-search.bs.table', function (event, table, query) {
                // 这里可以获取到普通搜索表单中字段的查询条件
                console.log('普通搜索_start');
                console.log(query);
                console.log('普通搜索_end');
            });

            // 普通搜索_渲染后
            table.on('post-common-search.bs.table', function (event, table) {
                var form = $("form", table.$commonsearch);

                // 标题搜索(动态列表)
                $("input[name='title']", form).addClass("selectpage").
                    data("source", "auth/adminlog/selectpage").
                    data("primaryKey", "title").
                    data("field", "title").
                    data("orderBy", "id desc");
                // 管理员
                $("input[name='username']", form).addClass("selectpage").
                    data("source", "auth/admin/index").
                    data("primaryKey", "username").
                    data("field", "username").
                    data("orderBy", "id desc");

                // 绑定事件
                Form.events.cxselect(form);
                Form.events.selectpage(form);
            });

            // 在表格内容渲染完成后回调的事件
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                console.log('表格渲染完成_start');
                console.log(e, settings, json, xhr);
                console.log('表格渲染完成_end');
            });

            // 当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                // 这里可以获取从服务端获取的JSON数据
                console.log('表格数据加载完成_start');
                console.log(data);
                console.log('表格数据加载完成_end');

                // 这里我们手动设置底部的值
                $("#money").text(data.extend.money);
                $("#price").text(data.extend.price);
            });

            // 初始化表格
            // 这里使用的是Bootstrap-table插件渲染表格
            // 相关文档：http://bootstrap-table.wenzhixin.net.cn/zh-cn/documentation/
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        //// 更多配置参数可参考http://bootstrap-table.wenzhixin.net.cn/zh-cn/documentation/#c
                        // 该列为复选框字段,如果后台返回state值将会默认选中
                        {field: 'state', checkbox: true,},
                        // [id列]sortable为是否排序,operate为搜索时的操作符,visible表示是否可见
                        {field: 'id', title: 'ID', sortable: true, operate: false},
                        // [管理员ID列]默认隐藏该列
                        {field: 'admin_id', title: __('管理员'), operate: false},
                        // [管理员用户名]直接响应搜索(点击列值)
                        {field: 'username', title: __('管理员'), formatter: Table.api.formatter.search},
                        // [标题]模糊搜索
                        {field: 'title', title: __('Title'), operate: 'LIKE %...%', placeholder: '模糊搜索，*表示任意字符'},
                        // [url路径]通过Ajax渲染searchList，也可以使用JSON数据
                        {
                            field: 'url',
                            title: __('Url'),
                            align: 'left',
                            // 普通搜索url列表
                            searchList: $.getJSON('example/bootstraptable/searchlist?search=a&field=row[user_id]'),
                            // 自定义渲染数值
                            formatter: Controller.api.formatter.url
                        },
                        // [IP列]点击IP时同时执行搜索此IP
                        {
                            field: 'ip',
                            title: __('IP'),
                            // 绑定自定义事件处理
                            events: Controller.api.events.ip,
                            // 自定义渲染列
                            formatter: Controller.api.formatter.ip
                        },
                        // [切换]自定义栏位,custom是不存在的字段
                        {field: 'custom', title: __('切换'), operate: false, formatter: Controller.api.formatter.custom},
                        // [browser列]browser是一个不存在的字段
                        // 通过formatter来渲染数据,同时为它添加上事件
                        {
                            field: 'browser',
                            title: __('Browser'),
                            operate: false,
                            // 自定义绑定事件
                            events: Controller.api.events.browser,
                            // 自定义渲染列
                            formatter: Controller.api.formatter.browser
                        },
                        // [联动搜索列]_分组/管理员关联搜索
                        {
                            field: 'admin_id',
                            title: __('联动搜索'),
                            searchList: function (column) {
                                // 返回模版
                                return Template('categorytpl', {});
                            }
                        },
                        // [更新时间]_启用时间段搜索
                        {
                            field: 'createtime',
                            title: __('Update time'),
                            sortable: true,
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange'
                        },
                        // 操作栏,默认有编辑、删除或排序按钮,可自定义配置buttons来扩展按钮
                        {
                            field: 'operate',
                            width: "120px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            // 自定义按钮扩展
                            buttons: [
                                {
                                    //// 详情按钮
                                    name: 'detail',
                                    title: __('弹出窗口打开'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-list',
                                    // 触发地址
                                    url: 'example/bootstraptable/detail',
                                    // btn-callback按钮回调函数
                                    callback: function (data) {
                                        Layer.alert("^2_3^接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                                {
                                    //// ajax请求详情
                                    name: 'ajax',
                                    title: __('发送Ajax'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    icon: 'fa fa-magic',
                                    url: 'example/bootstraptable/detail',
                                    success: function (data, ret) {
                                        Layer.alert(ret.msg + ",返回数据：" + JSON.stringify(data));

                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },
                                {
                                    //// 新选项卡中打开
                                    name: 'addtabs',
                                    title: __('新选项卡中打开'),
                                    classname: 'btn btn-xs btn-warning btn-addtabs', //以btn-addtabs指明；
                                    icon: 'fa fa-folder-o',
                                    url: 'example/bootstraptable/detail'
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        },
                    ],
                ],
                //
                //// 更多配置参数可参考http://bootstrap-table.wenzhixin.net.cn/zh-cn/documentation/#t
                //// 亦可以参考require-table.js中defaults的配置
                // 禁用默认搜索
                search: false,
                // 启用普通表单搜索
                commonSearch: true,
                // 可以控制是否默认显示搜索单表,false则隐藏,默认为false
                searchFormVisible: true,

                //// 查询字串(可以追加搜索条件)
                queryParams: function (params) {
                    // 这里可以追加搜索条件
                    var filter = JSON.parse(params.filter);
                    var op = JSON.parse(params.op);

                    //// 这里可以动态赋值，比如从URL中获取admin_id的值，filter.admin_id=Fast.api.query('admin_id');
                    //// BUG：如果没有添加判断，则admin-id永远为1，不会随下拉框选项改变而改变。
                    // filter.admin_id = 1;
                    // op.admin_id = "=";
                    // params.filter = JSON.stringify(filter);
                    // params.op = JSON.stringify(op);
                    if( filter.admin_id == null || filter.admin_id == undefined ) {
                        filter.admin_id = 1;
                        op.admin_id = "=";
                        params.filter = JSON.stringify(filter);
                        params.op = JSON.stringify(op);
                    }

                    return params;
                },
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            //// 监听下拉列表改变的事件
            // BUG：select[name=admin]没有，应该改为select[name=admin_id]；
            // $(document).on('change', 'select[name=admin]', function () {
            $(document).on('change', 'select[name=admin_id]', function () {
                // console.log('下拉列表', $(this).val());
            });

            // 工具栏_自定义搜索按钮_指定搜索条件
            $(document).on("click", ".btn-singlesearch", function () {
                // bootstraptable配置选项
                var options = table.bootstrapTable('getOptions');

                // 搜索字串
                var queryParams = options.queryParams;

                options.pageNumber = 1;
                options.queryParams = function (params) {
                    // 这一行必须要存在,否则在点击下一页时会丢失搜索栏数据
                    params = queryParams(params);

                    //如果希望追加搜索条件,可使用
                    var filter = params.filter ? JSON.parse(params.filter) : {};
                    var op = params.op ? JSON.parse(params.op) : {};
                    filter.url = 'login';
                    op.url = 'like';
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);

                    //如果希望忽略搜索栏搜索条件,可使用
                    //params.filter = JSON.stringify({url: 'login'});
                    //params.op = JSON.stringify({url: 'like'});
                    return params;
                };

                table.bootstrapTable('refresh', {});
                Toastr.info("当前执行的是自定义搜索,搜索URL中包含login的数据");
                return false;
            });

            // 工具栏_获取选中项
            $(document).on("click", ".btn-selected", function () {
                Layer.alert(JSON.stringify(table.bootstrapTable('getSelections')));
            });

            // 工具栏_启动和暂停按钮
            $(document).on("click", ".btn-start,.btn-pause", function () {
                // 在table外不可以使用添加.btn-change的方法
                // 只能自己调用Table.api.multi实现
                // 如果操作全部则ids可以置为空
                var ids = Table.api.selectedids(table);
                console.log('启动/停止(ids)', ids);
                Table.api.multi("changestatus", ids.join(","), table, this);
            });

            //// 请求JSONP返回格式
            $(document).on('click', '.btn-jsonp', function () {
                // jsonp请求
                $.ajax({
                    type : "post",
                    url : $.fn.bootstrapTable.defaults.extend.jsonp_url,
                    dataType : "jsonp", //预期服务器返回的数据类型;
                    jsonp: "callback", //传递给请求处理程序或页面的，用以获得jsonp回调函数名的参数名(默认为:callback);
                    //jsonpCallback:"success_jsonpCallback", //自定义的jsonp回调函数名称，默认为jQuery自动生成的随机函数名;
                    // 请求成功执行此函数，相当于jsonp回调函数, json参数相当于回调函数的参数;
                    success : function(json){
                        //alert('success');

                        // json为返回的数据;
                        console.log('success');
                        console.log(json);
                    },
                    error:function(){
                        alert('fail');
                    }
                });
            });

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        // 详情操作
        detail: function () {
            // 详情页面_回调按钮
            $(document).on('click', '.btn-callback', function () {
                // 页面回调
                Fast.api.close($("input[name=callback]").val());
            });
        },
        api: {
            // 绑定事件
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            //渲染的方法
            formatter: {
                // url路径列渲染
                url: function (value, row, index) {
                    return '<div class="input-group input-group-sm" style="width:250px;">' +
                                '<input type="text" class="form-control input-sm" value="' + value + '">' +
                                '<span class="input-group-btn input-group-sm">' +
                                    '<a href="' + value + '" target="_blank" class="btn btn-default btn-sm">' +
                                        '<i class="fa fa-link"></i>' +
                                    '</a>' +
                                '</span>' +
                            '</div>';
                },
                // [ip列]_渲染
                ip: function (value, row, index) {
                    return '<a class="btn btn-xs btn-ip bg-success"><i class="fa fa-map-marker"></i> ' + value + '</a>';
                },
                // [browser列]_渲染列数据
                browser: function (value, row, index) {
                    // 这里我们直接使用row的数据(因为没有对应字段，故需自定义数据)
                    return '<a class="btn btn-xs btn-browser">' + row.useragent.split(" ")[0] + '</a>';
                },
                // [切换列]_渲染自定义列
                custom: function (value, row, index) {
                    // 添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" ' +
                            'data-url="example/bootstraptable/change" ' +
                            'data-id="' + row.id + '">' +
                                '<i class="fa ' + (row.title == '' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i>'+
                            '</a>';
                },
            },
            // 绑定事件的方法
            events: {
                // [ip列]_绑定自定义事件
                ip: {
                    //格式为：方法名+空格+DOM元素
                    'click .btn-ip': function (e, value, row, index) {
                        // 停止事件传播
                        e.stopPropagation();
                        console.log('停止事件传播');

                        var container = $("#table").data("bootstrap.table").$container;
                        var options = $("#table").bootstrapTable('getOptions');

                        // 这里我们手动将数据填充到表单然后提交
                        $("form.form-commonsearch [name='ip']", container).val(value);
                        $("form.form-commonsearch", container).trigger('submit');
                        Toastr.info("执行了自定义IP搜索操作");
                    }
                },
                // [browser列]_绑定自定义事件
                browser: {
                    'click .btn-browser': function (e, value, row, index) {
                        // 阻止事件传播
                        e.stopPropagation();

                        // 显示layer弹层
                        Layer.alert("该行数据为: <code>" + JSON.stringify(row) + "</code>");
                    }
                },
            }
        }
    };

    return Controller;
});