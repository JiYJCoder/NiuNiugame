/*表单验证*/


var verify = {};
verify.empty = function(_this,str){
    str = str || "请输入";
    var value = _this.val()
    if(value.length == 0){
        layer.msg(str)
        return false;
    } else{
        return true;
    }
}
verify.verifyPhone = function(_this, success, error) {
    var value = _this.val()
    let lawful = /^(0[0-9]{2,3}[-]?)?[0-9]{7,8}(-[0-9]{1,8})?$/.test(value) || /^1[3578]{1}[0-9]{9}$/.test(value);
    if (lawful) {
        success && success()
        return true;
    } else {
        layer.msg('请输入正常的手机号码')
        error && error()
        return false;
    }
}

verify.verifyUser = function(_this, success, error) {
    //要求3-8位由字母、数字、_或汉字组成
    var reg = /^[\u4e00-\u9fff\w]{2,8}$/;
    var value = _this.val()
    if (value.length == "") {
        layer.msg('请输入用户名')
        return false;
    } else if (!reg.test(value)) {
        layer.msg('请输入2-8位字母组成的用户名')
        return false
    } else {
        return true;
    }
}
verify.checkEmail = function(_this, success, error) {
    //验证邮箱号码
    var reg =  /^[A-Za-z\d]+([-_.][A-Za-z\d]+)*@([A-Za-z\d]+[-.])+[A-Za-z\d]{2,4}$/; 
    var value = _this.val()
    if (reg.test(value)) {
        success && success()
        return true;
    } else {
        layer.msg('请输入正确邮箱号码')
        error && error()
        return false;
    }
}
verify.checkID= function(_this, success, error) {
    //身份证
    var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;  
    var value = _this.val()
    if (reg.test(value)) {
        success && success()
        return true;
    } else {
        layer.msg('请输入正确身份证号码')
        error && error()
        return false;
    }
}



var _wait = 60;
verify.getCode = function(Btn, className) {
    if (_wait == 0) {
        Btn.removeClass(className)
        Btn.removeAttr("disabled");
        Btn.val('获取验证码')
        _wait = 60;
    } else {
        Btn.addClass(className)
        Btn.attr("disabled", true);
        Btn.val(_wait + " S")
        _wait--;
        setTimeout(function() {
                verify.getCode(Btn, className)
            },
            1000)
    }
}
define(["layer"],function(layer){
    return verify
})
