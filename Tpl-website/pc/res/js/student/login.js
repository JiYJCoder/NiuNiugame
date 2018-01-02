define(["verify", 'ajax'], function( verify, ajax) {
    var loadTip = function() {

        //关闭login窗口
        $(".close-login-form").on("click", function() {
            $(".s-sx-login").removeClass("md-show");
        })

         //tabs切换
        var $tabsHead = $(".login-box .tabs span")
        $tabsHead.on("click", function() {
            var index = $(this).index();
            $(this).addClass('active').siblings().removeClass('active')
            $(".account-login").hide().eq(index).show();
        })
        
        //获取验证码
        var $sendCodeBtn = $(".s-sx-login .send-code-btn")
        $sendCodeBtn.on("click", function() {
            var f1 = verify.verifyPhone($phoneNumber)
            if (!f1) return false;
            verify.getCode($sendCodeBtn)
            ajax.get({
                url: "/index.php/student/send_verify",
                data: { mobile: $phoneNumber.val(), type: "2" },
                type: 3,
                success: function(data) {
                    // console.log(data)
                }
            })
        })

        /*学生登录*/
        var $phoneNumber = $('.login-box .phoneNumber')
        $(".go-login-btn").on("click", function() {
            var index = $(".login-box .tabs .active").index();
            if (index == 0) {
                var $accountLogin = $(".account-login").eq(index);
                var $accountInput = $accountLogin.find('.ipt').eq(0);
                var $passwordInput = $accountLogin.find('.ipt').eq(1);

                var f1 = verify.empty($accountInput, "请输入账号")
                if (!f1) return false;
                var userAccount = $accountInput.val();
                var password = $passwordInput.val();

                $.ajax({
                    url: "/index.php/student/login",
                    type: "POST",
                    beforeSend: function() {
                        $(".go-login-btn").val("正在登录中...")
                    },
                    data: {
                        type: 1,
                        username: userAccount,
                        password: password
                    },
                    success: function(data) {
                       
                        if (data.status == 0) {
                             $(".go-login-btn").val("立即登录")
                            layer.msg(data.msg)
                        } else {
                            window.location.reload();
                        }
                    }
                })
            } else {
                var $accountLogin = $(".account-login").eq(index);
                var $phoneNumber = $('.phoneNumber')
                var obj = {};
                obj.mobile = $phoneNumber.val();
                obj.check_code = $('.code-text').val();
                obj.type = 3;
                $.ajax({
                    url: "/index.php/student/login",
                    data: obj,
                    beforeSend: function() {
                        $(".go-login-btn").val("正在登录中...")
                    },
                    type: "POST",
                    success: function(data) {
                        if (data.status == 0) {
                             $(".go-login-btn").val("立即登录")
                            layer.msg(data.msg)
                        } else {
                            window.location.reload();
                        }
                    }
                })
            }
        })
    }
    return loadTip;
})