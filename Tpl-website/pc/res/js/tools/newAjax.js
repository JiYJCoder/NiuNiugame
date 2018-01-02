/*
1.请求之前没有提示，成功后没有提示。
2.请求之前有提示，成功后有提示。
3.请求之前没有提示，成功后有提示。
*/

var ajax = {};
ajax.get = function(option) {
    var loading;
    var url = option.url;
    var data = option.data;
    var success = option.success;
    var type = option.type || 1;
    var beforemsg = option.beforemsg || "加载中...";
    var beforeSent = option.beforeSent;

    $.ajax({
        url: url,
        data: data,
        type: "GET",
        beforeSend: function() {
            if (type == 2) {
                loading = layer.msg(beforemsg, { time: 2000000 })
            }
            beforeSent && beforeSent();
        },
        success: function(data) {
            // 请求发生失败
            if (data.status == 0) {
                layer.msg(data.msg)
                return false;
            }
            // 请求成功后信息提示

            if (type == 2 && data.status !== 0) {
                layer.msg(data.msg)
            }

            if (type == 3 && data.status !== 0) {
                layer.msg(data.msg)
            }

            success && success(data);

        }
    })
};

ajax.post = function(option) {
    var loading;
    var url = option.url;
    var data = option.data;
    var success = option.success;
    var type = option.type || 1;
    var beforemsg = option.beforemsg || "加载中...";

    $.ajax({
        url: url,
        data: data,
        type: "POST",
        beforeSend: function() {
            if (type == 2) {
                loading = layer.msg(beforemsg, { time: 2000000 })
            }
        },
        success: function(data) {
            console.log(data)

            // 请求发生失败
            if (data.status == 0) {
                layer.msg(data.msg)
                return false;
            }
            // 请求成功后信息提示

            if (type == 2 && data.status !== 0) {
                layer.msg(data.msg)
            }

            if (type == 3 && data.status !== 0) {
                layer.msg(data.msg)
            }

            success && success(data);

        }
    })
};
if (typeof module !== 'undefined' && typeof exports === 'object') {
    module.exports = ajax;
} else if (typeof define === 'function' && (define.amd || define.cmd)) {
    define(["layer"], function(layer) {
        return ajax;
    })
} else {
    window.ajax = ajax;
}