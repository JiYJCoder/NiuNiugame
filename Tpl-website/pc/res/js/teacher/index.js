　
define(["layer","clip","echarts"], function(layer,clip,echarts1) {　
    var loadTip = function() {
        $(function() {
            //折线图
            startChart();

            //直播复制功能
            copyAddress();

            //显示审核愿意
            showReasons()

            // 倒计时
            //GetRTime()

        })
    };　　

    function GetRTime() {
        var ts = (new Date(2017, 8, 11, 9, 0, 0)) - (new Date()); //计算剩余的毫秒数
        var dd = parseInt(ts / 1000 / 60 / 60 / 24, 10); //计算剩余的天数
        var hh = parseInt(ts / 1000 / 60 / 60 % 24, 10); //计算剩余的小时数
        var mm = parseInt(ts / 1000 / 60 % 60, 10); //计算剩余的分钟数
        var ss = parseInt(ts / 1000 % 60, 10); //计算剩余的秒数
        dd = splitTime(dd);
        hh = splitTime(hh);
        mm = splitTime(mm);
        ss = splitTime(ss);
        $(".djs_text").eq(0).html(dd[0])
        $(".djs_text").eq(1).html(dd[1])
        $(".djs_text").eq(2).html(hh[0])
        $(".djs_text").eq(3).html(hh[1])
        $(".djs_text").eq(4).html(mm[0])
        $(".djs_text").eq(5).html(mm[1])

        setInterval(GetRTime, 1000);
    }

    function splitTime(str) {
        if (i < 10) {
            i = "0" + i;
        }
        var str = str.toString();
        var arr = [];
        for (var i = 0; i < str.length; i++) {
            arr.push(str.substr(i, 1))
        }
        return arr;
    }　
    function showReasons() {
        $(".status_text.sjx_show").on("click",function(){
            var content = $(this).next().html()
            layer.alert(content)
        })
    }
    //复制功能,兼容所有浏览器
    function copyAddress() {
        var clipboard = new clip('.copy-btn-uk');
        clipboard.on('success', function(e) {
            var msg = e.trigger.getAttribute('aria-label');
            layer.msg(msg);
            e.clearSelection();
        });
    }
    //折线图
    function startChart() {
        var myChart = echarts.init(document.getElementById('chart'));
        var option = {
            color: ['#F54041', '#42CCFF', '#42ffa8', 'blueviolet'], //折线的颜色
            tooltip: { //鼠标悬浮交互时的信息提示
                trigger: 'item', // 触发类型，默认数据触发，可选为：’item’ | ‘axis’ ，提示框显示类型
                formatter: '{c}次'
            },
            legend: { //每个图表最多仅有一个图例，混搭图表共享
                data: ['收藏数', '报名数'],
                itemGap: 18, // Legend各个item之间的间隔，横向布局时为水平间隔，纵向布局时为纵向间隔
                x: "right",
                y: "45",
                top: 100,
                right:-100,
                // formatter: 'Legend {name}',//来用格式化
                selectedMode: 'single', //单选
                padding: [0, 50],
                textStyle: {
                    color: '#fff',
                    fontSize: 14
                }
            },
            grid: { //表格显示的位置
                 x:30,
                y:100,
                y2:40,
                x2:40,
                borderColor: "#333",
                // height: 200 //表格的高度
            },
            // calculable: true,
            xAxis: [{ //横纵坐标轴
                name: '天数',
                nameTextStyle: { //坐标轴名字样式
                    color: "#fff",
                    fontSize: 14
                },
                axisLine: { //坐标轴
                    show: true,
                    lineStyle: { //坐标轴样式
                        color: "rgba(128, 128, 128,0)"
                    }
                },
                axisTick: { // 去除坐标轴上的刻度线
                    show: false
                },
                axisLabel: { //设置坐标轴的文字
                    show: true,
                    textStyle: {
                        color: '#fff',
                        fontSize: '12'
                    }
                },
                splitLine: {
                    lineStyle: {
                        color: ['#333'],
                        width: 1,
                        type: 'solid'
                    }
                },
                type: 'category',
                boundaryGap: false,
                data: ['0', '5', '10', '15', '20', '25', '31']
            }],
            yAxis: [{ // 直角坐标系中的纵轴，通常并默认为数值型
                show: true,
                name: '人数',
                nameTextStyle: {
                    color: "#fff",
                    fontSize: 14
                },
                type: 'value',
                axisLabel: {
                    show: true,
                    textStyle: {
                        color: '#fff',
                        fontSize: '12'
                    }
                },
                axisLine: { //坐标轴
                    show: true,
                    lineStyle: { //坐标轴样式
                        color: "rgba(128, 128, 128,0)"
                    }
                },
                splitLine: {
                    lineStyle: {
                        color: ['#333'],
                        width: 1,
                        type: 'solid'
                    }
                },
                axisLabel: { //坐标文本颜色
                    show: true,
                    textStyle: {
                        color: '#fff'
                    }
                },
            }],
            series: [{ //    数据系列，一个图表可能包含多个系列，每一个系列可能包含多个数据
                name: '收藏数',
                type: 'line', //折线图
                data: [11, 11, 15, 133, 12, 13, 90],
                markPoint: { //平均值
                    data: [{
                        type: 'max',
                        name: '最大值'
                    }]
                },
            }, {
                name: '报名数',
                type: 'line',
                data: [18, 21, 22, 59, 34, 22, 10],
                markPoint: { //平均值
                    data: [{
                        type: 'max',
                        name: '最大值'
                    }]
                },
            }]
        };
       
        //图表更新数据;
        var $select1 = $(".select_zb_course_1");
        var $twoStageCatlog = $(".two-stage")
        var oneStageType = 1;
        $select1.change(function(){
              oneStageType = $(this).find("option:selected").val();
              var startId;
              if(oneStageType==1){
                 $(".two-stage").eq(0).show().siblings(".two-stage").hide();
                 startId = $(".two-stage1").find("option:selected").val();
              }else{
                 $(".two-stage").eq(1).show().siblings(".two-stage").hide();
                 startId = $(".two-stage2").find("option:selected").val();
              }
              getEchartsDate(oneStageType,startId)
        })

        $twoStageCatlog.change(function(){
            var startId = $(this).find("option:selected").val();
             getEchartsDate(oneStageType,startId)
        })

        var startId = $(".two-stage1").find("option:selected").val();
        getEchartsDate(1,startId)
        function getEchartsDate(type,id){
            $.ajax({
                url:"/index.php/teacher/link_data",
                type:"POST",
                data:{"type":type,"course_id":type},
                success:function(data){
                    var arr1 = [];
                    var arr2 = [];
                    for(var i in data.msg_fav){
                        arr1.push(data.msg_fav[i])
                    }
                    for(var i in data.msg_enroll){
                       arr2.push(data.msg_enroll[i])
                    }
                    option.series[0].data = arr1;
                    option.series[1].data = arr2;
                    option.xAxis[0].data = Object.keys(data.msg_fav);
                    myChart.setOption(option)
                }
            })
        }
    }　
    return {　　　　　　
        loadTip: loadTip
    };　　
});
