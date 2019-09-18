<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:84:"/Users/cenyebao/web/project-tp5-fastadmin181210/addons/example/view/index/index.html";i:1545618228;s:87:"/Users/cenyebao/web/project-tp5-fastadmin181210/addons/example/view/layout/default.html";i:1545618228;}*/ ?>
<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>开发者示例 - FastAdmin</title>

        <!-- Bootstrap Core CSS -->
        <link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

        <!-- Custom CSS -->
        <link href="/assets/addons/example/css/common.css" rel="stylesheet">

        <!-- Custom Fonts -->
        <link href="https://cdn.bootcss.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://cdn.bootcss.com/html5shiv/3.7.0/html5shiv.min.js"></script>
            <script src="https://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

    </head>

    <body>

        <!-- Navigation -->
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?php echo addon_url('example/index/index'); ?>">FastAdmin</a>
                </div>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a href="<?php echo addon_url('example/index/index'); ?>">插件首页</a>
                        </li>
                        <li>
                            <a href="<?php echo addon_url('example/demo/demo1', [':name'=>'s1']); ?>">无需登录页面</a>
                        </li>
                        <li>
                            <a href="<?php echo addon_url('example/demo/demo2', [':name'=>'s2']); ?>">需登录页面</a>
                        </li>
                        <?php if($user): ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">欢迎你! <?php echo $user['nickname']; ?><b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="<?php echo url('index/user/index'); ?>">会员中心</a>
                                </li>
                                <li>
                                    <a href="<?php echo url('index/user/profile'); ?>">个人资料</a>
                                </li>
                                <li>
                                    <a href="<?php echo url('index/user/logout'); ?>">退出登录</a>
                                </li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">会员中心 <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="<?php echo url('index/user/login'); ?>">登录</a>
                                </li>
                                <li>
                                    <a href="<?php echo url('index/user/register'); ?>">注册</a>
                                </li>
                            </ul>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!-- /.navbar-collapse -->
            </div>
            <!-- /.container -->
        </nav>

        
<!-- Header Carousel -->
<header id="myCarousel" class="carousel slide">
    <!-- Indicators -->
    <ol class="carousel-indicators">
        <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#myCarousel" data-slide-to="1"></li>
        <li data-target="#myCarousel" data-slide-to="2"></li>
        <li data-target="#myCarousel" data-slide-to="3"></li>
    </ol>

    <!-- Wrapper for slides -->
    <div class="carousel-inner">
        <div class="item active">
            <a href="https://www.fastadmin.net/store/cms.html" target="_blank">
                <div class="fill" style="background-image:url('http://bg.fh.com?text=random&color=3498db');"></div>
                <div class="carousel-body">
                    <div class="container">
                        <h1 class="display-1 text-white">CMS插件</h1>
                        <h2 class="display-4 text-white">简单强大的内容管理系统，可自定义内容模型、标签、伪静态、区块、个性化模板等功能<br >包含完整的小程序CMS客户端,拥有完善的资讯模块、产品模块、评论模块、会员模块</h2>
                    </div>
                </div>
            </a>
        </div>
        <div class="item">
            <a href="https://www.fastadmin.net/store/blog.html" target="_blank">
                <div class="fill" style="background-image:url('http://bg.fh.com?text=random&color=2ecc71');"></div>
                <div class="carousel-body">
                    <div class="container">
                        <h1 class="display-1 text-white">简单博客</h1>
                        <h2 class="display-4 text-white">响应式博客插件，包含日志、评论、分类、归档，包含完善的后台管理和前台功能<br />包含完整的小程序博客客户端,拥有博客日志列表、日志详情、评论等功能</h2>
                    </div>
                </div>
            </a>
        </div>
        <div class="item">
            <a href="https://www.fastadmin.net/store/docs.html" target="_blank">
                <div class="fill" style="background-image:url('http://bg.fh.com?text=random&color=9c88ff');"></div>
                <div class="carousel-body">
                    <div class="container">
                        <h1 class="display-1 text-white">Markdown文档生成插件</h1>
                        <h2 class="display-4 text-white">将Github或本地Git环境中的Markdown文件解析并生成HTML，可在线浏览或导出为HTML离线浏览</h2>
                    </div>
                </div>
            </a>
        </div>
        <div class="item">
            <a href="https://www.fastadmin.net/store/pay.html" target="_blank">
                <div class="fill" style="background-image:url('http://bg.fh.com?text=random&color=e66767');"></div>
                <div class="carousel-body">
                    <div class="container">
                        <h1 class="display-1 text-white">个人收款插件</h1>
                        <h2 class="display-4 text-white">提供微信、支付宝个人收款功能，免备注，实时通知和回调，可快速接入FastAdmin或自有订单系统</h2>
                    </div>
                </div>
            </a>
        </div>
    </div>
</header>

<!-- Page Content -->
<div class="container">

    <!-- Marketing Icons Section -->
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header">
                基础模块
            </h2>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><i class="fa fa-fw fa-user"></i> 前台模块</h4>
                </div>
                <div class="panel-body">
                    <p>FastAdmin有基础的前台模块，可快速的进行二次开发，前台模块中包含基础的会员模块，前台模块中的会员账号和API模块中的会员账号是同一账号体系</p>
                    <a href="<?php echo url('index/index/index'); ?>" target="_blank" class="btn btn-primary">立即访问</a>
                    <a href="<?php echo url('index/user/index'); ?>" target="_blank" class="btn btn-default">会员中心</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><i class="fa fa-fw fa-gift"></i> API模块</h4>
                </div>
                <div class="panel-body">
                    <p>FastAdmin有基础的API模块，可快速的进行二次开发，API模块中包含基础的会员模块，初始化模块、短信发送模块、验证模块</p>
                    <a href="<?php echo url('api/index/index'); ?>" target="_blank" class="btn btn-primary">立即访问</a>
                    <a href="<?php echo url('api/common/init'); ?>?version=1.0.0" target="_blank" class="btn btn-default">初始化接口</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><i class="fa fa-fw fa-compass"></i> API文档</h4>
                </div>
                <div class="panel-body">
                    <p>我们提供了一键生成API文档的功能，当写完API接口以后，可通过执行php think api一键生成我们所需要的API文档，并且可以直接在线测试</p>
                    <a href="<?php echo url('/'); ?>api.html" target="_blank" class="btn btn-primary">立即访问</a>
                    <a href="https://doc.fastadmin.net/docs/command.html#一键生成API文档" target="_blank" class="btn btn-default">一键生成文档</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header">
                功能示例
            </h2>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><i class="fa fa-fw fa-check"></i> 使用模板标签和变量</h4>
                </div>
                <div class="panel-body">
                    <p>在FastAdmin插件的视图中我们可以像前后台开发一样，向视图中渲染我们的自定义变量，然后在视图中进行访问。同时FastAdmin的插件视图中支持所有ThinkPHP5官方的系统模板标签和变量。轻轻松松的开发我们的插件</p>
                    <a href="<?php echo addon_url('example/demo/index',[':name'=>'s1']); ?>" class="btn btn-success">查看示例</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><i class="fa fa-fw fa-gift"></i> 访问不需要登录的页面</h4>
                </div>
                <div class="panel-body">
                    <p>在开发的过程有许多页面是不需要会员登录就可以访问的页面，比如新闻列表、新闻详情、评论列表、产品列表、产品展示、关于我们等等页面。在FastAdmin中可以使用noNeedLogin很方便的控制我们请求方法的访问权限</p>
                    <a href="<?php echo addon_url('example/demo/demo1',[':name'=>'s2']); ?>" class="btn btn-success">立即访问</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><i class="fa fa-fw fa-compass"></i> 访问需要登录的页面</h4>
                </div>
                <div class="panel-body">
                    <p>通常我们在开发中，如果页面与会员信息相关联，则通常需要控制页面在会员登录后才可以访问，FastAdmin中前台的权限控制可以很快捷的在插件中使用。我们可以直接重新定义noNeedLogin排除我们不需要登录的方法即可。</p>
                    <a href="<?php echo addon_url('example/demo/demo2',[':name'=>'s3']); ?>" class="btn btn-success">立即访问</a>
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->

    <div class="row addonlist">
        <div class="col-lg-12">
            <h2 class="page-header">模块&插件</h2>
        </div>

        <div class="col-md-4 col-sm-6">
            <a href="https://www.fastadmin.net/store/blog.html" target="_blank">
                <img class="img-responsive img-addon img-hover" src="https://cdn.fastadmin.net/uploads/addons/blog.png" alt="">
                <p>博客模块</p>
            </a>
        </div>
        <div class="col-md-4 col-sm-6">
            <a href="https://www.fastadmin.net/store/cms.html" target="_blank">
                <img class="img-responsive img-addon img-hover" src="https://cdn.fastadmin.net/uploads/addons/cms.png" alt="">
                <p>CMS模块</p>
            </a>
        </div>
        <div class="col-md-4 col-sm-6">
            <a href="https://www.fastadmin.net/store/pay.html" target="_blank">
                <img class="img-responsive img-addon img-hover" src="https://cdn.fastadmin.net/uploads/addons/pay.png" alt="">
                <p>个人微信支付宝收款</p>
            </a>
        </div>
        <div class="col-md-4 col-sm-6">
            <a href="https://www.fastadmin.net/store/docs.html" target="_blank">
                <img class="img-responsive img-addon img-hover" src="https://cdn.fastadmin.net/uploads/addons/docs.png" alt="">
                <p>文档生成模块</p>
            </a>
        </div>
        <div class="col-md-4 col-sm-6">
            <a href="https://www.fastadmin.net/store/filemanager.html" target="_blank">
                <img class="img-responsive img-addon img-hover" src="https://cdn.fastadmin.net/uploads/addons/filemanager.png" alt="">
                <p>文件管理器插件</p>
            </a>
        </div>
        <div class="col-md-4 col-sm-6">
            <a href="https://www.fastadmin.net/store/qiniu.html" target="_blank">
                <img class="img-responsive img-addon img-hover" src="https://cdn.fastadmin.net/uploads/addons/qiniu.png" alt="">
                <p>七牛上传插件</p>
            </a>
        </div>
    </div>
    <!-- /.row -->

    <hr>

    <!-- Call to Action Section -->
    <div class="well">
        <div class="row">
            <div class="col-md-8">
                <p>感谢你对FastAdmin的支持！如果你在使用FastAdmin开发插件的过程中有任何疑问或需要寻求帮助，请前往FastAdmin交流社区与小伙伴们一起交流。</p>
            </div>
            <div class="col-md-4">
                <a class="btn btn-lg btn-default btn-block" href="https://forum.fastadmin.net" target="_blank">立即前往社区</a>
            </div>
        </div>
    </div>

    <hr>

</div>

        <div class="container">
            <!-- Footer -->
            <footer>
                <div class="row">
                    <div class="col-lg-12">
                        <p>Copyright &copy; <a href="https://www.fastadmin.net">FastAdmin</a> 2018</p>
                    </div>
                </div>
            </footer>

        </div>
        <!-- /.container -->

        <!-- jQuery -->
        <script src="https://cdn.bootcss.com/jquery/1.11.1/jquery.min.js"></script>

        <!-- Bootstrap Core JavaScript -->
        <script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

        <!-- Script to Activate the Carousel -->
        <script>
            $('.carousel').carousel({
                interval: 5000 //changes the speed
            })
        </script>

    </body>

</html>
