<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:100:"/Users/cenyebao/web/project-tp5-fastadmin181210/public/../application/index/view/user/changepwd.html";i:1563735822;s:90:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/index/view/layout/default.html";i:1563710415;s:87:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/index/view/common/meta.html";i:1563708693;s:90:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/index/view/common/sidenav.html";i:1563726046;s:89:"/Users/cenyebao/web/project-tp5-fastadmin181210/application/index/view/common/script.html";i:1563710469;}*/ ?>
<!-- ^2_3^ 前台默认布局 -->
<!DOCTYPE html>
<html>
    <head>
        <!-- ^2_3^ -->
<meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?> – <?php echo __('The fastest framework based on ThinkPHP5 and Bootstrap'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<?php if(isset($keywords)): ?>
<meta name="keywords" content="<?php echo $keywords; ?>">
<?php endif; if(isset($description)): ?>
<meta name="description" content="<?php echo $description; ?>">
<?php endif; ?>
<meta name="author" content="王尔贝">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />

<link href="/assets/css/frontend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>"
      rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->

<script type="text/javascript">
    var require = {
        config: <?php echo json_encode($config); ?>
    };
</script>
        <link href="/assets/css/user.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">
    </head>

    <body>

        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <!-- 菜单展示/隐藏按钮 -->
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#header-navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- logo图标 -->
                    <a class="navbar-brand" href="<?php echo url('/'); ?>" style="padding:6px 15px;">
                        <img src="/assets/img/logo.png" style="height:40px;" alt="">
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="header-navbar">
                    <ul class="nav navbar-nav navbar-right">
                        <!--<li><a href="https://www.fastadmin.net" target="_blank"><?php echo __('Home'); ?></a></li>-->
                        <!--<li><a href="https://www.fastadmin.net/store.html" target="_blank"><?php echo __('Store'); ?></a></li>-->
                        <!--<li><a href="https://www.fastadmin.net/wxapp.html" target="_blank"><?php echo __('Wxapp'); ?></a></li>-->
                        <!--<li><a href="https://www.fastadmin.net/service.html" target="_blank"><?php echo __('Services'); ?></a></li>-->
                        <!--<li><a href="https://www.fastadmin.net/download.html" target="_blank"><?php echo __('Download'); ?></a></li>-->
                        <!--<li><a href="https://www.fastadmin.net/demo.html" target="_blank"><?php echo __('Demo'); ?></a></li>-->
                        <!--<li><a href="https://www.fastadmin.net/donate.html" target="_blank"><?php echo __('Donation'); ?></a></li>-->
                        <!--<li><a href="https://forum.fastadmin.net" target="_blank"><?php echo __('Forum'); ?></a></li>-->
                        <li><a href="https://doc.fastadmin.net" target="_blank"><?php echo __('Docs'); ?></a></li>
                        <li class="dropdown">
                            <?php if($user): ?>
                            <a href="<?php echo url('user/index'); ?>" class="dropdown-toggle" data-toggle="dropdown"
                               style="padding-top: 10px;height: 50px;">
                                <span class="avatar-img"><img src="<?php echo cdnurl($user['avatar']); ?>" alt=""></span>
                            </a>
                            <?php else: ?>
                            <a href="<?php echo url('user/index'); ?>" class="dropdown-toggle" data-toggle="dropdown">
                                <?php echo __('User center'); ?> <b class="caret"></b>
                            </a>
                            <?php endif; ?>
                            <ul class="dropdown-menu">
                                <?php if($user): ?>
                                <!-- 会员中心 -->
                                <li><a href="<?php echo url('user/index'); ?>">
                                    <i class="fa fa-user-circle fa-fw"></i><?php echo __('User center'); ?></a>
                                </li>
                                <!-- 个人资料 -->
                                <li><a href="<?php echo url('user/profile'); ?>">
                                    <i class="fa fa-user-o fa-fw"></i><?php echo __('Profile'); ?></a>
                                </li>
                                <!-- 效果密码 -->
                                <li><a href="<?php echo url('user/changepwd'); ?>">
                                    <i class="fa fa-key fa-fw"></i><?php echo __('Change password'); ?></a>
                                </li>
                                <!-- 注销 -->
                                <li><a href="<?php echo url('user/logout'); ?>">
                                    <i class="fa fa-sign-out fa-fw"></i><?php echo __('Sign out'); ?></a>
                                </li>
                                <?php else: ?>
                                <!-- 登录 -->
                                <li><a href="<?php echo url('user/login'); ?>">
                                    <i class="fa fa-sign-in fa-fw"></i> <?php echo __('Sign in'); ?></a>
                                </li>
                                <!-- 注册 -->
                                <li><a href="<?php echo url('user/register'); ?>">
                                    <i class="fa fa-user-o fa-fw"></i> <?php echo __('Sign up'); ?></a>
                                </li>
                                <?php endif; ?>

                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="content">
            <!-- ^2_3^ -->
<div id="content-container" class="container">
    <div class="row">
        <!-- 侧边菜单 -->
        <div class="col-md-3">
            <!-- ^2_3^ 会员中心 侧边菜单 -->
<div class="sidenav">
    <?php echo hook('user_sidenav_before'); ?>
    <ul class="list-group">
        <li class="list-group-heading"><?php echo __('User center'); ?></li>
        <li class="list-group-item <?php echo $config['actionname']=='index'?'active':''; ?>">
            <a href="<?php echo url('user/index'); ?>"><i class="fa fa-user-circle fa-fw"></i> <?php echo __('User center'); ?></a>
        </li>
        <li class="list-group-item <?php echo $config['actionname']=='profile'?'active':''; ?>">
            <a href="<?php echo url('user/profile'); ?>"><i class="fa fa-user-o fa-fw"></i> <?php echo __('Profile'); ?></a>
        </li>
        <li class="list-group-item <?php echo $config['actionname']=='changepwd'?'active':''; ?>">
            <a href="<?php echo url('user/changepwd'); ?>"><i class="fa fa-key fa-fw"></i> <?php echo __('Change password'); ?></a>
        </li>
        <li class="list-group-item <?php echo $config['actionname']=='logout'?'active':''; ?>">
            <a href="<?php echo url('user/logout'); ?>"><i class="fa fa-sign-out fa-fw"></i> <?php echo __('Sign out'); ?></a>
        </li>
    </ul>
    <?php echo hook('user_sidenav_after'); ?>
</div>
        </div>

        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h2 class="page-header"><?php echo __('Change password'); ?></h2>

                    <form id="changepwd-form" class="form-horizontal" role="form" data-toggle="validator"
                          method="POST" action="">

                        <?php echo token(); ?>

                        <div class="form-group">
                            <label for="oldpassword" class="control-label col-xs-12 col-sm-2">
                                <?php echo __('Old password'); ?>:
                            </label>
                            <div class="col-xs-12 col-sm-4">
                                <input type="password" class="form-control" id="oldpassword" name="oldpassword"
                                       value="" data-rule="required" placeholder="<?php echo __('Old password'); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="newpassword" class="control-label col-xs-12 col-sm-2">
                                <?php echo __('New password'); ?>:
                            </label>
                            <div class="col-xs-12 col-sm-4">
                                <input type="password" class="form-control" id="newpassword" name="newpassword"
                                       value="" data-rule="required" placeholder="<?php echo __('New password'); ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="renewpassword" class="control-label col-xs-12 col-sm-2">
                                <?php echo __('Renew password'); ?>:
                            </label>
                            <div class="col-xs-12 col-sm-4">
                                <input type="password" class="form-control" id="renewpassword" name="renewpassword"
                                       value="" data-rule="required" placeholder="<?php echo __('Renew password'); ?>" />
                            </div>
                        </div>

                        <div class="form-group normal-footer">
                            <label class="control-label col-xs-12 col-sm-2"></label>
                            <div class="col-xs-12 col-sm-8">
                                <button type="submit" class="btn btn-success btn-embossed disabled"><?php echo __('Submit'); ?></button>
                                <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

        </main>

        <footer class="footer" style="clear:both">
            <!-- FastAdmin是开源程序，建议在您的网站底部保留一个FastAdmin的链接 -->
            <p class="copyright">
                Copyright&nbsp;©&nbsp;2019 Powered by <a href="https://cyb.club" target="_blank">^2_3_^FastAdmin</a>
                All Rights Reserved <?php echo $site['name']; ?> <?php echo __('Copyrights'); ?>
                <a href="http://www.miibeian.gov.cn" target="_blank"><?php echo $site['beian']; ?></a></p>
        </footer>

        <!-- ^2_3^ -->
<script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js"
        data-main="/assets/js/require-frontend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>">
</script>

    </body>

</html>