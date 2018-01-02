<?php if (!defined('THINK_PATH')) exit();?><head>
    <meta charset="UTF-8">
    <title><?php echo ($seoData["title"]); ?></title>
	<meta name="author" content="" />
    <meta name="keywords" content="<?php echo ($seoData["keywords"]); ?>">
    <meta name="description" content="<?php echo ($seoData["description"]); ?>">
    <link rel="stylesheet" href=/Tpl-website/pc/res/css/teacher/teacher.css>
    <script src="/Tpl-website/pc/res/js/lib/jquery-3.1.1.min.js"></script>
    <script src='/Tpl-website/pc/res/js/lib/require.js'></script>
    <script src="/Tpl-website/pc/res/js/lib/main.js"></script>
    <script src="/Tpl-website/pc/res/js/common.js"></script>
</head>
<body>

<div class="zr_header zr_layout">
    <div class="z_header_l">
        <div class="header-logo">
            <a href="<?php echo U('/');?>"></a>
        </div>
    </div>
    <div class="z_header_r">
        <div class="header_nav fl">
            <ul class="nav_bar">
                <li class="hover-nav">
                    <a class="nav-link" href="<?php echo U('/Vod');?>">全部课程</a>
                    <div class="mask"></div>
                    <div class="lesson-wrapper">
                        <ul class="clearfix">
                            <?php if(is_array($lesson_info)): foreach($lesson_info as $key=>$value): ?><li>
                                    <h3><?php echo ($value['zc_caption']); ?></h3>
                                    <?php if(is_array($value['small_lesson'])): foreach($value['small_lesson'] as $key=>$val): ?><a href="javascript:gotoUrl(<?php echo ($val['id']); ?>,<?php echo ($value["id"]); ?>)"><?php echo ($val['zc_caption']); ?></a><?php endforeach; endif; ?>
                                </li><?php endforeach; endif; ?>
                        </ul>
                    </div>
                </li>
                <li class="hover-nav nav-live">
                    <a class="nav-link" href="<?php echo U('/Live');?>">正在直播</a>
                    <div class="mask"></div>
                    <div class="lesson-wrapper">
                        <ul class="clearfix">
                            <?php if(is_array($lesson_info)): foreach($lesson_info as $key=>$value): ?><li>
                                    <h3><?php echo ($value['zc_caption']); ?></h3>
                                    <?php if(is_array($value['small_lesson'])): foreach($value['small_lesson'] as $key=>$val): ?><a href="javascript:goUrl(<?php echo ($val['id']); ?>,<?php echo ($value["id"]); ?>)"><?php echo ($val['zc_caption']); ?></a><?php endforeach; endif; ?>
                                </li><?php endforeach; endif; ?>
                        </ul>
                    </div>
                </li>
                <li>
                    <a class="nav-link" href="<?php echo U('/Student');?>">我的课程</a>
                </li>
                <li>
                    <a class="nav-link" href="<?php echo U('/Content/about');?>">关于我们</a>
                </li>
                <li>
                    <a class="nav-link" href="<?php echo U('/Teacher/login');?>">我是老师</a>
                </li>
            </ul>
        </div>
        <div class="header_search fl">
            <form class="search_form" action="<?php echo U('/Search/search_live');?>" type="get">
                <div class="h_select_wapper fl">
                    <div class="v_select fl">
                        <span class="h_course">直播</span>
                        <i class="h_triangle"></i>
                        <div class="select_s_type">
                            <span class="search_live">直播</span>
                            <span class="search_vod">录播</span>
                            <span class="search_teacher">老师</span>
                        </div>
                    </div>
                    <input class="header_select_input fl" placeholder="请输入" required="required" type="text" name="kw" id="kw">
                </div>
                <input class="select_input_icon fl" type="submit" value="搜索">
            </form>
        </div>
        <?php if($teacher_login > 0): ?><div class="fr nav_bar_r">
                <a href="<?php echo U('/Teacher');?>" class="user-info">
                <img class="user_picture" src="<?php echo ($member_info_teacher['zc_headimg']); ?>" alt="">
                <span class="user-name"><?php echo ($member_info_teacher["zc_nickname"]); ?></span>
            </a>
                <a class="signOut kbt-signOut" href="javascript:void(0)">退出</a>
            </div>
            <?php elseif($student_login > 0): ?>
            <div class="fr nav_bar_r">
                <a href="<?php echo U('/Student');?>" class="user-info">
                    <img class="user_picture" src="<?php echo ($member_info_student['zc_headimg']); ?>" alt="">
                    <span class="user-name"><?php echo ($member_info_student["zc_nickname"]); ?></span>
                </a>
                <a class="signOut kbs-signOut" href="javascript:void(0)">退出</a>
            </div>
            <?php else: ?>
            <a class="i_teacher h-n-login" href="javascript:void(0)">
            <span>您好，请登陆</span>
            <!-- <img class="teacher_pic" src="/Tpl-website/pc/res/images/teacher/t.png" alt=""> -->
        </a><?php endif; ?>
    </div>
</div>
<div class="s-sx-login md-effect-1">
    <div class="mask md-content ">
        <div class="login-box">
            <div class="tabs">
                <span class="active">使用账号登录</span>
                <span>使用绑定手机号登录</span>
            </div>
            <div class="account-login">
                <div class="l-a-border-left">
                    <input class="ipt account-btn " type="text" placeholder="请输入账号/身份证" value="">
                </div>
                <div class="l-a-border-left">
                <input class="ipt password-btn " type="password" placeholder="请输入密码" value="">
                 </div>
                <a class="forget-password" href="<?php echo U('/Forget/step1');?>">忘记密码?</a>
            </div>
            <div class="account-login register-form">
            <div class="l-a-border-left">
                <input value="" class="ipt account-btn phoneNumber" type="text" placeholder="请输入注册手机号码">
                </div>
                <div class="l-a-border-left" style="display: inline-block;">
                <input class="ipt password-btn code-text" type="text" placeholder="请输入手机验证码">
                </div>
                <input class="ipt send-code-btn" type="button" value="发送验证码">
            </div>
            <div class="rlf-group">
                <input class="go-login-btn btn" type="button" value="立即登录">
                <!-- <input  class="register-new-account btn" type="button" value="注册账号"> -->
                <a class="register-new-account btn" href="<?php echo U('/Student/register_step1');?>">注册账号</a>
            </div>
            <i class="close-login-form"></i>
        </div>
    </div>

</div>
  <div class="md-overlay"></div>
<script>
function goUrl(cat_id, fid) {
    var searchurl = '/index.php/Live/index/s/';
    var urlpara = "fid/" + encodeURIComponent(fid) + "/";
    urlpara += "cat_id/" + encodeURIComponent(cat_id) + "/";
    location.href = searchurl + base64encode(urlpara);
}

function gotoUrl(cat_id, fid) {
    var searchurl = '/index.php/Vod/index/s/';
    var urlpara = "fid/" + encodeURIComponent(fid) + "/";
    urlpara += "cat_id/" + encodeURIComponent(cat_id) + "/";
    location.href = searchurl + base64encode(urlpara);
}

$(function() {
    var search_live = $('.search_live');
    var h_course = $('.h_course');
    var search_vod = $('.search_vod');
    var search_teacher = $('.search_teacher');
    var search_form = $('.search_form');

    search_live.click(function() {
        h_course.html(search_live.html());
        search_form.attr('action', "<?php echo U('/Search/search_live');?>");
        $(".select_s_type").hide();
    })

    search_vod.click(function() {
        h_course.html(search_vod.html())
        search_form.attr('action', "<?php echo U('/Search/search_vod');?>");
        $(".select_s_type").hide();
    })

    search_teacher.click(function() {
        h_course.html(search_teacher.html())
        search_form.attr('action', "<?php echo U('/Search/index');?>");
        $(".select_s_type").hide();
    })

    $(".v_select").hover(function() {
        $(".select_s_type").show();
    }, function() {
        $(".select_s_type").hide();
    })
})

$(".kbs-signOut").on("click", function() {
    $.get("/index.php/Student/loginout", function(data) {
        if (data.status == 1) {
            window.location.href = data.url;
        }
    })
})
$(".kbt-signOut").on("click", function() {
    $.get("/index.php/Teacher/loginout", function(data) {
        if (data.status == 1) {
            window.location.href = data.url;
        }
    })
})
</script>
<script>
require(["login"], function(login) {
    login()
    $(".h-n-login").on("click", function() {
        $(".s-sx-login").addClass('md-show');
    })
});

/*输入框动画*/
$(".l-a-border-left .ipt").focus(function() {
    $(this).parent().addClass('active');
})
$(".l-a-border-left .ipt").blur(function() {
    $(this).parent().removeClass('active');
});
</script>


<div class="t_login">
    <div class="zr_layout login_wrapper">
        <i class="circle circle1"></i>
        <i class="circle circle2"></i>
        <i class="circle small circle7"></i>
        <i class="circle small circle3"></i>
        <i class="circle small circle4"></i>
        <i class="circle smallPic circle5"></i>
        <i class="circle smallPic circle6"></i>
        <div class="joinus clearfix">
            <h1>孜尔直播签约教师</h1>
            <h3 class="sub_text1">深耕本地教育模式</h3>
            <h3>直播与录播多种形式</h3>
            <a href="javascript:void(0)" class="joinusBtn joinusBt1"></a>
            <a href="<?php echo U('register_step1');?>" class="already_joinus">还不是签约教师?点击这里开始签约</a>
        </div>
    </div>
    <div class="login">
        <div class="loginbox">
               <i class="close"></i>
                <h3 class="login_text">登录后台</h3>
                <div class="account_box">
                    <i class="pass_icon1"></i>
                    <i class="pass_icon2"></i>
                    <input class="username" value="" style="margin-bottom: 23px;" type="text" placeholder="请输入账号或手机号码">
                    <input class="password" value="" type="password" placeholder="请输入密码">
                    <a href="<?php echo U('/Forget/step1');?>">忘记密码?</a>
                </div>
                <input class="login_btn" type="button" value="立即登录">
        </div>
    </div>
</div>
<footer class="zr_footer">
    <div class="zr_layout clearfix">
        <div class="zr_footer_l fl">
            <div class="logo"></div>
            <div class="siteinfo">
                <div class="phone clearfix">
                    <i class="phoneIcon fl"></i>
                    <div class='fl phone_number'>
                        <span><?php echo ($SET_CONFIG['CONTACT_TEL']); ?></span>
                        <span><?php echo ($SET_CONFIG['SERVER_TIME']); ?></span>
                    </div>
                    <input class="fl qq_server" type="button" value="">
                </div>
            </div>
        </div>
        <div class="f_line fl"></div>
        <div class="zr_footer_r fl">
            <div class=" two_dimension_code ">
                <img src="<?php echo ($SET_CONFIG['WEB_CODE']); ?>" alt="">
            </div>
            <div class="copyright clearfix">
                <div class="copyright_text">
                    <a href="<?php echo U('content/about');?>">关于我们</a>
                    <a href="<?php echo U('content/contact');?>">联系我们</a>
                    <a href="<?php echo U('content/help');?>">帮助中心</a>
                </div>
                <div class="copyright_text2"><?php echo ($SET_CONFIG['WEB_ICP']); ?></div>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
<script>
    require(["verify"], function (verify) {
        $(".joinusBt1").on("click",function(){
            $(".login").show();
        })
        $(".close").on("click",function(){
            $(".login").hide();
        })

        $(".login_btn").on("click", function() {
            $(".login_btn").val("正在登录...")
            $(".login_btn").prop("disabled",true)
            $.ajax({
                url: "<?php echo U('login');?>",
                type: 'POST',
                data: {
                    "type": 1,
                    "username": $(".username").val(),
                    "password": $(".password").val()
                },

                success: function (data) {
                    if (data.status == 0) {
                        // layer.msg(data.msg)
                        layer.alert(data.msg)
                        $(".login_btn").val("立即登录")
                        $(".login_btn").prop("disabled",false)
                        return false;
                    }
                    // 通过验证
                    window.location.href = "<?php echo U('index');?>";
                }
            })
        })
    })
</script>