define(["verify", "ajax", "validate"], function(verify, ajax, validate) {
    var loadTip = function() {
        $(function() {

            // 同意协议(step1)
            $(".agree_btn").on("click", function() {
                if (!$(".agree_read input").prop('checked')) {
                    layer.msg("请勾选协议")
                } else {
                    window.location.href = "/index.php/teacher/register_step2";
                }
            })

            //未有开通孜尔成长(step3.1) 
            // 获取验证码
            $(".code").on("click", function() {
                if (verify.verifyPhone($(".zs_phone")) == false) {
                    return false;
                }
                var codeText = $(".codeTextInput");
                verify.getCode($(".code"))

                ajax.get({
                    url: "/index.php/teacher/send_verify",
                    data: { "mobile": $(".zs_phone").val() }
                })
            })

             //添加自定义验证规则
            jQuery.validator.addMethod("phone_number", function(value, element) {
                var length = value.length;
                var phone_number = /^(1\d{10})$/
                return this.optional(element) || (length == 11 && phone_number.test(value));
            }, "手机号码格式错误");

            jQuery.validator.addMethod("user_name", function(value, element) {
                var reg = /^[\u4e00-\u9fa5]{2,4}$/;
                return this.optional(element) || reg.test(value)
            }, "请输入2-4位字母组成的用户名");


            validateForm()
            $(".submitBtn").on("click", function() {
                  if(!validateForm().form()){
                    return false;
                  }

                // 输入框验证
                // if (
                //     verify.verifyUser($(".real_name")) == false ||
                //     verify.empty($(".sz_school"), "请输入学校") == false ||
                //     verify.verifyPhone($(".zs_phone")) == false ||
                //     verify.empty($(".code"), "验证码") == false ||
                //     verify.empty($(".password1"), "请输入密码") == false

                // ) {
                //     return false
                // }
                // //两次密码是否一致验证
                // if ($(".password1").val() != $(".password2").val()) {
                //     layer.msg("两次密码输入不一致")
                //     return false
                // } else if ($(".password1").val().length < 6 || $(".password2").val().length < 6) {
                //     layer.msg("密码长度不能少于6位")
                //     return false
                // }

                $.ajax({
                    url: "/index.php/teacher/register_step_sub",
                    type: 'POST',
                    data: {
                        "type": '2',
                        "nickname": $(".real_name").val(),
                        "school": $(".sz_school").val(),
                        "mobile": $(".zs_phone").val(),
                        "verify_code": $(".codeTextInput").val(),
                        "password": $(".password1").val()
                    },
                    success: function(data) {
                        if (data.status == 0) {
                            layer.msg(data.msg)
                            return false;
                        }
                        // 通过验证
                        window.location.href = "/index.php/teacher/register_step5";
                    }
                })
            })

            //已开通孜尔成长(step3.2) 
            //我要修改
            $(".modify_info").on("click", function() {
                $(".user_text").hide(); //隐藏span
                $(".user_info .has_boxshaw").show(); //显示要修改的input
            })


            //确认无误后
            $(".determine").on("click", function() {
              
                if (verify.empty($(".user_t_name_input"), "请填写姓名") == false ||
                    verify.empty($(".user_t_school_input"), "请填写学校") == false ||
                    verify.empty($(".user_t_phone_input"), "手机号码") == false
                ) {
                    return false
                }

                $.ajax({
                    url: "/index.php/teacher/register_step_sub",
                    type: "POST",
                    data: {
                        "nickname": $(".user_t_name_input").val(),
                        "school": $(".user_t_school_input").val(),
                        "mobile": $(".user_t_phone_input").val(),
                        "accout": $(".client_id").val(),
                        "password": $(".step3_password").val(),
                        "headimg": $(".avatar").attr("src")
                    },
                    success: function(data) {
                        if (data.status == 0) {
                            layer.msg(data.msg)
                        } else {
                            window.location.href = "/index.php/teacher/register_step5";
                        }
                    }

                })
            }) 

            $(".tips_text").on("click",function(){
                $('body,html').animate({scrollTop:200},500);;
            })
            // 滑动解锁
            restSlider();

        })
    }
    return {
        loadTip
    }
})


function validateForm() {
    return $(".list_4").validate({
        rules: {
            username: {
                required: true,
                user_name: true, //自定义的规则
            },
            school: {
                required: true,
                minlength: 3
            },
            phone_number: {
                required: true,
                phone_number: true, //自定义的规则
            },
            code: {
                required: true,
            },
            password:{
                required:true,
                minlength:6, 
                maxlength:32,
            },
            confirm_password: {
                required: true,
                minlength: 6,
                equalTo: '.password'
            }

        },
        messages: {
            username: {
                required: "请输入用户名"
            },
            school: {
                required: "请输入学校",
                minlength: "学校名称最少3个字符"
            },
            phone_number: {
                required: "请输入手机号码",
            },
            code: {
                required: "请输入验证码",
            },
            password:{
                required:"必须填写密码",
                minlength:"密码至少为6个字符",
                maxlength:"密码至多为32个字符",
            },
            confirm_password: {
                required: "请输入密码",
                minlength: "确认密码不能少于6个字符",
                equalTo: "两次输入密码不一致", //与另一个元素相同
            },
        }
    });
}



function restSlider() {
    new Slider($('.bar1'), {
        text: '滑动验证身份',
        successFunc: function() {
            $(".slide-to-unlock-bg span").html("验证中...")
            $(".slide-to-unlock-progress").css("backgroundColor", "#FFE97F")
            $.ajax({
                url: "/index.php/teacher/getGrowInfo",
                data: {
                    "client_id": $(".client_id").val(),
                    "password": $(".step3_password").val()
                },
                dataType: "JSON",
                success: function(data) {
                    console.log(data);
                    if (data.status == 1) {
                        //验证成功操作
                        $(".slide-to-unlock-bg span").html("验证成功")
                        $(".slide-to-unlock-progress").css("backgroundColor", "#02B78C")
                        $('.determine').css("display", "block");
                        $(".backStep2").hide();
                        $(".default_text").hide();
                        $(".user_info").show();
                        $(".tips_text").hide();
                        $(".modify_info").show();

                        $(".avatar").attr("src", data.data.headimg)
                        $(".user_t_name").html(data.data.nickname)
                        $(".user_t_school").html(data.data.school)
                        $(".user_t_id").html(data.data.accout)
                        $(".user_t_phone").html(data.data.mobile)


                        $(".user_t_name_input").val(data.data.nickname)
                        $(".user_t_school_input").val(data.data.school)
                        $(".user_t_phone_input").val(data.data.mobile)


                    } else {
                        $(".slide-to-unlock-bg span").html("失败")
                        $(".slide-to-unlock-progress").css("backgroundColor", "#e3a26e")
                        $(".slide-to-unlock-bg span").html("验证失败")
                        $(".tips_text").html("验证账号不符，请重新验证").addClass("no_pass")
                        //重置滑动验证
                        $(".tips_text.no_pass").on("click", function() {
                            restSlider()
                        })

                        $(".backStep2").show()
                    }
                    $('html, body').animate({
                        scrollTop: $(".user_archives").offset().top
                    }, 1000);

                }
            })
        }
    })
}