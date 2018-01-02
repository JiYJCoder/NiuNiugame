define(["ajax"], function(ajax) {
    var Tipload = function() {
        var $enroll = $(".enroll"); //报名
        // 报名
        $enroll.on("click", function() {
            var _this = $(this);
            var id = $(this).attr('val');
            // ajax.post({
            //     url: '/index.php/student/enroll',
            //     data: { type: 2, id: id },
            //     type: 3,
            //     success: function(data) {
            //         _this.html("已报名")
            //         _this.addClass('active')
            //         location.reload();
            //         _this.unbind('click');
            //         location.reload();
            //     }
            // })

            $.post("/index.php/student/enroll", { type: 2, id: id }, function(data) {
                if (data.status == 0) {
                    $(".h-n-login").trigger("click")
                } else {
                    _this.html("已报名")
                    _this.addClass('active')
                    location.reload();
                    _this.unbind('click');
                    location.reload();
                }
            })

        })


        var $collect = $(".collect"); //收藏
        $collect.on("click", function() {
            var _this = $(this);
            var id = $(this).attr('val');
            if (!_this.hasClass('active')) {
                //取消收藏
                // ajax.post({
                //     url: '/index.php/student/cancel_favorite',
                //     data: { type: 2, id: id },
                //     type: 3,
                //     success: function(data) {
                //         _this.html("立即收藏")
                //         _this.removeClass('active')
                //     }
                // })

                $.post("/index.php/student/cancel_favorite", { type: 2, id: id }, function(data) {
                    if (data.status == 0) {
                        $(".h-n-login").trigger("click")
                    } else {
                        _this.html("立即收藏")

                        _this.addClass('active')
                    }
                })


            } else {
                //立即收藏
                // ajax.post({
                //     url: '/index.php/student/favorite',
                //     data: { type: 2, id: id },
                //     type: 3,
                //     success: function(data) {
                //         _this.html("取消收藏")
                //         _this.addClass('active')
                //     }
                // })

                $.post("/index.php/student/favorite", { type: 2, id: id }, function(data) {
                    if (data.status == 0) {
                        $(".h-n-login").trigger("click")
                    } else {
                        _this.html("已收藏")
                        _this.removeClass('active')

                    }
                })


            }
        })

        // 播放
        var $playe = $(".playe-video-icon"); //播放
        $playe.on("click", function() {
            var obj = {};
            obj.teacher_id = $(this).attr("teacherid");
            obj.vod_id = $(this).attr("liveid");
            obj.vod_lesson_id = $(this).attr("vodlessonid");
            $.post("/index.php/vod/vodCheck", obj, function(data) {
                if (data.status == 2) {
                    $(".h-n-login").trigger("click")
                    return false;
                }
                if (data.status == 1) {
                    layer.msg(data.msg);
                    location.href = data.url;
                }
                if (data.status == 0) {
                    layer.msg(data.msg);
                }
            })
            // ajax.post({
            //     url: "/index.php/vod/vodCheck",
            //     data: obj,
            //     success: function(data) {

            //         // layer.msg(data.msg);
            //     }
            // })
        })

        var $vodLink = $('.video-list a');
        $vodLink.on("click", function() {
            var obj = {};
            obj.teacher_id = $playe.attr("teacherid");
            obj.vod_id = $playe.attr("liveid");
            obj.vod_lesson_id = $(this).attr("vod_id");

            ajax.post({
                url: "/index.php/vod/vodCheck",
                data: obj,
                success: function(data) {
                    if (data.status == 1) {
                        location.href = data.url;
                    }
                    layer.msg(data.msg);
                }
            })
        })

    };
    return Tipload;
})