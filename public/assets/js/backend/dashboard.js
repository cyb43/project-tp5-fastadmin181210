define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    // ^2_3^
    var Controller = {
        index: function () {
            //// 图表初始化
            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('echart'), 'walden');

            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '',
                    subtext: ''
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: [__('Sales'), __('Orders')]
                },
                toolbox: {
                    show: false,
                    feature: {
                        magicType: {show: true, type: ['stack', 'tiled']},
                        saveAsImage: {show: true}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: Orderdata.column
                },
                yAxis: {},
                grid: [{
                    left: 'left',
                    top: 'top',
                    right: '10',
                    bottom: 30
                }],
                series: [{
                    name: __('Sales'),
                    type: 'line',
                    smooth: true,
                    areaStyle: {
                        normal: {}
                    },
                    lineStyle: {
                        normal: {
                            width: 1.5
                        }
                    },
                    data: Orderdata.paydata
                },
                {
                    name: __('Orders'),
                    type: 'line',
                    smooth: true,
                    areaStyle: {
                        normal: {}
                    },
                    lineStyle: {
                        normal: {
                            width: 1.5
                        }
                    },
                    data: Orderdata.createdata
                }]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);

            //// 图表动态数据
            //动态添加数据，可以通过Ajax获取数据然后填充
            setInterval(function () {
                // 替换非数字字符为空
                Orderdata.column.push((new Date()).toLocaleTimeString().replace(/^\D*/, ''));

                // floor(x)，对数进行下舍入；
                var amount = Math.floor(Math.random() * 200) + 20;
                Orderdata.createdata.push(amount);
                Orderdata.paydata.push(Math.floor(Math.random() * amount) + 1);

                //按自己需求可以取消这个限制
                if (Orderdata.column.length >= 20) {
                    //移除最开始的一条数据
                    Orderdata.column.shift();
                    Orderdata.paydata.shift();
                    Orderdata.createdata.shift();
                }

                // 设置数据
                myChart.setOption({
                    xAxis: {
                        data: Orderdata.column
                    },
                    series: [{
                        name: __('Sales'),
                        data: Orderdata.paydata
                    },
                    {
                        name: __('Orders'),
                        data: Orderdata.createdata
                    }]
                });

                if ($("#echart").width() != $("#echart canvas").width() && $("#echart canvas").width() < $("#echart").width()) {
                    myChart.resize();
                }
            }, 2000);

            $(window).resize(function () {
                myChart.resize();
            });

            //// 后台控制器-服务器信息-检查最新版
            $(document).on("click", ".btn-checkversion", function () {
                top.window.$("[data-toggle=checkupdate]").trigger("click");
            });


            //// 读取FastAdmin的更新信息和社区动态
            // https://api.fastadmin.net/news/index
            var url_news = Config.fastadmin.api_url + '/news/index';
            $.ajax({
                url: url_news,
                type: 'post',
                dataType: 'jsonp',
                success: function (ret) {
                    // 最新新闻
                    //$("#news-list").html(Template("newstpl", {news: ret.newslist, url: url_news}));
                    $("#news-list").html(Template("newstpl", {news: [], url: ''}));
                }
            });
            $.ajax({
                url: Config.fastadmin.api_url + '/forum/discussion',
                type: 'post',
                dataType: 'jsonp',
                success: function (ret) {
                    // 最新发贴
                    //$("#discussion-list").html(Template("discussiontpl", {news: ret.discussionlist}));
                    $("#discussion-list").html(Template("discussiontpl", {news: []}));
                }
            });
        }
    };

    return Controller;
});