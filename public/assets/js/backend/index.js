define(['jquery', 'bootstrap', 'backend', 'addtabs', 'adminlte', 'form'],
    function ($, undefined, Backend, undefined, AdminLTE, Form) {

    // ^2_3^
    var Controller = {
        // index方法
        index: function () {
            //// 双击菜单重新加载页面
            $(document).on("dblclick", ".sidebar-menu li > a", function (e) {
                $("#con_" + $(this).attr("addtabs") + " iframe").attr('src', function (i, val) {
                    return val;
                });
                e.stopPropagation(); //阻止事件传播;
            });

            //// 修复: 在移除窗口时下拉框不隐藏的BUG;
            $(window).on("blur", function () {
                $("[data-toggle='dropdown']").parent().removeClass("open");

                //// 边栏切换按钮
                if ($("body").hasClass("sidebar-open")) {
                    $(".sidebar-toggle").trigger("click");
                }
            });

            //// 快捷搜索
            // 搜索结果宽度设置
            $(".menuresult").width( $("form.sidebar-form > .input-group").width() );
            // 搜索结果
            var searchResult = $(".menuresult");
            // 菜单搜索
            $("form.sidebar-form").on("blur", "input[name=q]", function () {
                    //// 失去焦点
                searchResult.addClass("hide"); //隐藏搜索结果框;

            }).on("focus", "input[name=q]", function () {
                //// 获得焦点
                if ($("a", searchResult).size() > 0) {
                    searchResult.removeClass("hide"); //显示搜索结果框;
                }

            }).on("keyup", "input[name=q]", function () {
                //// 按键触发
                searchResult.html('');

                var val = $(this).val();
                var html = [];

                //// 过滤菜单形成结果框
                if (val != '') {
                    $("ul.sidebar-menu li a[addtabs]:not([href^='javascript:;'])").each(function () {
                        //// 过滤菜单
                        // indexOf 检索字符串。
                        if (
                            $("span:first", this).text().indexOf(val) > -1 ||
                            $(this).attr("py").indexOf(val) > -1 ||
                            $(this).attr("pinyin").indexOf(val) > -1
                        ) {
                            // 添加入搜索框
                            html.push('<a data-url="' + $(this).attr("href") + '" href="javascript:;">' +
                                $("span:first", this).text() + '</a>');

                            if (html.length >= 100) {
                                return false;
                            }
                        }
                    });
                }

                $(searchResult).append(html.join(""));
                if (html.length > 0) {
                    searchResult.removeClass("hide");

                } else {
                    searchResult.addClass("hide");
                }

            });

            //// 快捷搜索点击事件
            $("form.sidebar-form").on('mousedown click', '.menuresult a[data-url]', function () {
                // 添加tab菜单
                Backend.api.addtabs($(this).data("url"));
            });

            //// 读取FastAdmin的更新信息(后台信息铃铛)
            $.ajax({
                // https://api.fastadmin.net/news/index
                // url: Config.fastadmin.api_url + '/news/index',
                url: '/admin/index/news',
                type: 'post',
                dataType: 'jsonp',
                success: function (ret) {
                    // console.log( ret );

                    // 最新信息数量
                    $(".notifications-menu > a span").text(ret.new > 0 ? ret.new : '');

                    // 最新信息地址
                    $(".notifications-menu .footer a").attr("href", ret.url);

                    //// 添加数据
                    $.each(ret.newslist, function (i, j) {
                        var item = '<li>' +
                                        '<a href="' + j.url + '" target="_blank"><i class="' + j.icon + '">' +
                                            '</i> ' + j.title +
                                        '</a>' +
                                    '</li>';
                        $(item).appendTo($(".notifications-menu ul.menu"));
                    });
                }
            });

            //// 读取首次登录推荐插件列表
            if (localStorage.getItem("fastep") == "installed") {
                $.ajax({
                    // https://api.fastadmin.net/addon/recommend
                    url: Config.fastadmin.api_url + '/addon/recommend',
                    type: 'post',
                    dataType: 'jsonp',
                    success: function (ret) {
                        require(['template'], function (Template) {

                            //// 安装插件
                            var install = function (name, title) {
                                // ajax请求
                                Fast.api.ajax({
                                    url: 'addon/install',
                                    data: {name: name, faversion: Config.fastadmin.version}

                                }, function (data, ret) {
                                    // 刷新菜单
                                    Fast.api.refreshmenu();
                                });
                            };

                            //// 安装按钮
                            $(document).on('click', '.btn-install', function () {
                                // 禁用按钮
                                $(this).prop("disabled", true).addClass("disabled");

                                // 安装选中插件
                                $("input[name=addon]:checked").each(function () {
                                    install($(this).data("name"));
                                });

                                return false;
                            });

                            //// btn-notnow按钮
                            $(document).on('click', '.btn-notnow', function () {
                                Layer.closeAll();
                            });

                            //// 打开弹层
                            Layer.open({
                                type: 1,
                                skin: 'layui-layer-page',
                                area: ["860px", "620px"],
                                title: '',
                                content: Template.render(ret.tpl, {addonlist: ret.rows})
                            });

                            localStorage.setItem("fastep", "dashboard");
                        });
                    }
                });
            }

            //// 版本检测
            var checkupdate = function (ignoreversion, tips) {
                $.ajax({
                    // https://api.fastadmin.net/version/check
                    url: Config.fastadmin.api_url + '/version/check',
                    type: 'post',
                    data: {version: Config.fastadmin.version},
                    dataType: 'jsonp',
                    success: function (ret) {
                        // console.log( ret );

                        if (ret.data && ignoreversion !== ret.data.newversion) {
                            //// 弹层
                            Layer.open({
                                // 发现新版本
                                title: __('Discover new version'),
                                maxHeight: 400,
                                content: '' +
                                    '<h5 style="background-color:#f7f7f7; font-size:14px; padding: 10px;">' +
                                        __('Your current version') + ':' + ret.data.version + '，' +
                                        __('New version') + ':' + ret.data.newversion +
                                    '</h5>' +
                                    '<span class="label label-danger">' + __('Release notes') + '</span><br/>' +
                                    ret.data.upgradetext,
                                btn: [__('Go to download'), __('Ignore this version'), __('Do not remind again')],
                                btn2: function (index, layero) {
                                    localStorage.setItem("ignoreversion", ret.data.newversion);
                                },
                                btn3: function (index, layero) {
                                    localStorage.setItem("ignoreversion", "*");
                                },
                                success: function (layero, index) {
                                    // 设置下载按钮路径
                                    $(".layui-layer-btn0", layero).
                                        attr("href", ret.data.downloadurl).
                                        attr("target", "_blank");
                                }
                            });

                        } else {
                            if (tips) {
                                Toastr.success(__('Currently is the latest version'));
                            }
                        }
                    },
                    error: function (e) {
                        if (tips) {
                            Toastr.error(__('Unknown data format') + ":" + e.message);
                        }
                    }
                });
            };

            //// 读取版本检测信息
            var ignoreversion = localStorage.getItem("ignoreversion");
            // 自动检测更新
            if (Config.fastadmin.checkupdate && ignoreversion !== "*") {
                checkupdate(ignoreversion, false); //检测版本;
            }

            //// 手动检测版本信息
            $("a[data-toggle='checkupdate']").on('click', function () {
                checkupdate('', true);
            });

            //// 切换左侧sidebar显示隐藏
            $(document).on("click fa.event.toggleitem", ".sidebar-menu li > a", function (e) {
                // 取消所有激活状态
                $(".sidebar-menu li").removeClass("active");

                // 当外部触发隐藏的a时,触发父辈a的事件
                if (!$(this).closest("ul").is(":visible")) {
                    //如果不需要左侧的菜单栏联动可以注释下面一行即可
                    $(this).closest("ul").prev().trigger("click");
                }

                var visible = $(this).next("ul").is(":visible");
                if (!visible) {
                    $(this).parents("li").addClass("active");

                } else {
                }

                e.stopPropagation();
            });

            //// 清除缓存
            $(document).on('click', "ul.wipecache li a", function () {
                // 清除请求
                $.ajax({
                    url: 'ajax/wipecache',
                    dataType: 'json',
                    data: {type: $(this).data("type")},
                    cache: false,
                    success: function (ret) {
                        if (ret.hasOwnProperty("code")) {
                            // 提示信息
                            var msg = ret.hasOwnProperty("msg") && ret.msg != "" ? ret.msg : "";

                            if (ret.code === 1) {
                                Toastr.success(msg ? msg : __('Wipe cache completed'));

                            } else {
                                Toastr.error(msg ? msg : __('Wipe cache failed'));
                            }

                        } else {
                            Toastr.error(__('Unknown data format'));
                        }
                    },
                    error: function () {
                        Toastr.error(__('Network error'));
                    }
                });
            });

            //// 全屏事件
            $(document).on('click', "[data-toggle='fullscreen']", function () {
                var doc = document.documentElement;
                if ($(document.body).hasClass("full-screen")) {
                    //// 退出全屏
                    $(document.body).removeClass("full-screen");

                    document.exitFullscreen ?
                        document.exitFullscreen() :
                        document.mozCancelFullScreen ?
                            document.mozCancelFullScreen() :
                            document.webkitExitFullscreen && document.webkitExitFullscreen();
                } else {
                    //// 进入全屏
                    $(document.body).addClass("full-screen");
                    doc.requestFullscreen ?
                        doc.requestFullscreen() :
                        doc.mozRequestFullScreen ?
                            doc.mozRequestFullScreen() :
                            doc.webkitRequestFullscreen ?
                                doc.webkitRequestFullscreen() :
                                doc.msRequestFullscreen && doc.msRequestFullscreen();
                }
            });

            //// 是否启用多级菜单导航
            var multiplenav = Config.fastadmin.multiplenav;
            var firstnav = $("#firstnav .nav-addtabs");
            var nav = multiplenav ? $("#secondnav .nav-addtabs") : firstnav;

            //// 刷新菜单事件
            $(document).on('refresh', '.sidebar-menu', function () {
                Fast.api.ajax({
                    url: 'index/index',
                    data: {action: 'refreshmenu'}

                }, function (data) {
                    // 删除动态菜单(非扩展菜单)
                    $(".sidebar-menu li:not([data-rel='external'])").remove();

                    $(".sidebar-menu").prepend(data.menulist);
                    if (multiplenav) {
                        // ?怀疑：此处firstnav写错，改为nav;
                        // firstnav.html(data.navlist);
                        nav.html(data.navlist);
                    }

                    // 触发链接
                    $("li[role='presentation'].active a", nav).trigger('click');

                    return false;

                }, function () {
                    return false;
                });
            });

            //// 多级菜单
            if (multiplenav) {
                //// 一级菜单自适应
                $(window).resize(function () {
                    //// 兄弟姐妹宽度
                    var siblingsWidth = 0;
                    firstnav.siblings().each(function () {
                        siblingsWidth += $(this).outerWidth();
                    });
                    //// 顶部菜单宽度
                    firstnav.width(firstnav.parent().width() - siblingsWidth);
                    //// 刷新
                    firstnav.refreshAddtabs();
                });

                //点击顶部第一级菜单栏
                firstnav.on("click", "li a", function () {
                    //// 处理激活状态
                    $("li", firstnav).removeClass("active");
                    $(this).closest("li").addClass("active");

                    //// 隐藏所有菜单
                    $(".sidebar-menu > li.treeview").addClass("hidden");
                    // 一级菜单
                    if ($(this).attr("url") == "javascript:;") {
                        var sonlist = $(".sidebar-menu > li[pid='" + $(this).attr("addtabs") + "']");
                        sonlist.removeClass("hidden");

                        var sidenav;
                        var last_id = $(this).attr("last-id");
                        if (last_id) {
                            sidenav = $(".sidebar-menu > li[pid='" + $(this).attr("addtabs") +
                                "'] a[addtabs='" + last_id + "']");

                        } else {
                            sidenav = $(".sidebar-menu > li[pid='" + $(this).attr("addtabs") + "']:first > a");
                        }
                        if (sidenav) {
                            sidenav.attr("href") != "javascript:;" && sidenav.trigger('click');
                        }

                    } else {

                    }
                });

                //// 点击左侧菜单栏
                $(document).on('click', '.sidebar-menu li a[addtabs]', function (e) {
                    var parents = $(this).parentsUntil("ul.sidebar-menu", "li");
                    var top = parents[parents.length - 1];
                    var pid = $(top).attr("pid");
                    if (pid) {
                        var obj = $("li a[addtabs=" + pid + "]", firstnav);
                        var last_id = obj.attr("last-id");
                        if (!last_id || last_id != pid) {
                            obj.attr("last-id", $(this).attr("addtabs"));
                            if (!obj.closest("li").hasClass("active")) {
                                obj.trigger("click");
                            }
                        }
                    }
                });

                var mobilenav = $(".mobilenav");
                $("#firstnav .nav-addtabs li a").each(function () {
                    mobilenav.append($(this).clone().addClass("btn btn-app"));
                });

                //// 点击移动端一级菜单
                mobilenav.on("click", "a", function () {
                    $("a", mobilenav).removeClass("active");
                    $(this).addClass("active");

                    $(".sidebar-menu > li.treeview").addClass("hidden");
                    if ($(this).attr("url") == "javascript:;") {
                        var sonlist = $(".sidebar-menu > li[pid='" + $(this).attr("addtabs") + "']");
                        sonlist.removeClass("hidden");
                    }
                });
            }

            //// 这一行需要放在点击左侧链接事件之前
            var addtabs = Config.referer ? localStorage.getItem("addtabs") : null;

            //绑定tabs事件,如果需要点击强制刷新iframe,则请将iframeForceRefresh置为true,iframeForceRefreshTable只强制刷新表格
            nav.addtabs({iframeHeight: "100%", iframeForceRefresh: false, iframeForceRefreshTable: true, nav: nav});

            if ($("ul.sidebar-menu li.active a").size() > 0) {
                $("ul.sidebar-menu li.active a").trigger("click");

            } else {
                if (Config.fastadmin.multiplenav) {
                    $("li:first > a", firstnav).trigger("click");

                } else {
                    $("ul.sidebar-menu li a[url!='javascript:;']:first").trigger("click");
                }
            }

            //// 如果是刷新操作则直接返回刷新前的页面
            if (Config.referer) {
                if (Config.referer === $(addtabs).attr("url")) {
                    var active = $("ul.sidebar-menu li a[addtabs=" + $(addtabs).attr("addtabs") + "]");
                    if (multiplenav && active.size() == 0) {
                        active = $("ul li a[addtabs='" + $(addtabs).attr("addtabs") + "']");
                    }
                    if (active.size() > 0) {
                        active.trigger("click");

                    } else {
                        $(addtabs).appendTo(document.body).addClass("hide").trigger("click");
                    }

                } else {
                    // 刷新页面后跳到到刷新前的页面
                    Backend.api.addtabs(Config.referer);
                }
            }

            var my_skins = [
                "skin-blue",
                "skin-white",
                "skin-red",
                "skin-yellow",
                "skin-purple",
                "skin-green",
                "skin-blue-light",
                "skin-white-light",
                "skin-red-light",
                "skin-yellow-light",
                "skin-purple-light",
                "skin-green-light"
            ];
            setup();

            function change_layout(cls) {
                $("body").toggleClass(cls);

                AdminLTE.layout.fixSidebar();

                //Fix the problem with right sidebar and layout boxed
                if (cls == "layout-boxed"){
                    AdminLTE.controlSidebar._fix($(".control-sidebar-bg"));
                }

                if ($('body').hasClass('fixed') && cls == 'fixed') {
                    AdminLTE.pushMenu.expandOnHover();
                    AdminLTE.layout.activate();
                }
                AdminLTE.controlSidebar._fix($(".control-sidebar-bg"));
                AdminLTE.controlSidebar._fix($(".control-sidebar"));
            }

            function change_skin(cls) {
                if (!$("body").hasClass(cls)) {
                    $("body").removeClass(my_skins.join(' ')).addClass(cls);

                    localStorage.setItem('skin', cls);
                    var cssfile = Config.site.cdnurl + "/assets/css/skins/" + cls + ".css";
                    $('head').append('<link rel="stylesheet" href="' + cssfile + '" type="text/css" />');
                }
                return false;
            }

            function setup() {
                var tmp = localStorage.getItem('skin');
                if (tmp && $.inArray(tmp, my_skins)) {
                    change_skin(tmp);
                }

                //// 皮肤切换
                $("[data-skin]").on('click', function (e) {
                    if ($(this).hasClass('knob')) {
                        return;
                    }

                    e.preventDefault();
                    change_skin($(this).data('skin'));
                });

                //// 布局切换
                $("[data-layout]").on('click', function () {
                    change_layout($(this).data('layout'));
                });

                //// 切换子菜单显示和菜单小图标的显示
                $("[data-menu]").on('click', function () {
                    if ($(this).data("menu") == 'show-submenu') {
                        $("ul.sidebar-menu").toggleClass("show-submenu");

                    } else {
                        nav.toggleClass("disable-top-badge");
                    }
                });

                //// 右侧控制栏切换
                $("[data-controlsidebar]").on('click', function () {
                    change_layout($(this).data('controlsidebar'));

                    var slide = !AdminLTE.options.controlSidebarOptions.slide;
                    AdminLTE.options.controlSidebarOptions.slide = slide;

                    if (!slide){
                        $('.control-sidebar').removeClass('control-sidebar-open');
                    }
                });

                //// 右侧控制栏背景切换
                $("[data-sidebarskin='toggle']").on('click', function () {
                    var sidebar = $(".control-sidebar");

                    if (sidebar.hasClass("control-sidebar-dark")) {
                        sidebar.removeClass("control-sidebar-dark")
                        sidebar.addClass("control-sidebar-light")

                    } else {
                        sidebar.removeClass("control-sidebar-light")
                        sidebar.addClass("control-sidebar-dark")
                    }
                });

                //// 菜单栏展开或收起
                $("[data-enable='expandOnHover']").on('click', function () {
                    $(this).attr('disabled', true);

                    AdminLTE.pushMenu.expandOnHover();

                    if (!$('body').hasClass('sidebar-collapse')) {
                        $("[data-layout='sidebar-collapse']").click();
                    }
                });

                //// 重设选项
                if ($('body').hasClass('fixed')) {
                    $("[data-layout='fixed']").attr('checked', 'checked');
                }
                if ($('body').hasClass('layout-boxed')) {
                    $("[data-layout='layout-boxed']").attr('checked', 'checked');
                }
                if ($('body').hasClass('sidebar-collapse')) {
                    $("[data-layout='sidebar-collapse']").attr('checked', 'checked');
                }
                if ($('ul.sidebar-menu').hasClass('show-submenu')) {
                    $("[data-menu='show-submenu']").attr('checked', 'checked');
                }
                if (nav.hasClass('disable-top-badge')) {
                    $("[data-menu='disable-top-badge']").attr('checked', 'checked');
                }

            }

            $(window).resize();

        },
        //// 登录方法
        login: function () {
            var lastlogin = localStorage.getItem("lastlogin");
            if (lastlogin) {
                lastlogin = JSON.parse(lastlogin);

                $("#profile-img").attr("src", Backend.api.cdnurl(lastlogin.avatar));
                $("#profile-name").val(lastlogin.username);
            }

            //// 让错误提示框居中
            Fast.config.toastr.positionClass = "toast-top-center";

            //// 本地验证未通过时提示
            $("#login-form").data("validator-options", {
                invalid: function (form, errors) {
                    $.each(errors, function (i, j) {
                        Toastr.error(j);
                    });
                },
                target: '#errtips'
            });

            //// 为表单绑定事件
            Form.api.bindevent($("#login-form"), function (data) {
                localStorage.setItem("lastlogin", JSON.stringify({
                    id: data.id,
                    username: data.username,
                    avatar: data.avatar
                }));

                location.href = Backend.api.fixurl(data.url);
            });
        }
    };

    return Controller;
});