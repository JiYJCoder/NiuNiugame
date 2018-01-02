define(["layer", "ajax"], function(layer, ajax) {
    var loadTip = function() {
        // 主课程id
        var fid_id = $(".t_info_wrapper").attr("fid_id");
        $(".save_sq_mask").fadeIn(100, function() {});

        //保存
        $(".complete_save_btn").on("click", function() {
            //主课程id
            var id = fid_id

            //课程描述
            var zc_content = editor.html();

            // lesson课程表
            var syllabus_list = $(".sq_syllabus .syllabus_list");
            var lesson = [];
            syllabus_list.each(function(index, el) {
                var obj = {};
                obj.lesson_title = $(this).find(".sq_syllabus_title").html();
                obj.lesson_date = $(this).find(".sq_syllabus_date").html();
                obj.lesson_start_time = $(this).find(".syllabus_title span").eq(1).html();
                obj.lesson_end_time = $(this).find(".syllabus_title span").eq(3).html();
                var id = $(this).attr("val");
                if(id){
                    obj.id = id;
                }
                lesson.push(obj)
            });
            // console.log(lesson)
            ajax.post({
                url: "/index.php/teacher/sqlive_complete",
                data: {
                    id: id,
                    zc_content: zc_content,
                    lesson: lesson
                },
                success: function(data) {
                    layer.msg(data['msg']);
                    window.location.href = "/index.php/teacher/mylive";
                }
            })

        })

        /*课程表附件上传*/
        $(".upload-ppt-file input").change(function(e) {
            var filesUrl = this.value
            var files = this.files[0];
            var $this = $(this)

            //初始化一个FormData对象
            var formData = new FormData();

            var lesson_id = this.getAttribute("val")

            formData.append("file", files);
            formData.append("fid_id", fid_id);
            formData.append("lesson_id", lesson_id);
            formData.append("type", "1");

            var postLoad;
            $.ajax({
                url: "/index.php/teacher/file_upload",
                type: "POST",
                cache: false,
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    postLoad = layer.msg("正在上传中...")
                },
                success: function(data) {
                    layer.close(postLoad)
                    layer.msg(data.msg)
                    if (data.status != 0) {
                        $this.parent().find(".upload_coures_file").addClass('has_coures_file')
                    }
                }
            })

        })

        var $closeFile = $(".complete_course .close-file")
        var $uploadBtn = $(".complete_course .uploadBtn")
        var $srUploadFileBox = $(".complete_course .sr-upload-file-box")
        var $uploadVideoBtn = $(".complete_course  .upload-video-btn")
        var $placeEditFileName = $(".complete_course .place-edit-file-name")
        var $startUpload = $(".complete_course .start-upload")
        var $fileBox = $(".complete_course .fileBox")
        var $fileBox = $(".complete_course .fileBox")


        // 关闭上传窗口
        $closeFile.on("click", function() {
            $srUploadFileBox.hide();
        })
        // 显示上传窗口
        $uploadBtn.on("click", function() {
            $srUploadFileBox.show();
        })
        // 删除文件
        $fileBox.on("click", ".del_file", function() {
            var $this = $(this);
            var oss_id = $(this).attr("oss_id")
            //询问框
            layer.confirm('确定删除附件？', {
                btn: ['确定', '取消'] //按钮
            }, function() {
                ajax.post({
                    url: "/index.php/teacher/delupload",
                    data: { live_id: fid_id, file_id: oss_id },
                    type: 2,
                    success: function(data) {
                        $this.parent().remove();
                    }
                })
            });
        })


        var courseDataFileForm;
        var courseDataFileObject;

        triggerChange()

        function triggerChange() {
            $uploadVideoBtn = $(".complete_course .upload-video-btn")
            $uploadVideoBtn.change(function(event) {
                if (this.files[0]) {
                    courseDataFileObject = this.files[0];
                    $placeEditFileName.val(courseDataFileObject.name)
                    //初始化一个FormData对象
                    courseDataFileForm = new FormData();
                    courseDataFileForm.append("file", courseDataFileObject);
                    courseDataFileForm.append("fid_id", fid_id);
                    courseDataFileForm.append("type", "2");
                    layer.msg(courseDataFileObject.name)
                }
            });
        }


        $startUpload.on("click", function() {
            var loading;

            if ($placeEditFileName.val().length == 0) {
                layer.msg("请填写文件名")
                return false;
            }
            if (!courseDataFileObject) {
                layer.msg("请选择文件")
                return false;
            }
            courseDataFileForm.append("file_name", $placeEditFileName.val());
            $.ajax({
                url: "/index.php/teacher/file_upload",
                type: "POST",
                cache: false,
                data: courseDataFileForm,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $srUploadFileBox.hide();
                    loading = layer.msg("正在上传中...", { time: 200000, shade: 0 })
                },
                success: function(data) {
                    if (data.status == 1) {
                        layer.close(loading)
                        //插入html
                        var fileHtml = `<div class="file_list">
                                            <span class="upload_file_bg"></span>
                                            <i class="file_icon"></i>
                                            <span class="file_file_text">FILE</span>
                                            <i class="del_file" oss_id="${data.data}"></i>
                                            <span class="file_text">${data.file_name}</span>
                                        </div>`

                        $fileBox.append(fileHtml)

                        //解决上传相同w文件不触发onchange事件 
                        var inputHtml = `<input class="upload-video-btn ii" id="files" type="file" accept=".ppt,.pptx" value="上传文件">`
                        $uploadVideoBtn.remove();
                        $(".upload-btn").append(inputHtml)
                        triggerChange()

                        // 改变状态
                        layer.msg("上传成功")
                        courseDataFileObject = false;
                        $placeEditFileName.val("");
                        $srUploadFileBox.hide();
                    }

                }
            })
        })
    }
    return loadTip;
})