define(["datePicker", "timePicker", "layer", "syllabus", "myAjx","ajax"], function(datePicker, timePicker, layer, syllabus, myAjx,ajax) {
    var loadTip = function() {
        $(function() {
            var changeCourseId = $(".s_q_live_wrapper").attr("changecourseid")
            //初始化日历和时间
            new datePicker(".add_one_timeBtn")
            timePicker.init(".sr_start_time1")
            timePicker.init(".sr_start_time2")

            new datePicker(".add_many_timeBtn")
            timePicker.init(".sr_many_time1")
            timePicker.init(".sr_many_time2")

            //课程分类
            $(".select_option").on("click", ".select_class_btn", function() {
                var selectOptionBtn = $(this).parent()
                var selectClassBtn = selectOptionBtn.find(".select_class_btn")
                selectOptionBtn.find(".s_option_input").slideDown(150, function() {
                    selectOptionBtn.find(".s_option_input").on("click", "span", function() {
                        selectClassBtn.val($(this).html())
                        selectClassBtn.attr("val", $(this).attr("val"))
                        $(this).parent().slideUp(150)
                    })
                });
            })

            $(".select-one-catalog span").on("click", function() {
                $(".two-catalog-btn").val("请选择二级分类")
                var cat_id = $(this).attr("val")
                $.ajax({
                    url: "/index.php/Global/getLessonCat",
                    data: {
                        "cat_id": cat_id
                    },
                    success: function(data) {
                        var html = "";
                        for (var i = 0; i < data.length; i++) {
                            html += "<span val=" + data[i].id + ">" + data[i].title + "</span>"
                        }
                        $(".select-two-option").html(html)
                    }
                })
            })

            //课题封面
            $(".img_box .uploadPic").change(function(e) {
                var imgUrl = window.URL.createObjectURL(this.files[0])
                $(".coures_img").attr("src", imgUrl)
                $(".mask").removeClass('init_pic')
            });

            //添加单节课程
            syllabus()

            var base64Images;
            $(".uploadPic").change(function(event) {
                var reader = new FileReader();
                reader.readAsDataURL(this.files[0]);
                reader.onload = function(e) {
                    base64Images = e.target.result;
                };
            });
            //验证
            $(".save_sq_btn").on("click", function() {

                if ($(".select_class_btn").val() == "请选择一级分类") {
                    layer.msg("请选择一级分类")
                    return;
                }
                if ($(".select_class_btn").val() == "请选择二级分类") {
                    layer.msg("请选择二级分类")
                    return;
                }
                if ($(".course_title input").val().length == 0) {
                    layer.msg("请输入课题名称")
                    return;
                }
                if ($(".course_summary textarea").val().length == 0) {
                    layer.msg("请输入课题介绍")
                    return;
                }

                var syllabus_list = $(".sq_syllabus .syllabus_list");
                var arr = [];
                syllabus_list.each(function(index, el) {
                    var obj = {};
                    obj.lesson_title = $(this).find(".sq_syllabus_title").html();
                    obj.lesson_date = $(this).find(".sq_syllabus_date").html();
                    obj.lesson_start_time = $(this).find(".syllabus_title span").eq(1).html();
                    obj.lesson_end_time = $(this).find(".syllabus_title span").eq(3).html();
                    var id = $(this).attr("val");
                    if (id) {
                        obj.id = id;
                    }
                    arr.push(obj)
                });
                // fid 一级分类id
                var fid = $(".one-catalog-btn").attr("val")
                // cat_id 二级分类id
                var cat_id = $(".two-catalog-btn").attr("val")
                // title 课程标题
                var title = $(".course_title input").val();
                // image 课程封面
                base64Images
                // summary 课程简介
                var summary = $(".course_summary textarea").val()
                console.log(arr)
                $.ajax({
                    url: "/index.php/teacher/sqlive_change",
                    type: "POST",
                    data: {
                        "id": changeCourseId,
                        "fid": fid,
                        "cat_id": cat_id,
                        "title": title,
                        "image": base64Images,
                        "summary": summary,
                        "lesson": arr
                    },
                    success: function(data) {
                        if (data.status == 1) {
                            $(".save_sq_mask").fadeIn(200);
                        } else {
                            layer.msg(data.msg)
                        }
                    }
                })
            })

            /*删除课程*/
            $(".sq_syllabus").unbind("click");
            $(".sq_syllabus").on("click", ".del", function() {
                var id = $(this).attr("val")
                console.log(id)
                //询问框
                var _this = this;
                layer.confirm('确定删除？', {
                    btn: ['确定', '取消'] //按钮
                }, function() {
                    ajax.post({
                        url:"/index.php/teacher/live_del_course",
                        data:{id:id},
                        type:3,
                        success:function(data){
                            $(_this).parent().parent().remove();
                        }
                    })
                }, function() {});
            })

        })
    }

    return {　　　　　　
        loadTip: loadTip
    };　
})