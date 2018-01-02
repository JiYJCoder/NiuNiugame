define(['layer', 'verify', "myAjx", "validate"], function(layer, verify, myAjx, validate) {
    var loadTip = function() {
        // step1 同意协议
        $(".agree_btn").on("click", function() {
            if (!$(".agree_read input").prop('checked')) {
                layer.msg("请勾选协议")
            } else {
                window.location.href = "/index.php/student/register_step2";
            }
        })

        // step2 手机邮箱切换
        $(".tabs label").on("click", function() {
            var index = $(this).index();
            $(".tabs-body .tabs-item").hide()
            $(".tabs-body .tabs-item").eq(index).show()
        })

        //获取验证码
        var $sendCodeBtn = $(".register .send-code-btn")
        $sendCodeBtn.on("click", function() {
            var checkedPhone = $(".list_2 .tabs input").eq(0).prop("checked")
            if (checkedPhone) {
                if (!verify.verifyPhone($(".phone-box"))) return false;
                var phoneNumber = $(".phone-box").val()
                myAjx.get("/index.php/student/send_verify", { "mobile": phoneNumber },
                    function(data) {
                        if (data.status == 1) {
                            layer.msg(data.msg)
                        }
                    })
            } else {
                if (!verify.checkEmail($(".email-box"))) return false;
                $(".go-email-link a").attr("href", "https://" + gotomail($(".email-box").val()))
                var emailNumber = $(".email-box").val()
                myAjx.get("/index.php/student/send_email", { "email": emailNumber },
                    function(data) {
                        if (data.status == 1) {
                            layer.msg(data.msg)
                            $(".go-email-link").show()
                        }
                    })
            }
            verify.getCode($sendCodeBtn)
        })

        //添加自定义验证规则
        jQuery.validator.addMethod("phone_number", function(value, element) {
            var length = value.length;
            var phone_number = /^(((13[0-9]{1})|(15[0-9]{1}))+\d{8})$/
            return this.optional(element) || (length == 11 && phone_number.test(value));
        }, "手机号码格式错误");

        jQuery.validator.addMethod("user_name", function(value, element) {
            var reg = /^[\u4e00-\u9fa5]{2,4}$/;
            return this.optional(element) || reg.test(value)
        }, "请输入2-4位字母组成的用户名");

        jQuery.validator.addMethod("checkID", function(value, element) {
            var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;  
            return this.optional(element) || reg.test(value)
        }, "请输入正确身份证号码");


        validateForm()
        function validateForm() {
            return $(".textbox").validate({
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
                    checkID:{
                        required: true,
                        checkID:true
                    },
                    code: {
                        required: true,
                    },
                    password: {
                        required: true,
                        minlength: 3,
                        maxlength: 32,
                    },
                    confirm_password: {
                        required: true,
                        minlength: 3,
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
                     checkID:{
                        required: "请输入身份证"
                    },
                    code: {
                        required: "请输入验证码",
                    },
                    password: {
                        required: "必须填写密码",
                        minlength: "密码至少为3个字符",
                        maxlength: "密码至多为32个字符",
                    },
                    confirm_password: {
                        required: "请输入密码",
                        minlength: "确认密码不能少于3个字符",
                        equalTo: "两次输入密码不一致", //与另一个元素相同
                    },
                }
            });
        }

        $(".submit-btn").on('click', function() {
            var checkedPhone = $(".list_2 .tabs input").eq(0).prop("checked")

            if (!verify.empty($(".user-name-box"), "请输入真实姓名") ||
                !verify.empty($(".user-school"), "请输入学校名") ||
                !verify.checkID($(".user-id-box")) ||
                !verify.empty($(".user-password"), "请输入密码") ||
                !verify.empty($(".code-box"), "请输入验证码")) return false;


            if (checkedPhone) {
                if (!verify.verifyPhone($(".phone-box"))) return false;
            } else {
                if (!verify.checkEmail($(".email-box"))) return false;
            }

            var obj = {};
            obj.real_name = $(".user-name-box").val();
            obj.school = $(".user-school").val();
            obj.idcard = $(".user-id-box").val();
            obj.type = $(".tabs").find("input[name='verify']:checked").attr("data_type");
            obj.email = $(".email-box").val();
            obj.mobile = $(".phone-box").val();
            obj.check_code = $(".code-box").val();
            obj.password = $(".user-password").val();

            myAjx.post("/index.php/student/student_register", obj, function(data) {
                if (data.status == 1) {
                    window.location.href = "/index.php/student/register_step3";
                }
            })
        })

    }
    return loadTip;
})

function gotomail($mail) {
    var $temp = String($mail).indexOf('@');
    var $t = String($mail).substring($temp + 1);

    if ($t == '163.com') {
        return 'mail.163.com';
    } else if ($t == 'vip.163.com') {
        return 'vip.163.com';
    } else if ($t == '126.com') {
        return 'mail.126.com';
    } else if ($t == 'qq.com' || $t == 'vip.qq.com' || $t == 'foxmail.com') {
        return 'mail.qq.com';
    } else if ($t == 'gmail.com') {
        return 'mail.google.com';
    } else if ($t == 'sohu.com') {
        return 'mail.sohu.com';
    } else if ($t == 'tom.com') {
        return 'mail.tom.com';
    } else if ($t == 'vip.sina.com') {
        return 'vip.sina.com';
    } else if ($t == 'sina.com.cn' || $t == 'sina.com') {
        return 'mail.sina.com.cn';
    } else if ($t == 'tom.com') {
        return 'mail.tom.com';
    } else if ($t == 'yahoo.com.cn' || $t == 'yahoo.cn') {
        return 'mail.cn.yahoo.com';
    } else if ($t == 'tom.com') {
        return 'mail.tom.com';
    } else if ($t == 'yeah.net') {
        return 'www.yeah.net';
    } else if ($t == '21cn.com') {
        return 'mail.21cn.com';
    } else if ($t == 'hotmail.com') {
        return 'www.hotmail.com';
    } else if ($t == 'sogou.com') {
        return 'mail.sogou.com';
    } else if ($t == '188.com') {
        return 'www.188.com';
    } else if ($t == '139.com') {
        return 'mail.10086.cn';
    } else if ($t == '189.cn') {
        return 'webmail15.189.cn/webmail';
    } else if ($t == 'wo.com.cn') {
        return 'mail.wo.com.cn/smsmail';
    } else if ($t == '139.com') {
        return 'mail.10086.cn';
    } else {
        return '';
    }
}