<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>sender</title>
</head>
<body>
<strong id="count"></strong>
<h1 id="target"></h1>
</body>
</html>
<script src="http://cdn.bootcss.com/jquery/3.1.0/jquery.min.js"></script>
<script src='http://cdn.bootcss.com/socket.io/1.3.7/socket.io.js'></script>
<script>
    jQuery(function ($) {

        // 连接服务端
        var socket = io('http://127.0.0.1:2120'); //这里当然填写真实的地址了
        // uid可以是自己网站的用户id，以便针对uid推送以及统计在线人数
        uid = <?php echo ($uid); ?>;
        console.log(uid);
        // socket连接后以uid登录
        socket.on('connect', function () {
            console.log('连接成功')
            socket.emit('login', uid);
        });

        ////前端需要通知其它用户时调用接口通知
        $.post("<?php echo U('join_room');?>");

        // 后端推送来消息时,type=>类型 data=>数据
        socket.on('new_msg', function (msg) {
            var data = $.parseJSON(msg);
            console.log(data)
            //alert(data.type);
            $('#target').append(msg).append('<br>');
        });
        // 后端推送来在线数据时
        socket.on('update_online_count', function (online_stat) {
            console.log(online_stat);
            $('#count').html(online_stat);
        });

    })

</script>