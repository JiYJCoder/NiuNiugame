$(function() {
    $(".select_option").on("click", ".select_class_btn", function() {
        var selectOptionBtn = $(this).parent()
        var selectClassBtn = selectOptionBtn.find(".select_class_btn")
        selectOptionBtn.find(".s_option_input").slideDown(50, function() {
            selectOptionBtn.find(".s_option_input").on("click", "span", function() {
                selectClassBtn.val($(this).html())
                selectClassBtn.attr("val", $(this).attr("val"))
                $(this).parent().slideUp(50)
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
    var base64Images;
    $(".img_box .uploadPic").change(function(e) {
        var imgUrl = window.URL.createObjectURL(this.files[0])
        $(".coures_img").attr("src", imgUrl)
        $(".mask").removeClass('init_pic')

        var reader = new FileReader();
        reader.readAsDataURL(this.files[0]);
        reader.onload = function(e) {
            base64Images = e.target.result;
        };

    });

    function submitForm() {
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

        // fid 一级分类id
        var fid = $(".one-catalog-btn").attr("val")
        // cat_id 二级分类id
        var cat_id = $(".two-catalog-btn").attr("val")

        var arr = [];
        $(".fileBox").find(".file_list").each(function(index, el) {
            var obj = {};
            var lesson_title;
            var UploadAddress;
            var VideoId;
            var UploadAuth;

            obj.UploadAddress = $(this).attr("UploadAddress")
            obj.VideoId = $(this).attr("uploadvodid")
            obj.UploadAuth = $(this).attr("UploadAuth")
            obj.lesson_title = $(this).find(".file_text").html();
            var val_id = $(this).attr("val_id");

            if(val_id){
                obj.id = val_id;
            }

            arr.push(obj)
        });

        var dataObj = {};
        dataObj.fid = fid;
        dataObj.cat_id = cat_id;
        dataObj.title = $(".s-course-name").val();
        dataObj.image = base64Images;
        dataObj.summary = $("textarea").val();
        dataObj.exp_lesson = $(".exp_lesson").val();
        dataObj.lesson = arr;
        return dataObj;
     
    }
    // 申请录播播保存
    $(".save_sqbroad_btn").on("click", function() {
        var dataObj  = submitForm();
        var loading;
        $.ajax({
            url: "/index.php/teacher/sqbroadcast_sub",
            type: "POST",
            beforeSend: function() {
                loading = layer.load(1, {
                    shade: [0.1, '#fff'] //0.1透明度的白色背景
                });
            },
            data: dataObj,
            success: function(data) {
                layer.close(loading)
                if (data.status == 1) {
                    $(".save_sq_mask").fadeIn(100, function() {});
                } else {
                    layer.msg(data.msg)
                }
            }
        })
    })

    $(".save_sqbroadchange_btn").on("click", function() {
        var dataObj  = submitForm();
        var changeId = $(".s_q_record").attr("vod_id")
        dataObj.id = changeId;

        var loading;
        $.ajax({
            url: "/index.php/teacher/sqbroadcast_change",
            type: "POST",
            beforeSend: function() {
                loading = layer.load(1, {
                    shade: [0.1, '#fff'] //0.1透明度的白色背景
                });
            },
            data: dataObj,
            success: function(data) {
                layer.close(loading)
                if (data.status == 1) {
                    $(".save_sq_mask").fadeIn(100, function() {});
                } else {
                    layer.msg(data.msg)
                }
            }
        })
    })

    //修改录播保持
    var uploader;

    var uploadAuth;
    var uploadAddress;
    var uploadVodId;

    // 选择文件
    var videoFile;
    var $editFileName = $(".place-edit-file-name")


    window.onload = new function() {
        uploader = new VODUpload({
            // 文件上传失败
            'onUploadFailed': function(uploadInfo, code, message) {
                //上传完成后按钮恢复
                $(".start-upload").removeAttr("disabled"); //将按钮可用
                $(".upload-video-btn").removeAttr("disabled"); //将按钮可用

                //上次成功后提示
                layer.msg("上传失败")
                //上传成功后关闭弹层
                $(".sr-upload-file-box").fadeOut(100)
                // log("onUploadFailed: file:" + uploadInfo.file.name + ",code:" + code + ", message:" + message);
            },
            // 文件上传完成
            'onUploadSucceed': function(uploadInfo) {

                // 判断是否有输入文件名称。
                var itemFileName;
                if ($editFileName.val().lenght == 0) {
                    itemFileName = videoFile[0].anme;
                } else {
                    itemFileName = $editFileName.val();
                }

                var str =
                    `<div class="file_list" uploadAuth=${uploadAuth} uploadAddress=${uploadAddress} uploadVodId=${uploadVodId}>
                        <span class="upload_file_bg"></span>
                        <i class="file_icon"></i>
                        <span class="file_file_text">Video</span>
                        <i class="del_file"></i>
                        <span class="file_text">${itemFileName}</span>
                    </div>`

                $(".fileBox").append(str)

                //上传完成后按钮恢复
                $(".start-upload").removeAttr("disabled"); //将按钮可用
                $(".upload-video-btn").removeAttr("disabled"); //将按钮可用
                //上次成功后清空文件
                videoFile = "";
                $editFileName.val("");
                $(".file-name").html("")
                $(".now-upload-text").html("请上传文件")
                //上次成功后提示
                layer.msg("视频上传成功")
                //上传成功后关闭弹层
                $(".sr-upload-file-box").fadeOut(100)
                $(".progress").css("width", 0)
                //
                // log("onUploadSucceed: " + uploadInfo.file.name + ", endpoint:" + uploadInfo.endpoint + ", bucket:" + uploadInfo.bucket + ", object:" + uploadInfo.object);

            },
            // 文件上传进度
            'onUploadProgress': function(uploadInfo, totalSize, uploadedSize) {

                console.log("onUploadProgress:file:" + uploadInfo.file.name + ", fileSize:" + totalSize + ", percent:" + Math.ceil(uploadedSize * 100 / totalSize) + "%")
                // log("onUploadProgress:file:" + uploadInfo.file.name + ", fileSize:" + totalSize + ", percent:" + Math.ceil(uploadedSize * 100 / totalSize) + "%");
                // 上传中进度条
                var progress = Math.ceil(uploadedSize * 100 / totalSize) - 1 + "%"
                $(".now-upload-text").html("正在上传" + progress)
                $(".progress").css("width", progress)

            },

            // 开始上传
            'onUploadstarted': function(uploadInfo) {

                uploader.setUploadAuthAndAddress(uploadInfo, uploadAuth, uploadAddress);
                // log("onUploadStarted:" + uploadInfo.file.name + ", endpoint:" + uploadInfo.endpoint + ", bucket:" + uploadInfo.bucket + ", object:" + uploadInfo.object);

            }
        });

        // 点播上传。每次上传都是独立的鉴权，所以初始化时，不需要设置鉴权
        uploader.init();

    };


    document.getElementById("files")
        .addEventListener('change', function(event) {
            var userData;
            userData = '{"Vod":{"UserData":"{"IsShowWaterMark":"false","Priority":"7"}"}}';

            for (var i = 0; i < event.target.files.length; i++) {
                uploader.addFile(event.target.files[i], null, null, null, userData);
            }
        });

    var textarea = document.getElementById("textarea");

    function start() {
        uploader.startUpload();
    }

    function stop() {
        uploader.stopUpload();
    }


    // 开启弹层
    $(".uploadBtn").on("click", function() {
        $(".sr-upload-file-box").fadeIn(100)

        $(".start-upload").attr({ "disabled": "disabled" });
        if (uploadAddress) {
            $(".start-upload").removeAttr("disabled"); //将按钮可用
        }
    })


    $(".upload-video-btn").change(function(event) {
        var file = this.files[0];
        var fileName = this.files[0].name
        videoFile = this.files[0];
        $editFileName.val(fileName)
        $(".file-name").html(fileName)


        $.ajax({
            url: '/index.php/teacher/apiUpload',
            data: {
                file_title: file.name,
                file_name: file.name,
                file_size: file.size
            },
            success: function(data) {
                uploadAuth = data.msg.UploadAuth;
                uploadAddress = data.msg.UploadAddress;
                uploadVodId = data.msg.VideoId;
                //将按钮可用
                $(".start-upload").removeAttr("disabled");
            }
        })

    });
    // 开始上传
    $(".start-upload").on("click", function() {
        // 判断是否有上传文件
        if (!videoFile) {
            layer.msg("请选择上传文件")
            return;
        }
        //上传的时候禁止按钮点
        $(".start-upload").attr({
            "disabled": "disabled"
        });
        $(".upload-video-btn").attr({
            "disabled": "disabled"
        });

        start();

    })

    // 关闭弹层
    $(".upload-wrp .close").on("click", function() {
        $(".sr-upload-file-box").fadeOut(100)
    })

    //删除文件
    $(".fileBox").on("click", ".del_file", function() {
        $(this).parent().remove();
    })
})