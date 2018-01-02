define(['ajax'], function(ajax) {
    var loadTip = function() {
        /*1.直播列表页面index*/
        var $selectGradeBtn = $(".select-grade")
        var $selectGradePanel = $(".select-grade-panel")
        var $selectGradePanelBtn = $selectGradePanel.find("button")
        $selectGradeBtn.on("click", function() {
            $selectGradePanel.show();
        })
        //选择年级
        $selectGradePanelBtn.on("click", function() {
            var gradeVal = $(this).html();
            var val = $(this).attr("val")
            gradeVal = gradeVal.replace(/(^\s*)|(\s*$)/g, "");
            $selectGradeBtn.find("button").html(gradeVal);
            $selectGradePanel.hide();
            $(".l-select-grade-input").val(val)
            $(".l-cat-id").val(0);
            toUrl()
        })
        //选择分类
        var $classifyBtn = $(".l-classify .btn")
        var $lCatId = $(".l-cat-id")
        $classifyBtn.on("click", function() {
            $(this).addClass('active').siblings().removeClass('active');
            var cat_id = $(this).attr("cat_id");
            $lCatId.val(cat_id)
            toUrl()
        })
        // 选择本校老师
        var $selectTeacher = $(".l-select-school-teacher");
        var $catIdBtn = $(".l-school");
        $selectTeacher.change(function() {
            if ($(this).is(':checked')) {
                $catIdBtn.val(1);
                toUrl()
            } else {
                $catIdBtn.val(0);
                toUrl()
            }
        })
        // 排序
        var $item = $(".l-record-statistics .item");
        $item.on("click", function() {
            var number;
            var index = $(this).index();
            $(this).addClass('active').siblings().removeClass("active");
            var $develop = $(this).find(".develop");
            if ($develop.hasClass('develop-up')) {
                $develop.removeClass('develop-up');
                if (index == 0) {
                    number = 1;
                } else if (index == 1) {
                    number = 3;
                } else {
                    number = 5;
                }
            } else {
                $develop.addClass('develop-up')
                if (index == 0) {
                    number = 2;
                } else if (index == 1) {
                    number = 4;
                } else {
                    number = 6;
                }
            }
            $(".l-orderby").val(number);
            toUrl()
        })

        function toUrl() {
            var class_url = $(".s-sn-recordlist").attr("class_url");
            var urlpara;
            var fid = $("#fid").val();
            var cat_id = $("#cat_id").val();
            var school = $("#school").val();
            var orderby = $("#orderby").val();

            var searchurl = '/index.php/' + class_url + '/index/s/';
            if (fid) urlpara = "fid/" + encodeURIComponent(fid) + "/";
            if (cat_id) urlpara += "cat_id/" + encodeURIComponent(cat_id) + "/";
            if (school) urlpara += "school/" + encodeURIComponent(school) + "/";
            if (orderby) urlpara += "orderby/" + encodeURIComponent(orderby) + "/";
            location.href = searchurl + base64encode(urlpara);
        }


        /*2.直播详情页*/
        var $liveDetailContain = $(".detail-wrapper")
        var $enroll = $liveDetailContain.find(".enroll"); //报名
        var $collect = $liveDetailContain.find(".collect"); //收藏
        var $playe = $liveDetailContain.find(".playe-video-icon"); //播放

        // 报名
        $enroll.on("click", function() {
            var _this = $(this);
            var id = $(this).attr('val');
            // ajax.post({
            //     url: '/index.php/student/enroll',
            //     data: { type: 1, id: id },
            //     // type: 3,
            //     success: function(data) {
            //         _this.html("已报名")
            //         _this.addClass('active')
            //         _this.unbind('click');
            //         location.reload();
            //     }
            // })
            $.post("/index.php/student/enroll", { type: 1, id: id }, function(data) {
                if (data.status == 0) {
                    $(".h-n-login").trigger("click")
                } else {
                    _this.html("已报名")
                    _this.addClass('active')
                    _this.unbind('click');
                    location.reload();
                }
            })
        })
        //收藏
        $collect.on("click", function() {
            var _this = $(this);
            var id = $(this).attr('val');
            if (!_this.hasClass('active')) {
                //取消收藏
                // ajax.post({
                //     url: '/index.php/student/cancel_favorite',
                //     data: { type: 1, id: id },
                //     type: 3,
                //     success: function(data) {
                //         _this.html("立即收藏")
                //         _this.removeClass('active')
                //     }
                // })

                $.post("/index.php/student/cancel_favorite", { type: 1, id: id }, function(data) {
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
                //     data: { type: 1, id: id },
                //     type: 3,
                //     success: function(data) {
                //         _this.html("取消收藏")
                //         _this.addClass('active')
                //     }
                // })
                $.post("/index.php/student/favorite", { type: 1, id: id }, function(data) {
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
        $playe.on("click", function() {
            var teacherId = $(this).attr("teacherId")
            var liveId = $(this).attr("liveId")
            var obj = {};
            obj.teacher_id = teacherId;
            obj.live_id = liveId;
            $.post("/index.php/Live/liveCheck", obj, function(data) {
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

        })
    }


    return loadTip;
})