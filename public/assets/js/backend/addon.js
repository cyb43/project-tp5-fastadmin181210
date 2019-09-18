define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'],
    function ($, undefined, Backend, Table, Form, Template) {

    // ^2_3^
    var Controller = {
        // index方法
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    // "api_url": "https://api.fastadmin.net"
                    // https://api.fastadmin.net/addon/index
                    index_url: Config.fastadmin.api_url + '/addon/index',
                    add_url: '',
                    edit_url: '',
                    del_url: '',
                    multi_url: ''
                }
            });

            var table = $("#table");

            //// 当所有数据被加载时触发
            table.on('load-success.bs.table', function (e, json) {
                // tab分类
                // "category": [{
                //     "id": "19",
                //     "name": "完整应用"
                // }, {
                //     "id": "14",
                //     "name": "开发测试"
                // }, {
                //     "id": "16",
                //     "name": "编辑器"
                // }, {
                //     "id": "17",
                //     "name": "云储存"
                // }, {
                //     "id": "18",
                //     "name": "短信验证"
                // }, {
                //     "id": "15",
                //     "name": "接口整合"
                // }, {
                //     "id": "21",
                //     "name": "辅助增强"
                // }]
                if (json && typeof json.category != 'undefined' && $(".nav-category li").size() == 2) {
                    $.each(json.category, function (i, j) {
                        // 插入元素
                        $("<li><a href='javascript:;' data-id='" + j.id + "'>" + j.name + "</a></li>").
                            insertBefore($(".nav-category li:last"));
                    });
                }
            });

            //// 当<tbody></tbody>中的内容被加载完后或者在你所用的DOM中有定义则被触发；
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                // 获取.bootstrap-table的div
                var parenttable = table.closest('.bootstrap-table');

                // 工具栏_搜索框
                var d = $(".fixed-table-toolbar", parenttable).find(".search input");
                d.off("keyup drop blur");
                d.on("keyup", function (e) {
                    if (e.keyCode == 13) {
                        var that = this;

                        //// 添加搜索数值进入搜索字串
                        // 表格配置
                        var options = table.bootstrapTable('getOptions');
                        // 搜索字串
                        var queryParams = options.queryParams;
                        options.pageNumber = 1;
                        options.queryParams = function (params) {
                            var params = queryParams(params);
                            params.search = $(that).val(); //添加搜索数值;
                            return params;
                        };

                        // 刷新表格
                        table.bootstrapTable('refresh', {});
                    }
                });
            });

            //// 添加函数方法
            Template.helper("Moment", Moment);
            Template.helper("addons", Config['addons']); //本地插件配置;
            // "addons": {
            //     "command": {
            //         "name": "command",
            //             "title": "\u5728\u7ebf\u547d\u4ee4",
            //             "intro": "\u53ef\u5728\u7ebf\u6267\u884cFastAdmin\u7684\u547d\u4ee4\u884c\u76f8\u5173\u547d\u4ee4",
            //             "author": "Karson",
            //             "website": "http:\/\/www.fastadmin.net",
            //             "version": "1.0.5",
            //             "state": "1",
            //             "url": "\/addons\/command",
            //             "config": 0
            //     },
            //     "database": {
            //         "name": "database",
            //             "title": "\u6570\u636e\u5e93\u7ba1\u7406",
            //             "intro": "\u6570\u636e\u5e93\u7ba1\u7406\u63d2\u4ef6",
            //             "author": "Karson",
            //             "website": "http:\/\/www.fastadmin.net",
            //             "version": "1.0.3",
            //             "state": "1",
            //             "url": "\/addons\/database",
            //             "config": 1
            //     },
            //     "example": {
            //         "name": "example",
            //             "title": "\u5f00\u53d1\u793a\u4f8b",
            //             "intro": "FastAdmin\u591a\u4e2a\u5f00\u53d1\u793a\u4f8b",
            //             "author": "Karson",
            //             "website": "http:\/\/www.fastadmin.net",
            //             "version": "1.0.7",
            //             "state": "1",
            //             "url": "\/addons\/example",
            //             "config": 1
            //     },
            //     "summernote": {
            //         "name": "summernote",
            //             "title": "Summernote\u5bcc\u6587\u672c\u7f16\u8f91\u5668",
            //             "intro": "\u4fee\u6539\u540e\u53f0\u9ed8\u8ba4\u7f16\u8f91\u5668\u4e3aSummernote",
            //             "author": "Karson",
            //             "website": "http:\/\/www.fastadmin.net",
            //             "version": "1.0.3",
            //             "state": "1",
            //             "url": "\/addons\/summernote",
            //             "config": 0
            //     }
            // }

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        // ID
                        {field: 'id', title: 'ID', operate: false, visible: false},
                        // 前台
                        {
                            field: 'home',
                            title: __('Index'),
                            width: '50px',
                            formatter: Controller.api.formatter.home //自定义渲染;
                        },
                        // 名称
                        {field: 'name', title: __('Name'), operate: false, visible: false, width: '120px'},
                        // 插件名称
                        {
                            field: 'title',
                            title: __('Title'),
                            operate: 'LIKE',
                            align: 'left',
                            formatter: Controller.api.formatter.title
                        },
                        // 介绍
                        {field: 'intro', title: __('Intro'), operate: 'LIKE', align: 'left', class: 'visible-lg'},
                        // 作者
                        {
                            field: 'author',
                            title: __('Author'),
                            operate: 'LIKE',
                            width: '100px',
                            formatter: Controller.api.formatter.author
                        },
                        // 价格
                        {
                            field: 'price',
                            title: __('Price'),
                            operate: 'LIKE',
                            width: '100px',
                            align: 'center',
                            formatter: Controller.api.formatter.price
                        },
                        // 下载
                        {
                            field: 'downloads',
                            title: __('Downloads'),
                            operate: 'LIKE',
                            width: '80px',
                            align: 'center',
                            formatter: Controller.api.formatter.downloads
                        },
                        // 版本
                        {
                            field: 'version',
                            title: __('Version'),
                            operate: 'LIKE',
                            width: '80px',
                            align: 'center',
                            formatter: Controller.api.formatter.version
                        },
                        // 状态
                        {
                            field: 'toggle',
                            title: __('Status'),
                            width: '80px',
                            formatter: Controller.api.formatter.toggle
                        },
                        // 操作列
                        {
                            field: 'id',
                            title: __('Operate'),
                            align: 'center',
                            table: table,
                            formatter: Controller.api.formatter.operate,
                            align: 'right'
                        },
                    ]
                ],
                //// 在加载服务器发送来的数据之前，处理数据的格式，参数res表示the response data（获取的数据）；
                responseHandler: function (res) {
                    // 为插件数据添加(已安装)插件配置信息
                    $.each(res.rows, function (i, j) {
                        j.addon = typeof Config.addons[j.name] != 'undefined' ? Config.addons[j.name] : null;
                    });
                    return res;
                },
                dataType: 'jsonp',
                templateView: false,
                clickToSelect: false,
                search: true,
                showColumns: false,
                showToggle: false,
                showExport: false,
                showSearch: false, //是否显示普通搜索按钮;
                commonSearch: true,
                searchFormVisible: true,
                searchFormTemplate: 'searchformtpl',
                pageSize: 12,
                pagination: false,
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 离线安装
            require(['upload'], function (Upload) {
                // 离线安装按钮
                Upload.api.plupload("#plupload-addon", function (data, ret) {
                    Config['addons'][data.addon.name] = data.addon;
                    Toastr.success(ret.msg);

                    // 启用插件
                    operate(data.addon.name, 'enable', false);
                });
            });

            // 查看插件首页
            $(document).on("click", ".btn-addonindex", function () {
                if ($(this).attr("href") == 'javascript:;') {
                    Layer.msg(__('Not installed tips'), {icon: 7});

                } else if ($(this).closest(".operate").find("a.btn-enable").size() > 0) {
                    Layer.msg(__('Not enabled tips'), {icon: 7});
                    return false;
                }
            });

            // 切换(全部／免费／付费／本地插件)
            $(document).on("click", ".btn-switch", function () {
                $(".btn-switch").removeClass("active");

                $(this).addClass("active");

                // 普通搜索模版 插件类型
                $("form.form-commonsearch input[name='type']").val($(this).data("type"));

                // 刷新表格,加载数据
                table.bootstrapTable('refresh', {url: $(this).data("url"), pageNumber: 1});

                return false;
            });

            // ^2_3^ tab分类
            $(document).on("click", ".nav-category li a", function () {
                //// 切换li状态
                $(".nav-category li").removeClass("active");
                $(this).parent().addClass("active");

                // 普通搜索 插件分类 赋值
                $("form.form-commonsearch input[name='category_id']").val($(this).data("id"));

                // 刷新表格
                table.bootstrapTable('refresh', {url: $(this).data("url"), pageNumber: 1});

                return false;
            });

            // 会员信息
            $(document).on("click", ".btn-userinfo", function () {
                var that = this;

                // 会员信息
                var userinfo = Controller.api.userinfo.get();
                if (!userinfo) {

                    // 显示登录弹窗
                    Layer.open({
                        content: Template("logintpl", {}),
                        area: ['430px', '350px'],
                        title: __('Login FastAdmin'),
                        resize: false,
                        btn: [__('Login'), __('Register')],
                        yes: function (index, layero) {
                            // 登录fastadmin官方
                            Fast.api.ajax({
                                url: Config.fastadmin.api_url + '/user/login',
                                dataType: 'jsonp',
                                data: {
                                    account: $("#inputAccount", layero).val(),
                                    password: $("#inputPassword", layero).val(),
                                    _method: 'POST'
                                }
                            }, function (data, ret) {
                                //// 回调函数,本地存储会员信息
                                Controller.api.userinfo.set(data);

                                // 关闭弹窗
                                Layer.closeAll();

                                // 显示alert信息
                                Layer.alert(ret.msg);

                            }, function (data, ret) {
                                // 请求失败
                                Layer.alert(ret.msg);
                            });
                        },
                        btn2: function () {
                            return false;
                        },
                        // 层完成显示后回调
                        success: function (layero, index) {
                            // 添加注册地址
                            $(".layui-layer-btn1", layero).prop("href", "http://www.fastadmin.net/user/register.html").
                                prop("target", "_blank");
                        }
                    });

                } else {
                    //// 存在会员信息
                    Fast.api.ajax({
                        url: Config.fastadmin.api_url + '/user/index',
                        dataType: 'jsonp',
                        data: {
                            user_id: userinfo.id,
                            token: userinfo.token,
                        }

                    }, function (data) {
                        Layer.open({
                            content: Template("userinfotpl", userinfo),
                            area: ['430px', '360px'],
                            title: __('Userinfo'),
                            resize: false,
                            btn: [__('Logout'), __('Cancel')],
                            yes: function () {
                                // 注销请求
                                Fast.api.ajax({
                                    url: Config.fastadmin.api_url + '/user/logout',
                                    dataType: 'jsonp',
                                    data: {uid: userinfo.id, token: userinfo.token}

                                }, function (data, ret) {
                                    Controller.api.userinfo.set(null);
                                    Layer.closeAll();
                                    Layer.alert(ret.msg);

                                }, function (data, ret) {
                                    Controller.api.userinfo.set(null);
                                    Layer.closeAll();
                                    Layer.alert(ret.msg);
                                });
                            }
                        });
                        return false;

                    }, function (data) {
                        Controller.api.userinfo.set(null);
                        $(that).trigger('click');
                        return false;
                    });

                }
            });

            // ^2_3^插件安装(插件名称/版本/是否强制覆盖)
            var install = function (name, version, force) {
                // 会员信息(存储本地)
                var userinfo = Controller.api.userinfo.get();
                var uid = userinfo ? userinfo.id : 0;
                var token = userinfo ? userinfo.token : '';

                // 请求插件安装
                Fast.api.ajax({
                    url: 'addon/install',
                    data: {
                        name: name,
                        force: force ? 1 : 0,
                        uid: uid,
                        token: token,
                        version: version,
                        faversion: Config.fastadmin.version
                    }

                }, function (data, ret) {
                    //// 成功回调
                    Layer.closeAll();
                    Config['addons'][data.addon.name] = ret.data.addon;

                    Layer.alert(__('Online installed tips'), {
                        btn: [__('OK')],
                        title: __('Warning'),
                        icon: 1
                    });

                    // 刷新按钮
                    $('.btn-refresh').trigger('click');

                    // 刷新菜单
                    Fast.api.refreshmenu();

                }, function (data, ret) {
                    // 如果是需要购买的插件则弹出二维码提示
                    if (ret && ret.code === -1) {
                        //扫码支付
                        Layer.open({
                            content: Template("paytpl", ret.data),
                            shade: 0.8,
                            area: ['800px', '600px'],
                            skin: 'layui-layer-msg layui-layer-pay',
                            title: false,
                            closeBtn: true,
                            btn: false,
                            resize: false,
                            end: function () {
                                Layer.alert(__('Pay tips'));
                            }
                        });

                    } else if (ret && ret.code === -2) {
                        // 如果登录已经超时,重新提醒登录
                        if (uid && uid != ret.data.uid) {
                            Controller.api.userinfo.set(null);

                            $(".operate[data-name='" + name + "'] .btn-install").trigger("click");
                            return;
                        }

                        top.Fast.api.open(ret.data.payurl, __('Pay now'), {
                            area: ["650px", "700px"],
                            end: function () {
                                top.Layer.alert(__('Pay tips'));
                            }
                        });

                    } else if (ret && ret.code === -3) {
                        // 插件目录发现影响全局的文件
                        Layer.open({
                            content: Template("conflicttpl", ret.data),
                            shade: 0.8,
                            area: ['800px', '600px'],
                            title: __('Warning'),
                            btn: [__('Continue install'), __('Cancel')],
                            end: function () {

                            },
                            yes: function () {
                                install(name, version, true);
                            }
                        });

                    } else {
                        Layer.alert(ret.msg);
                    }
                    return false;
                });
            };

            //// 插件卸载(插件名称, 是否强制覆盖)
            var uninstall = function (name, force) {
                // 卸载
                Fast.api.ajax({
                    url: 'addon/uninstall',
                    data: {name: name, force: force ? 1 : 0}

                }, function (data, ret) {
                    delete Config['addons'][name];

                    Layer.closeAll();

                    $('.btn-refresh').trigger('click');
                    Fast.api.refreshmenu();

                }, function (data, ret) {
                    if (ret && ret.code === -3) {
                        // 插件目录发现影响全局的文件
                        Layer.open({
                            content: Template("conflicttpl", ret.data),
                            shade: 0.8,
                            area: ['800px', '600px'],
                            title: __('Warning'),
                            btn: [__('Continue uninstall'), __('Cancel')],
                            end: function () {

                            },
                            yes: function () {
                                uninstall(name, true);
                            }
                        });

                    } else {
                        Layer.alert(ret.msg);
                    }
                    return false;
                });
            };

            //// ^2_3^操作 (插件名称, 动作(启用／禁用), 是否强制覆盖)
            var operate = function (name, action, force) {

                // ajax请求
                Fast.api.ajax({
                    url: 'addon/state',
                    data: {name: name, action: action, force: force ? 1 : 0}

                }, function (data, ret) {
                    // 当前插件
                    var addon = Config['addons'][name];

                    // 插件状态
                    addon.state = action === 'enable' ? 1 : 0;

                    // 层关闭
                    Layer.closeAll();

                    // 触发刷新按钮
                    $('.btn-refresh').trigger('click');

                    // 刷新菜单
                    Fast.api.refreshmenu();

                }, function (data, ret) {
                    if (ret && ret.code === -3) {
                        //插件目录发现影响全局的文件
                        Layer.open({
                            content: Template("conflicttpl", ret.data),
                            shade: 0.8,
                            area: ['800px', '600px'],
                            title: __('Warning'),
                            btn: [__('Continue operate'), __('Cancel')],
                            end: function () {

                            },
                            yes: function () {
                                operate(name, action, true);
                            }
                        });

                    } else {
                        Layer.alert(ret.msg);
                    }
                    return false;
                });
            };

            //// 插件更新
            var upgrade = function (name, version) {
                // 会员信息
                var userinfo = Controller.api.userinfo.get();
                var uid = userinfo ? userinfo.id : 0;
                var token = userinfo ? userinfo.token : '';

                // 更新
                Fast.api.ajax({
                    url: 'addon/upgrade',
                    data: {name: name, uid: uid, token: token, version: version, faversion: Config.fastadmin.version}

                }, function (data, ret) {
                    Config['addons'][name].version = version;

                    Layer.closeAll();

                    $('.btn-refresh').trigger('click');
                    Fast.api.refreshmenu();

                }, function (data, ret) {
                    Layer.alert(ret.msg);
                    return false;
                });
            };

            // 点击安装^2_3^
            $(document).on("click", ".btn-install", function () {
                var that = this;

                // 插件名称
                var name = $(this).closest(".operate").data("name");
                // 获取data-version属性,插件版本
                var version = $(this).data("version");

                // 用户名称
                var userinfo = Controller.api.userinfo.get();
                var uid = userinfo ? userinfo.id : 0;

                // 不是免费
                if ($(that).data("type") !== 'free') {
                    if (parseInt(uid) === 0) {
                        return Layer.alert(__('Not login tips'), {
                            title: __('Warning'),
                            btn: [__('Login now'), __('Continue install')],
                            yes: function (index, layero) {
                                $(".btn-userinfo").trigger("click");
                            },
                            btn2: function () {
                                install(name, version, false);
                            }
                        });
                    }
                }
                install(name, version, false);
            });

            // 点击卸载
            $(document).on("click", ".btn-uninstall", function () {
                // 插件名称
                var name = $(this).closest(".operate").data('name');

                // 启用状态
                if (Config['addons'][name].state == 1) {
                    Layer.alert(__('Please disable addon first'), {icon: 7});
                    return false;
                }

                // 确认
                Layer.confirm(__('Uninstall tips', Config['addons'][name].title), function () {
                    uninstall(name, false);
                });
            });

            // 点击配置
            $(document).on("click", ".btn-config", function () {
                // 插件名称
                var name = $(this).closest(".operate").data("name");

                // 配置
                Fast.api.open("addon/config?name=" + name, __('Setting'));
            });

            // 点击启用/禁用
            $(document).on("click", ".btn-enable,.btn-disable", function () {
                var name = $(this).data("name");
                var action = $(this).data("action");
                operate(name, action, false);
            });

            // 点击升级
            $(document).on("click", ".btn-upgrade", function () {
                // 插件名称
                var name = $(this).closest(".operate").data('name');

                // 启用状态
                if (Config['addons'][name].state == 1) {
                    Layer.alert(__('Please disable addon first'), {icon: 7});
                    return false;
                }

                var version = $(this).data("version");

                Layer.confirm(__('Upgrade tips', Config['addons'][name].title), function () {
                    upgrade(name, version);
                });
            });

            //// 安装选择多版本
            $(document).on("click", ".operate .btn-group .dropdown-toggle", function () {
                // 切换版本下拉菜单
                $(this).closest(".btn-group").toggleClass("dropup", $(document).height() - $(this).offset().top <= 200);
            });

            //// 插件截图(点击查看相册层)
            $(document).on("click", ".view-screenshots", function () {
                // 当前行
                var row = Table.api.getrowbyindex(table, parseInt($(this).data("index")));

                var data = [];
                $.each(row.screenshots, function (i, j) {
                    data.push({
                        "src": j
                    });
                });
                var json = {
                    "title": row.title,
                    "data": data
                };
                // 相册层
                top.Layer.photos(top.JSON.parse(JSON.stringify({photos: json})));
            });

        },
        add: function () {
            Controller.api.bindevent();
        },
        config: function () {
            Controller.api.bindevent();
        },
        api: {
            formatter: {
                // 插件名称列渲染
                title: function (value, row, index) {
                    var title = '<a class="title" href="' + row.url + '" data-toggle="tooltip" ' +
                                    'title="' + __('View addon home page') + '" target="_blank">' + value + '</a>';
                    // 存在屏幕截图
                    if (row.screenshots && row.screenshots.length > 0) {
                        title += ' <a href="javascript:;" data-index="' + index + '" ' +
                                    'class="view-screenshots text-success" title="' + __('View addon screenshots') +
                                    '" data-toggle="tooltip"><i class="fa fa-image"></i></a>';
                    }

                    return title;
                },
                // 操作列操作
                operate: function (value, row, index) {
                    return Template("operatetpl", {item: row, index: index});
                },
                // 状态列渲染
                toggle: function (value, row, index) {
                    if (!row.addon) {
                        return '';
                    }
                    return '<a href="javascript:;" data-toggle="tooltip" title="' + __('Click to toggle status') +
                            '" class="btn btn-toggle btn-' + (row.addon.state == 1 ? "disable" : "enable") + '" ' +
                            'data-action="' + (row.addon.state == 1 ? "disable" : "enable") + '" ' +
                            'data-name="' + row.name + '"><i class="fa ' +
                            (row.addon.state == 0 ? 'fa-toggle-on fa-rotate-180 text-gray' :
                                'fa-toggle-on text-success') + ' fa-2x"></i></a>';
                },
                // 作者列渲染
                author: function (value, row, index) {
                    return '<a href="https://wpa.qq.com/msgrd?v=3&uin=' + row.qq + '&site=fastadmin.net&menu=yes" ' +
                        'target="_blank" data-toggle="tooltip" title="' + __('Click to contact developer') +
                        '" class="text-primary">' + value + '</a>';
                },
                // 价格列渲染
                price: function (value, row, index) {
                    return parseFloat(value) == 0 ? '<span class="text-success">' + __('Free') + '</span>' :
                            '<span class="text-danger">￥' + value + '</span>';
                },
                // 下载列渲染
                downloads: function (value, row, index) {
                    return value;
                },
                // 版本列刷新
                version: function (value, row, index) {
                    return row.addon && row.addon.version != row.version ?
                        '<a href="' + row.url + '?version=' + row.version + '" target="_blank">' +
                            '<span class="releasetips text-primary" data-toggle="tooltip" ' +
                                'title="' + __('New version tips', row.version) + '">' + row.addon.version + '<i></i>' +
                            '</span></a>' : row.version;
                },
                // 前台列渲染
                home: function (value, row, index) {
                    return row.addon ?
                        '<a href="' + row.addon.url + '" data-toggle="tooltip" title="' + __('View addon index page') +
                            '" target="_blank"><i class="fa fa-home text-primary"></i>' +
                        '</a>' :
                        '<a href="javascript:;"><i class="fa fa-home text-gray"></i></a>';
                },
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            // (^2_3^)会员信息
            userinfo: {
                // 获取信息
                get: function () {
                    // 获取本地存储
                    var userinfo = localStorage.getItem("fastadmin_userinfo");
                    return userinfo ? JSON.parse(userinfo) : null;
                },
                // 设置信息
                set: function (data) {
                    if (data) {
                        localStorage.setItem("fastadmin_userinfo", JSON.stringify(data));

                    } else {
                        localStorage.removeItem("fastadmin_userinfo");
                    }
                }
            }
        }
    };

    return Controller;
});