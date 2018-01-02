define(["layer"], function(layer) {
    var myAjx = {};
    myAjx.get = function(url, data, successFunc){
        $.ajax({
            url: url,
            type: "GET",
            data: data,
            success: function(data) {
                if (data.status == 0) {
                    layer.msg(data.msg)
                    return;
                }
                successFunc && successFunc(data);
            }
        })
    }
    var postLoad;
    myAjx.post = function(url, data, successFunc){
        console.log(url,data,successFunc)
        $.ajax({
            url: url,
            type: "POST",
            beforeSend: function() {
                 postLoad = layer.load(1, {
                    shade: [0.1, '#fff'] //0.1透明度的白色背景
                });
            },
            data: data,
            success: function(data) {
                
                layer.close(postLoad)
                layer.msg(data.msg)
                successFunc && successFunc(data);
            }
        })
    }
    return myAjx
})
