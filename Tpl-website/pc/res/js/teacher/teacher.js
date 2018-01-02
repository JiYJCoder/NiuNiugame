define(["layer", "myAjx", "clip", "ajax"], function(layer, myAjx, clip, ajax) {
    var loadTip = function() {
        /*我的直播页面*/
        //申请结束课程
        $(".gx_finish_class").on("click", function() {
            var id = $(this).attr("lesson_id")
            //询问框
            layer.confirm('是否申请结课？', {
                btn: ['确定', '取消'] //按钮
            }, function() {
                myAjx.post("/index.php/teacher/end_apply", { "lesson_id": id }, function(data) {})
            });
        })
        // 删除直播
        $(".gx_del_mylive").on("click", function() {
            var id = $(this).attr("del_id")
            //询问框
            var del_layer = layer.confirm('是否删除课程？', {
                btn: ['确定', '取消'] //按钮
            }, function(data) {
                layer.close(del_layer)
                ajax.post({
                    url: "/index.php/teacher/delcourse",
                    data: { id: id },
                    success: function() {
                        window.location.reload();
                    }
                })
            });
        })
        // 推流地址复制
       var clipboard = new clip('.copy-btn-uk');
        clipboard.on('success', function(e) {
            var msg = e.trigger.getAttribute('aria-label');
            layer.msg(msg);
            e.clearSelection();
        });

        //课程下降原因
        //是否停止更新录播
        $(".gx_look_reason").on("click", function() {
            var reason = $(this).attr("reason_value")
            layer.alert(reason);
        })
        /*我的录播页面*/
        $(".gx_del_myrecorded").on("click", function() {
            var $this = $(this)
            var id = $(this).attr("del_id")
            //询问框
            var del_layer = layer.confirm('是否停止更新？', {
                btn: ['确定', '取消'] //按钮
            }, function(data) {
                layer.close(del_layer)
                ajax.post({
                    url: "/index.php/teacher/delrecorded",
                    data: { "del_id": id },
                    type: 3,
                    success: function(data) {
                        $this.html("已停更");
                        $this.unbind("click"); //移除click
                        $this.removeClass('gx_del_myrecorded')
                    }
                })
            });
        })
        // 删除录播
        $(".gx_vod_del").on("click",function(){
            var id = $(this).attr("del_id")
            //询问框
            var del_layer = layer.confirm('是否删除课程？', {
                btn: ['确定', '取消'] //按钮
            }, function(data) {
                layer.close(del_layer)
                ajax.post({
                    url: "/index.php/teacher/delcourse",
                    data: { id: id ,type:2},
                    type:3,
                    success: function() {
                        window.location.reload();
                    }
                })
            });
        })
    }
    return loadTip;
})