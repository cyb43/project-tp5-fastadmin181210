<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:114:"/Users/cenyebao/web/project-tp5-fastadmin181210/public/../application/admin/view/example/bootstraptable/index.html";i:1563190647;s:90:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/admin/view/layout/default.html";i:1563531062;s:87:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/admin/view/common/meta.html";i:1562838653;s:89:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/admin/view/common/script.html";i:1562839069;}*/ ?>
<!-- ^2_3^ -->
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <!-- ^2_3^ -->
<meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>"
      rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    // 配置信息
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>
    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !$config['fastadmin']['multiplenav']): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <li>
                                        <a href="dashboard" class="addtabsit">
                                            <i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?>
                                        </a>
                                    </li>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <!-- ^2_3^ -->
<div class="panel panel-default panel-intro">
    <!-- 规则 标题/备注 -->
    <?php echo build_heading(); ?>

    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">
                <div class="widget-body no-padding">
                    <div id="toolbar" class="toolbar">
                        <!-- 工具栏 -->
                        <?php echo build_toolbar('refresh,delete'); ?>
                        <!-- 获取选中项 -->
                        <a class="btn btn-info btn-disabled disabled btn-selected" href="javascript:;">
                            <i class="fa fa-leaf"></i> 获取选中项
                        </a>
                        <!-- 更多按钮(下拉菜单) -->
                        <div class="dropdown btn-group">
                            <a class="btn btn-primary btn-more dropdown-toggle btn-disabled disabled"
                               data-toggle="dropdown"><i class="fa fa-cog"></i> <?= __('More') ?></a>
                            <ul class="dropdown-menu text-left" role="menu">
                                <li><a class="btn btn-link btn-multi btn-disabled disabled" href="javascript:;"
                                       data-params="status=normal"><i class="fa fa-eye"></i> <?php echo __('Set to normal'); ?></a></li>
                                <li><a class="btn btn-link btn-multi btn-disabled disabled" href="javascript:;"
                                       data-params="status=hidden"><i class="fa fa-eye-slash"></i> <?php echo __('Set to hidden'); ?></a></li>
                            </ul>
                        </div>
                        <!-- 自定义搜索 -->
                        <a class="btn btn-success btn-singlesearch" href="javascript:;">
                            <i class="fa fa-user"></i> 自定义搜索</a>
                        <!-- 启动 (data-params="action=start" data-url="example/bootstraptable/start") -->
                        <a class="btn btn-success btn-change btn-start"
                           data-params="action=start" data-url="example/bootstraptable/start" href="javascript:;">
                            <i class="fa fa-play"></i> 启动</a>
                        <!-- 暂停 (data-params="action=pause" data-url="example/bootstraptable/pause") -->
                        <a class="btn btn-danger btn-change btn-pause"
                           data-params="action=pause" data-url="example/bootstraptable/pause" href="javascript:;">
                            <i class="fa fa-pause"></i> 暂停</a>
                        <!-- JSONP请求 -->
                        <a class="btn btn-primary btn-jsonp" href="javascript:;">
                            <i class="glyphicon glyphicon-screenshot"></i> JSONP</a>
                        <!-- 金额/单价 显示 -->
                        <a href="javascript:;" class="btn btn-default" style="font-size:14px; color:dodgerblue;">
                            <i class="fa fa-dollar"></i>
                            <span class="extend">
                                金额：<span id="money">0</span>
                                单价：<span id="price">0</span>
                            </span>
                        </a>
                    </div>

                    <!-- 表格 -->
                    <table id="table" class="table table-striped table-bordered table-hover" width="100%">
                    </table>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- 模版(分组/管理员 关联列表) -->
<script id="categorytpl" type="text/html">
    <div class="row">
        <div class="col-xs-12">
            <div class="form-inline" data-toggle="cxselect" data-selects="group,admin">
                <!-- 分组列表 -->
                <select class="group form-control" name="group"
                        data-url="example/bootstraptable/cxselect?type=group"></select>
                <!-- 管理员列表 -->
                <select class="admin form-control" name="admin_id"
                        data-url="example/bootstraptable/cxselect?type=admin" data-query-name="group_id"></select>
                <input type="hidden" class="operate" data-name="admin_id" value="=" />
            </div>
        </div>
    </div>
</script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ^2_3^ -->
<script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js"
        data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>