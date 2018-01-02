define(["layer", "ajax", "Util", "verify"], function(layer, ajax, Util, verify) {
    var loadTip = function() {
        var $validateAccount = $(".validate-account");
        $validateAccount.on("click", function() {
            var account = $(".account-input-12").val();
            if (account.length == 0) {
                layer.msg("请填写帐号")
                return false;
            }

            ajax.post({
                url: "/index.php/forget/forget_account",
                data: { "account": account },
                success: function(data) {
                    location.href = "./step2.html"
                    Util.cookie("forgetAccount", account)
                }
            })
        });

        var $column = $(".selectForm .column");
        var $step2Save = $(".step2-save-btn")
        $column.on("click", function() {
            $(this).find("input").prop("checked", true);
        })
        $step2Save.on("click", function() {
            var type = $(".tab").find(":checked").attr("sort");
            // Util.cookie("userInfo", { "forgetType": type })
            if (type == "phone") {
                location.href = "./step3_1.html"
            } else {
                location.href = "./step3_2.html"
            }
        });

        var $getPhoneCode = $(".get-phone-code");
        var $step31Btn = $(".step3-1-btn");
        var $phone = $(".phone-input-32");
        $getPhoneCode.on("click", function() {
            var _this = $(this)
            _this.prop("disebled", true)
            if (verify.verifyPhone($phone)) {
                ajax.get({
                    url: "/index.php/student/send_verify",
                    data: { "mobile": $phone.val() },
                    type: 3,
                    success: function() {
                        verify.getCode(_this)
                        _this.prop("disebled", false)
                    }
                })
            }
        })
        $step31Btn.on("click", function() {
            var account = Util.cookie("forgetAccount");
            var obj = {};
            obj.identify = $phone.val();
            obj.code = $(".phone-code").val();
            obj.account = account;
            if (verify.verifyPhone($phone)) {
                ajax.post({
                    url: "/index.php/student/verification_code",
                    data: obj,
                    success: function() {
                        location.href = "./step4.html"
                    }
                })
            }
        })

        var $emailNumber = $(".email-input83");
        $(".get-email-code").on("click", function() {

            var _this = $(this)
            _this.prop('disabled', true);
            if (verify.checkEmail($emailNumber)) {
                ajax.get({
                    url: "/index.php/student/send_email",
                    data: { "email": $emailNumber.val() },
                    type: 3,
                    success: function() {
                        verify.getCode(_this)
                        _this.prop("disebled", false)
                        $(".go-email-link").show();
                    }
                })
            }
        })
        $(".step3-email-btn").on("click", function() {
            var account = Util.cookie("forgetAccount");
            if (verify.checkEmail($emailNumber)) {
                var obj = {};
                obj.identify = $emailNumber.val();
                obj.code = $(".email-input23").val();
                obj.account = account;
                ajax.post({
                    url: "/index.php/student/verification_code",
                    data: obj,
                    success: function() {
                        location.href = "./step4.html"
                    }
                })
            }
        })

        var $step4Btn = $(".step4-btn");
        $step4Btn.on("click", function() {
            console.log("11")
            var p1 = $(".new-pass-word1").val();
            var p2 = $(".new-pass-word2").val();
            if (p1.length < 6 || p1.length < 5) {
                layer.msg("密码长度不能小于6位");
                return false;
            }
            if (p1 !== p2) {
                layer.msg("两次密码输入不一样");
                return false;
            }
            var obj = {};
            obj.password = p2;
            obj.account = Util.cookie("forgetAccount");
            console.log(obj);
            ajax.post({
                url: "/index.php/forget/change_password",
                data: obj,
                success: function(data) {
                    layer.msg("修改成功")
                    setTimeout(function() {
                        location.href = "/index.php/index/index"

                    }, 1000)
                }
            })
        })

    }
    return loadTip;
})