<?php if (!defined('THINK_PATH')) exit();?><head>
    <meta charset="UTF-8">
    <title><?php echo ($seoData["title"]); ?></title>
	<meta name="author" content="" />
    <meta name="keywords" content="<?php echo ($seoData["keywords"]); ?>">
    <meta name="description" content="<?php echo ($seoData["description"]); ?>">
    <link rel="stylesheet" href="/Tpl-website/pc/res/css/student/student.css">
    <script src="/Tpl-website/pc/res/js/lib/jquery-3.1.1.min.js"></script>
    <script src='/Tpl-website/pc/res/js/lib/require.js'></script>
    <script src="/Tpl-website/pc/res/js/lib/main.js"></script>
    <!--<script type="text/javascript" src="/Tpl-website/pc/res/plugins/ckplayer/barrage.js" charset="utf-8"></script>-->
    <script type="text/javascript" src="/Tpl-website/pc/res/plugins/ckplayer/ckplayer.js" charset="utf-8"></script>
</head>

<body>
    <div class="broadcast-wapper">
        <button class="close-window-btn"><a href="<?php echo U('/Live/livedetail',array('tnid'=>$id));?>">退出</a></button>
        <div class="broadcast-right">
            <div class="course-title">
                <div class="class-hour">
                    <span class="class-text">课时</span>
                    <span class="no_number"><?php echo ($lesson_info["lesson_now"]); ?></span>
                </div>
                <h3>【<?php echo ($detailInfo["fid_label"]); ?>.<?php echo ($detailInfo["cat_id_label"]); ?>】</h3>
                <span><?php echo ($lesson_info["zc_title"]); ?></span>
                <div class="online-people-count">
                    <span>在线人数:</span>
                    <span class="count-text"><?php echo ($viewNum); ?>人</span>
                </div>
            </div>
            <div class="player-wapper">
                <div class="player-content tab1" id="player-content">
                    <script type="text/javascript">
                        var flashvars={
//                            f:'<?php echo $lesson_info['zc_pull_url'] ?>',
                            f:'/play.mp4',
                            c:0,
                            b:0,
                            fs:1,
                            p:1,
                            loaded:'loadedHandler'
                        };
                        var video=['/play.mp4'];
                        var params={bgcolor:'#FFF',allowFullScreen:true,allowScriptAccess:'always',wmode:'transparent',allowFullScreenInteractive:true};
                        CKobject.embed('/Tpl-website/pc/res/plugins/ckplayer/ckplayer.swf','player-content','ckplayer_a1','100%','100%',false,flashvars,video,params)
                    </script>
                </div>
                 <div style="display: none" class="player-content tab2" id="player-content">
                    <iframe style="width: 100%;height: 100%;" src="http://ow365.cn/?i=13502&furl=http://live-upload.oss-cn-shanghai.aliyuncs.com/2017-08-04/598410793dbbb.pptx" frameborder="0"></iframe>
                </div>
            </div>
        </div>
        <div class="broadcast-left">
            <div class="shrink"></div>
            <div class="slider-top">
                <img class="teacher-photo" src="<?php echo ($teacherInfo["zc_headimg"]); ?>" alt="">
                <div class="teacher-info">
                    <span>主讲人:</span>
                    <span style="padding-left: 5px; "><?php echo ($teacherInfo["zc_nickname"]); ?></span>
                    <p style="margin-top:5px;font-size:12px;color: #a1a1a1;"><?php echo ($teacherInfo["zc_school"]); ?></p>
                    <?php if(($teacherInfo["is_auth"]) == "1"): ?><i>认证教师</i><?php endif; ?>
                </div>
                <div class="tabs-screen active">
                    <i></i>
                    <div class="mask">
                    </div>
                    <img src="<?php echo ($teacherInfo["zc_headimg"]); ?>" alt="<?php echo ($teacherInfo["zc_nickname"]); ?>">
                    <span class="tabs-text">切换为PPT</span>
                </div>
            </div>
            <div class="forum-box">
                <div class="forum-title">
                    <i class="icon"></i>
                    <span>讨论区</span>
                </div>
                <div class="chat-bxo" >
                </div>
                <div class="send-sms">
                    <input class="sms-content" type="text" placeholder="请输入内容">
                    <button class="send-sms-btn live-send-msg" lesson_id="<?php echo ($lesson_info["id"]); ?>">发送</button>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    require(["studentBrodcast"], function(studentBrodcast) {
          studentBrodcast()
    })
    $(function(){
        talk();
        setTimeout('liveSum()',10000);

    })
    var lesson_id = <?php echo ($lesson_info["id"]); ?>;
    function liveSum(){
        $.ajax({
            type: "POST",
            url: "<?php echo U('/Live/liveSum');?>",
            data: "lesson_id="+lesson_id,
            success: function(msg){
                setTimeout('liveSum()',10000);
            }
        });
    }


    function talk(){
        var url = "<?php echo U('/Live/talk');?>";
        var talk =
        $.post(url,{
            "lesson_id" : lesson_id,
        },function(data){
            $('.chat-bxo').empty();
            $('.chat-bxo').append(data);
            setTimeout('talk()',9000);
        },'json')
    }
</script>