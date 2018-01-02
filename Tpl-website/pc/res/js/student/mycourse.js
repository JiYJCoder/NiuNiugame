define(["ajax"], function(ajax) {
    var loadTip = function() {
    	/*我的课程页面*/
        var $myCourseTab = $(".my-course-content .tab")
        var $myCourseTabContain = $(".my-course-content .tabcontain")
        $myCourseTab.on("click", function() {
            var index = $(this).index();
            $myCourseTabContain.eq(index).show().siblings('.tabcontain').hide();
            $myCourseTab.removeClass("active").eq(index).addClass("active");
        })
        /*直播课程和录播课程*/
    	// 取消收藏直播课程
        var $unStar = $(".cancel_favorite")
        $unStar.on("click", function() {
        	var type;
            var cid = $(this).attr("cid");
            var isLive = $(this).hasClass("cancel_favorite_live");
            isLive ? type = 1 : type = 2;
            //询问框
            var liveLayer = layer.confirm('您是否取消收藏？', {
                btn: ['确定', '取消'] //按钮
            }, function() {
                layer.close(liveLayer);
                ajax.post({
                    url: "/index.php/student/cancel_favorite",
                    data: { type: type, id: cid },
                    success: function() {
                        window.location.reload();
                    }
                })
            })
        })

    
        
    }
    return loadTip;
})