<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:112:"/Users/cenyebao/web/project-tp5-fastadmin181210/public/../application/admin/view/example/customsearch/index.html";i:1562761692;s:90:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/admin/view/layout/default.html";i:1544409142;s:87:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/admin/view/common/meta.html";i:1557217439;s:89:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/admin/view/common/script.html";i:1557217598;}*/ ?>
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
<div class="panel panel-default panel-intro">
    <!-- 规则 标题/备注 -->
    <?php echo build_heading(); ?>

    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">
                <div class="widget-body no-padding">
                    <div id="toolbar" class="toolbar">
                        <?php echo build_toolbar('refresh'); ?>
                    </div>

                    <table id="table" class="table table-striped table-bordered table-hover" width="100%">
                    </table>

                </div>
            </div>

        </div>
    </div>
</div>

<script id="customformtpl" type="text/html">
    <!--form表单必须添加form-commonsearch这个类-->
    <form action="" class="form-horizontal form-commonsearch">
        <div style="border-radius:2px; margin-bottom:10px; background:#f5f5f5; padding:20px;">
            <h4>自定义搜索表单</h4>
            <hr>
            <div class="row">
                <!-- ID搜索 -->
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label for="id" class="col-md-2 control-label">ID</label>
                        <div class="col-md-10" style="display: inline-block">
                            <!--[组合组件]_显式的operate操作符-->
                            <div class="input-group">
                                <div class="input-group-btn">
                                    <!-- operate，表明操作符 -->
                                    <select class="form-control operate" data-name="id" style="width:auto;">
                                        <option value="=" selected>等于</option>
                                        <option value=">">大于</option>
                                        <option value="<">小于</option>
                                    </select>
                                </div>
                                <input class="form-control" type="text" id="id" name="id"
                                       placeholder="记录id数值" value=""/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 标题搜索 -->
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label for="title" class="col-md-2 control-label">标题</label>
                        <div class="col-md-10">
                            <!--隐式的operate操作符，必须携带一个class为operate隐藏的文本框,且它的data-name="字段",值为操作符-->
                            <input class="operate" type="hidden" data-name="title" value="="/>
                            <input class="form-control" type="text" id="title" name="title"
                                   placeholder="请输入查找的标题" value=""/>
                        </div>
                    </div>
                </div>

                <!-- 管理员ID搜索 -->
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label class="col-md-2 control-label">管理员ID</label>
                        <!-- 关联下拉列表 -->
                        <div class="col-md-10">
                            <div class="row" data-toggle="cxselect" data-selects="group,admin">
                                <div class="col-xs-6" style="padding-right: 5px;">
                                    <select class="group form-control" name="group"
                                            data-url="example/bootstraptable/cxselect?type=group"></select>
                                </div>
                                <div class="col-xs-6" style="padding-left: 5px;">
                                    <select class="admin form-control" name="admin_id"
                                            data-url="example/bootstraptable/cxselect?type=admin"
                                            data-query-name="group_id"></select>
                                </div>
                                <!-- 操作符 -->
                                <input type="hidden" class="operate" data-name="admin_id" value="="/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 用户名搜索 -->
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">用户名</label>
                        <div class="col-sm-10">
                            <input type="hidden" class="operate" data-name="username" value="="/>
                            <!-- selectpage动态数据列表 -->
                            <input id="c-category_id" data-source="auth/admin/index" data-primary-key="username"
                                   data-field="username" class="form-control selectpage" name="username" type="text"
                                   value="">
                        </div>
                    </div>
                </div>

                <!-- IP搜索 -->
                <!--这里添加68px是为了避免刷新时出现元素错位闪屏-->
                <div class="col-xs-12 col-md-4" style="min-height:68px;">
                    <div class="form-group">
                        <label class="col-md-2 control-label">IP</label>
                        <div class="col-md-10">
                            <input type="hidden" class="operate" data-name="ip" value="in"/>
                            <!--给select一个固定的高度-->
                            <select id="c-flag" class="form-control selectpicker" multiple name="ip" style="height:31px;">
                                <?php if(is_array($ipList) || $ipList instanceof \think\Collection || $ipList instanceof \think\Paginator): if( count($ipList)==0 ) : echo "" ;else: foreach($ipList as $key=>$vo): ?>
                                <option value="<?php echo $key; ?>" <?php if(in_array(($key), explode(',',""))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 记录时间 -->
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label class="col-md-2 control-label">记录时间</label>
                        <div class="col-md-10">
                            <input type="hidden" class="operate" data-name="createtime" value="RANGE"/>
                            <input type="text" class="form-control datetimerange" name="createtime" value=""/>
                        </div>
                    </div>
                </div>

                <!-- 按钮组 -->
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label class="control-label"></label>
                        <div class="row">
                            <div class="col-xs-6">
                                <input type="submit" class="btn btn-success btn-block" value="提交"/>
                            </div>
                            <div class="col-xs-6">
                                <input type="reset" class="btn btn-primary btn-block" value="重置"/>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
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