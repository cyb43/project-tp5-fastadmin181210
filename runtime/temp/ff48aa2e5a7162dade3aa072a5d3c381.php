<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:100:"/Users/cenyebao/web/project-tp5-fastadmin181210/public/../application/admin/view/user/group/add.html";i:1561523284;s:90:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/admin/view/layout/default.html";i:1544409142;s:87:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/admin/view/common/meta.html";i:1557217439;s:89:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/admin/view/common/script.html";i:1557217598;}*/ ?>
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
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
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
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
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
<form id="add-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">
    <!-- 权限节点 -->
    <input type="hidden" name="row[rules]" />
    <!-- 组名 文本框 -->
    <div class="form-group">
        <label for="c-name" class="control-label col-xs-12 col-sm-2"><?php echo __('Name'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-name" class="form-control" name="row[name]" type="text" value="">
        </div>
    </div>
    <!-- 权限 树形选择 -->
    <div class="form-group">
        <label for="nickname" class="control-label col-xs-12 col-sm-2"><?php echo __('Permission'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <span class="text-muted">
                <input type="checkbox" name="" id="checkall" />
                <label for="checkall"><small><?php echo __('Check all'); ?></small></label>
            </span>
            <span class="text-muted">
                <input type="checkbox" name="" id="expandall" />
                <label for="expandall"><small><?php echo __('Expand all'); ?></small></label>
            </span>
            <!-- 树形选择 -->
            <div id="treeview"></div>
        </div>
    </div>
    <!-- 状态 单选按钮 -->
    <div class="form-group">
        <label for="c-status" class="control-label col-xs-12 col-sm-2"><?php echo __('Status'); ?>:</label>
        <div class="col-xs-12 col-sm-8">

            <div class="radio">
                <?php if(is_array($statusList) || $statusList instanceof \think\Collection || $statusList instanceof \think\Paginator): if( count($statusList)==0 ) : echo "" ;else: foreach($statusList as $key=>$vo): ?>
                <label for="row[status]-<?php echo $key; ?>">
                    <input id="row[status]-<?php echo $key; ?>" name="row[status]" type="radio" value="<?php echo $key; ?>"
                           <?php if(in_array(($key), explode(',',"normal"))): ?>checked<?php endif; ?> /> <?php echo $vo; ?></label>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </div>

        </div>
    </div>
    <!-- 按钮组 -->
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-success btn-embossed disabled"><?php echo __('OK'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
        </div>
    </div>
</form>
<script>
    var nodeData = <?php echo json_encode($nodeList);; ?>;
</script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ^2_3^ -->
<script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>