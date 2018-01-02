define(function() {
    var loadTip = function() {

        // 显示与关闭
        $(".add_double_btn").on("click", function() {
            $(".add_one_content").hide();
            $(".add_many_content").fadeIn(100);
        })

        $(".add_one_btn").on("click", function() {
            $(".add_one_content").hide();
            $(".add_one_content1").fadeIn(100)
        })

        //添加多节课程操作
        $(".save_double_btn").on("click", function() {
            $(".arrange_course").remove()//默认图片
            var addManyDate = $(".add_many_timeBtn").val();
            var addManyTitle = $(".add_double_title").val();
            var srManyTime1 = $(".sr_many_time1").val();
            var srManyTime2 = $(".sr_many_time2").val();
            var addCourseCount = $(".add_course_count1").val(); //添加课节数量
            var addCourseDay = $(".add_course_day").val(); //隔天数量

            //if (addCourseCount > addCourseDay) {
            //    layer.msg("隔天天数不能少于课节数")
            //    return
            //}
            if (valiDate(addManyDate, addManyTitle, srManyTime1, srManyTime2) == false) {
                return
            }
            addManyDate = addManyDate.split("-");
            var tmp1 = parseInt(addManyDate[0]);
            var tmp2 = parseInt(addManyDate[1] - 1);
            var tmp3 = parseInt(addManyDate[2]);

            var html = ""
            var suffix = $(".sq_suffix").find("input[type='radio']:checked").val()
            for (var i = 0; i < addCourseCount; i++) {
                var date1 = new Date(tmp1, tmp2, tmp3);
                tmp3 = tmp3 + parseInt(addCourseDay)
                var date2 = [];
                date2[0] = date1.getFullYear();
                date2[1] = date1.getMonth() + 1;
                date2[2] = date1.getDate();
                date2 = date2.join("-")

                var conversion;
                if (suffix == "number") {
                    conversion = "(" + (i + 1) + ")"
                } else {
                    conversion = "(" + String.fromCharCode((65 + i)) + ")"
                }

                html += `<div class="syllabus_list clearfix">
                        <div class="list_left fl">
                            <span class="sq_syllabus_no fl">${i+1}.</span>
                            <span class="sq_syllabus_title fl">${addManyTitle}${conversion}</span>
                            <div class="syllabus_title fl">
                                <span style="padding-right:4px;" class="sq_syllabus_date">${date2}</span>
                                <span class="k_srStartTime1">${srManyTime1}</span>
                                <span>-</span>
                                <span class="k_srStartTime2">${srManyTime2}</span>
                            </div>
                        </div>
                        <div class="syllabus_right fr">
                            <i class="revise"></i>
                            <i class="del"></i>
                        </div>
                    </div>`
            }

            $(".sq_syllabus").append(html)

            $(".add_many_content").fadeOut(200, function() {
                restData($(".add_many_timeBtn"), $(".add_double_title"), $(".sr_many_time1"), $(".sr_many_time2"))
            })

        })


        //添加单节课程操作
        var addOfrevise;
        $(".save_sigle_btn").on("click", function() {
            $(".arrange_course").remove()
            var addSligleDate = $(".add_one_timeBtn").val();
            var addSligleTitle = $(".add_sligle_title").val();
            var srStartTime1 = $(".sr_start_time1").val();
            var srStartTime2 = $(".sr_start_time2").val();
            if (valiDate(addSligleDate, addSligleTitle, srStartTime1, srStartTime2) == false) {
                return
            }
            var no = $(".syllabus_list").length;

            if (addOfrevise) {
                //修改值
                addDate.html(addSligleDate);
                addTitle.html(addSligleTitle);
                reviseSrStartTime1.html(srStartTime1);
                reviseSrStartTime2.html(srStartTime2);
                addOfrevise = false;
                restData($(".add_one_timeBtn"), $(".add_sligle_title"), $(".sr_start_time1"), $(".sr_start_time2"))
                $(".add_one_content1").fadeOut(200, function() {
                    restData($(".add_one_timeBtn"), $(".add_sligle_title"), $(".sr_start_time1"), $(".sr_start_time2"))
                })
                return

            }
            var html = `<div class="syllabus_list clearfix">
                    <div class="list_left fl">
                        <span class="sq_syllabus_no fl">${(no+1)}.</span>
                        <span class="sq_syllabus_title fl">${addSligleTitle}</span>
                        <div class="syllabus_title fl">
                            <span style="padding-right:4px;" class="sq_syllabus_date">${addSligleDate}</span>
                            <span class="k_srStartTime1">${srStartTime1}</span>
                            <span>-</span>
                            <span class="k_srStartTime2">${srStartTime2}</span>
                        </div>
                    </div>
                    <div class="syllabus_right fr">
                        <i class="revise"></i>
                        <i class="del"></i>
                    </div>
                </div>`
            $(".sq_syllabus").append(html)
            $(".add_one_content1").fadeOut(200, function() {
                restData($(".add_one_timeBtn"), $(".add_sligle_title"), $(".sr_start_time1"), $(".sr_start_time2"))
            })
        })


        //修改当前课程
        var addDate;
        var addTitle;
        var reviseSrStartTime1;
        var reviseSrStartTime2;
        $(".sq_syllabus").on("click", ".revise", function() {
            console.log("99")
            addOfrevise = true;
            //找出元素
            var parentBox = $(this).parent().parent();
            addDate = parentBox.find(".sq_syllabus_date")
            addTitle = parentBox.find(".sq_syllabus_title")
            reviseSrStartTime1 = parentBox.find(".k_srStartTime1")
            reviseSrStartTime2 = parentBox.find(".k_srStartTime2")
            //取值
            var addDateHtm = addDate.html();
            var addTitleHtm = addTitle.html();
            var srStartTime1Html = reviseSrStartTime1.html();
            var srStartTime2Html = reviseSrStartTime2.html();

             console.log(srStartTime1Html,srStartTime2Html)
            $(".add_one_content1").fadeIn(200)
            //赋值到弹窗

            $(".add_one_content1 .add_one_timeBtn").val(addDateHtm)
            $(".add_one_content1 .add_sligle_title").val(addTitleHtm)
            $(".add_one_content1 .sr_start_time1").val(srStartTime1Html)
            $(".add_one_content1 .sr_start_time2").val(srStartTime2Html)

        })

        //删除课程
        $(".sq_syllabus").on("click", ".del", function() {
            //询问框
            var _this = this;
            layer.confirm('确定删除？', {
                btn: ['确定', '取消'] //按钮
            }, function() {
                $(_this).parent().parent().remove();
                layer.msg('删除成功');
            }, function() {
            });
        })

        //关闭课程表
        $(".close").on("click", function() {
            $(this).parent().hide();
            $(".v-date-picker").removeClass("active");
        })

        function valiDate(date, title, StartTime1, StartTime2) {
            if (date == "选择日期") {
                layer.msg("请输入日期")
                return false;
            }
            if (title.length == 0) {
                layer.msg("请输入标题")
                return false;
            }
            if (StartTime1 == "请输入" || StartTime2 == "请输入") {
                layer.msg("请输入直播时间")
                return false;
            }
            return true;
        }

        function restData(date, title, time1, time2) {
            date.val("选择日期");
            title.val("请输入标题");
            time1.val("请输入");
            time2.val("请输入");
        }

    }
    return loadTip;
})
