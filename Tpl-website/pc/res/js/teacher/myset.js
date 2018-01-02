define(['layer', 'Util'], function(layer, Util) {
    function loadTip() {
        $(function() {
            //上传头像
            var uploadPicBtn = $(".select_file input")
            var base64Images;
            uploadPicBtn.change(function() {
                uploadPicBtn = this;
                var imgUrl = window.URL.createObjectURL(this.files[0])
                $(".t_pic").attr("src", imgUrl)

                var reader = new FileReader();
                reader.readAsDataURL(this.files[0]);
                reader.onload = function(e) {
                    base64Images = e.target.result;
                };
            })

            $(".suer_skill").on("click", function() {
                var skillHTML = $(".skill_input").val();
                if (skillHTML.length == 0) {
                    layer.msg("请添加技能")
                    return;
                }
                $(".skill_input").val("")
                var html = "<div class='tag_extra'><span>" + skillHTML + "</span><i class='delTag'></i></div>"
                $(".skill_tag ").append(html)
            })

            $(".skill_tag").on("click", ".delTag", function() {
                $(this).parent().remove();
            })

            //提交资料
            var submitInput = $(".t_set_save input")

            submitInput.on("click", function() {
                var tag = $(".tag_extra span")
                var arr = [];
                var textarea = $("textarea").val();
                tag.each(function(index, el) {
                    arr.push(Util.trimStr($(this).html()))
                });
                // arr = arr.join(",");
                // base64Images
                var loading;
                $.ajax({
                    url: mysetUpdateApi,
                    type: "POST",
                    beforeSend: function() {
                         loading = layer.load(1, {
                            shade: [0.1, '#fff'] //0.1透明度的白色背景
                        });
                    },
                    data: {
                        "headimg": base64Images,
                        "intro": textarea,
                        "good_at": arr = arr.join(",")
                    },
                    success: function(data) {
                        layer.close(loading)
                        layer.msg(data.msg)
                    }
                })
            })

        })
    }
    return {
        loadTip: loadTip
    }
})
