$(function() {
    var fid_id = $(".sq_com_course").attr("fid_id");

    /*1.视频上传*/

    //修改录播保持
    var uploader;
    var uploadAuth;
    var uploadAddress;
    var uploadVodId;

    // 选择文件
    var videoFile;
    var $editFileName = $(".video-upload-file-box .place-edit-file-name") //video文件名
    var $videoFileLayer = $('.video-upload-file-box') //上传video弹层
    var $videoStartUploadBtn = $(".video-upload-file-box .video-start-upload") // 开始上传
    var $selectVideoFile = $(".video-upload-file-box .upload-video-btn"); //选择文件

    // 开启video-upload-box
    $(".video-upload").on("click", function() {
        $videoFileLayer.fadeIn(100)
        // $videoStartUploadBtn.attr({ "disabled": "disabled" });
        if (uploadAddress) {
            $videoStartUploadBtn.removeAttr("disabled"); //将按钮可用
        }
    })
      document.getElementById("files1").addEventListener('change', function(event) {
            var userData;
            userData = '{"Vod":{"UserData":"{"IsShowWaterMark":"false","Priority":"7"}"}}';

            for (var i = 0; i < event.target.files.length; i++) {
                uploader.addFile(event.target.files[i], null, null, null, userData);
            }
        });


    $selectVideoFile.change(function(event) {
        // var userData = '{"Vod":{"UserData":"{"IsShowWaterMark":"false","Priority":"7"}"}}';
        // for (var i = 0; i < event.target.files.length; i++) {
        //     uploader.addFile(event.target.files[i], null, null, null, userData);
        // }

        var file = this.files[0];
        var fileName = this.files[0].name
        videoFile = this.files[0];
        $editFileName.val(fileName)
        $videoFileLayer.find(".file-name").html(fileName)

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
                $videoStartUploadBtn.removeAttr("disabled");
            }
        })
    });

    // 开始上传
    $videoStartUploadBtn.on("click", function() {
        // 判断是否有上传文件
        if (!videoFile) {
            layer.msg("请选择上传文件")
            return;
        }
        //上传的时候禁止按钮点
        $videoStartUploadBtn.attr({
            "disabled": "disabled"
        });
        $selectVideoFile.attr({ "disabled": "disabled" });

        // 开始上传
        uploader.startUpload();
    })

    // 关闭弹层
    $videoFileLayer.on("click", ".close", function() {
        $videoFileLayer.fadeOut(100)
    })

    //删除视频
    $(".video-fileBox").on("click", ".del_file", function() {
        var $this = $(this)
        var file_id = $(this).attr("val_id")
        //询问框
        var delVideoLayer = layer.confirm('确定删除视频？', {
            btn: ['确定', '取消'] //按钮
        }, function() {
            ajax.post({
                url: "/index.php/teacher/del_vod_upload",
                data: { id: file_id },
                type: 3,
                success: function(data) {
                    $this.parent().remove();
                }
            })
        });
    })

    window.onload =  new function() {
        uploader = new VODUpload({
            // 文件上传失败
            'onUploadFailed': function(uploadInfo, code, message) {
                //上传完成后按钮恢复
                $videoStartUploadBtn.removeAttr("disabled"); //将按钮可用
                $selectVideoFile.removeAttr("disabled"); //将按钮可用

                //上次成功后提示
                layer.msg("上传失败")
                //上传成功后关闭弹层
                $(".sr-upload-file-box").fadeOut(100)
                // log("onUploadFailed: file:" + uploadInfo.file.name + ",code:" + code + ", message:" + message);
            },
            // 文件上传完成
            'onUploadSucceed': function(uploadInfo) {

                //判断是否有输入文件名称。
                var itemFileName;
                if ($editFileName.val().lenght == 0) {
                    itemFileName = videoFile[0].anme;
                } else {
                    itemFileName = $editFileName.val();
                }

                $.ajax({
                    url: "/index.php/teacher/vod_insert",
                    type: "POST",
                    data: {
                        VideoId: uploadVodId,
                        UploadAuth: uploadAuth,
                        UploadAddress: uploadAddress,
                        zc_title: itemFileName,
                        zn_cat_id: fid_id
                    },
                    success: function(data) {
                        var id = data.val_id;
                        var str =
                            `<div class="file_list" uploadAuth=${uploadAuth} uploadAddress=${uploadAddress} uploadVodId=${uploadVodId}>
                        <span class="upload_file_bg"></span>
                        <i class="file_icon"></i>
                        <span class="file_file_text">Video</span>
                        <i val_id=${id} class="del_file"></i>
                        <span class="file_text">${itemFileName}</span>
                    </div>`

                        $(".video-fileBox").append(str)

                        //上传完成后按钮恢复
                        $videoStartUploadBtn.removeAttr("disabled"); //将按钮可用
                        $selectVideoFile.removeAttr("disabled"); //将按钮可用
                        //上次成功后清空文件
                        videoFile = "";
                        $editFileName.val("");
                        $videoFileLayer.find(".file-name").html("")
                        $(".now-upload-text").html("请上传文件")
                        //上次成功后提示
                        layer.msg("视频上传成功")
                        //上传成功后关闭弹层
                        $videoFileLayer.fadeOut(100)
                        $(".progress").css("width", 0)
                    }
                })
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

    /*2.资料上传*/
    var $courseDataFileLayer = $(".course-data-file-layer")
    var $selectPPtBtn = $('.upload-ppt-file');
    var $startUploadPpt = $(".start-upload-ppt")
    var $close = $courseDataFileLayer.find('.close ')
    var $placeEditFileName = $courseDataFileLayer.find(".place-edit-file-name");
    var $courseFileBox = $(".course-fileBox");
    var courseDataFileForm;
    var courseDataFileObject;

    $(".course-data-upload").on("click", function() {
        $courseDataFileLayer.show();
    })
    $close.on("click", function() {
        $courseDataFileLayer.hide();
    })

    // 课程表附件上传
    $selectPPtBtn.change(function(e) {
        if (this.files[0]) {
            courseDataFileObject = this.files[0];
            $placeEditFileName.val(courseDataFileObject.name)

            //初始化一个FormData对象
            courseDataFileForm = new FormData();
            courseDataFileForm.append("file", courseDataFileObject);
            courseDataFileForm.append("fid_id", fid_id);
            courseDataFileForm.append("type", "2");
            courseDataFileForm.append("zn_type", "2");
            layer.msg(courseDataFileObject.name)
            $startUploadPpt.removeAttr("disabled");
        }
    })

    // 上传资料上传文件
    $startUploadPpt.on("click", function() {
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
                $courseDataFileLayer.hide();
                loading = layer.msg("正在上传中...", { time: 200000, shade: 0 })
            },
            success: function(data) {
                layer.close(loading)
                if (data.status == 1) {
                    //插入html
                    var fileHtml = `<div class="file_list">
                                            <span class="upload_file_bg"></span>
                                            <i class="file_icon"></i>
                                            <span class="file_file_text">FILE</span>
                                            <i class="del_file" oss_id="${data.data}"></i>
                                            <span class="file_text">${data.file_name}</span>
                                        </div>`
                    $('.course-fileBox').append(fileHtml)

                    // 改变状态
                    layer.msg("上传成功")
                    courseDataFileObject = false;
                    $placeEditFileName.val("");
                } else {
                    layer.msg(data.msg)
                }
            }
        })
    })

    // 删除课堂资料文件
    $courseFileBox.on("click", ".del_file", function() {
        var $this = $(this);
        var oss_id = $(this).attr("oss_id")
        //询问框
        var delLayer = layer.confirm('确定删除附件？', {
            btn: ['确定', '取消'] //按钮
        }, function() {
            layer.close(delLayer)
            ajax.post({
                url: "/index.php/teacher/delupload",
                data: { live_id: fid_id, file_id: oss_id },
                type: 3,
                success: function(data) {
                    $this.parent().remove();
                }
            })
        });
    })
  /*3.保存修改*/
    $(".complete_save_btn").on("click", function() {
        var zc_content = editor.html();
        if (!zc_content) {
            layer.msg("描述不能为空，请重新填写..")
            return;
        }
        ajax.post({
            url: '/index.php/teacher/recorded_complete',
            data: { id: fid_id, zc_content: zc_content },
            success: function(data) {
                layer.msg(data['msg']);
                setTimeout(function(){
                    window.location.href = "/index.php/teacher/myrecorded";
                },1000);
            }
        })
    })

})