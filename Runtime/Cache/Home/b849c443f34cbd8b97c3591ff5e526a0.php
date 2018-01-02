<?php if (!defined('THINK_PATH')) exit();?><head>
    <meta charset="UTF-8">
    <title>个人资料</title>
	<meta name="author" content="" />
    <meta name="keywords" content="<?php echo ($seoData["keywords"]); ?>">
    <meta name="description" content="<?php echo ($seoData["description"]); ?>">
       <link rel="stylesheet" href="/Tpl-website/pc/res/css/student/student.css">
    <script src="/Tpl-website/pc/res/js/lib/jquery-3.1.1.min.js"></script>
     <script src="/Tpl-website/pc/res/js/lib/YMDClass.mini.js"></script>
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
                    <input class="header_select_input fl" placeholder="请输入"  required="required" type="text" name="kw" id="kw">
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

<div class="s-sx-login">
    <div class="mask">
        <div class="login-box">
            <div class="tabs">
                <span class="active">使用孜尔账号登录</span>
                <span>使用绑定手机号登录</span>
            </div>
            <div class="account-login">
                <input class="ipt account-btn " type="text" placeholder="请输入孜尔账号/身份证" value="313768239@qq.com">
                <input class="ipt password-btn " type="password" placeholder="请输入密码" value="123456">
                <a class="forget-password" href="<?php echo U('/Forget/step1');?>">忘记密码?</a>
            </div>
            <div class="account-login register-form">
                <input value="13631479553" class="ipt account-btn phoneNumber" type="text" placeholder="请输入注册手机号码">
                <input class="ipt password-btn code-text" type="text" placeholder="请输入手机验证码">
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


<script>
    function goUrl(cat_id,fid)
    {
        var searchurl='/index.php/Live/index/s/';
        var urlpara ="fid/"+encodeURIComponent(fid)+"/";
        urlpara +="cat_id/"+encodeURIComponent(cat_id)+"/";
        location.href=searchurl + base64encode(urlpara);
    }

    function gotoUrl(cat_id,fid)
    {
        var searchurl='/index.php/Vod/index/s/';
        var urlpara ="fid/"+encodeURIComponent(fid)+"/";
         urlpara +="cat_id/"+encodeURIComponent(cat_id)+"/";
        location.href=searchurl + base64encode(urlpara);
    }

    $(function(){
        var search_live = $('.search_live');
        var h_course = $('.h_course');
        var search_vod = $('.search_vod');
        var search_teacher = $('.search_teacher');
        var search_form = $('.search_form');

        search_live.click(function(){
            h_course.html(search_live.html());
            search_form.attr('action',"<?php echo U('/Search/search_live');?>");
            $(".select_s_type").hide();
        })

        search_vod.click(function(){
            h_course.html(search_vod.html())
            search_form.attr('action',"<?php echo U('/Search/search_vod');?>");
            $(".select_s_type").hide();
        })

        search_teacher.click(function(){
            h_course.html(search_teacher.html())
            search_form.attr('action',"<?php echo U('/Search/index');?>");
            $(".select_s_type").hide();
        })

        $(".v_select").hover(function(){
            $(".select_s_type").show();
        },function(){
            $(".select_s_type").hide();
        })
    })

    $(".kbs-signOut").on("click",function(){
        $.get("/index.php/Student/loginout",function(data){
            console.log(data)
            if(data.status == 1){
                window.location.href= data.url;
            }
        })
    })
    $(".kbt-signOut").on("click",function(){
        $.get("/index.php/Teacher/loginout",function(data){
            console.log(data)
            if(data.status == 1){
                window.location.href= data.url;
            }
        })
    })


</script>
<script>
      require(["login"], function (login) {
        login()
        $(".h-n-login").on("click",function(){
            $(".s-sx-login").show();
        })
    });

</script>



<div class="student-nav-wrapper">
    <div class="zr_layout clearfix">
        <div class="nav-bar">
            <ul class="fl">
                <li class="<?php echo ($index_active); ?>">
                    <a href="<?php echo U('/Student/index');?>">课堂首页</a>
                </li>
                <li class="<?php echo ($livecourse_active); ?>">
                    <a href="<?php echo U('/Student/livecourse');?>">直播课程</a>
                </li>
                <li class="<?php echo ($vodcourse_active); ?>">
                    <a href="<?php echo U('/Student/vodcourse');?>">录播课程</a>
                </li>
                <li class="<?php echo ($my_active); ?>">
                    <a href="<?php echo U('/Student/my');?>">个人资料</a>
                </li>
            </ul>
            <!--<div class="user-info fr">-->
                <!--<a class="user-name" href="<?php echo U('/Student/index');?>">-->
                    <!--<img src="<?php echo ($student_information['zc_headimg']); ?>" alt="">-->
                    <!--<span href=""><?php echo ($student_information['zc_nickname']); ?></span>-->
                <!--</a>-->
                <!--<a class="out" href="<?php echo U('/Student/LoginOut');?>">退出</a>-->
            <!--</div>-->
        </div>
    </div>
    <script>
        var $nav_link = $('.nav-bar').find('.active');
        var $index = $nav_link.index();
        var leftDistance = $nav_link.position().left;
        var NAVLEFT = 39;
        $(".student-nav-wrapper .nav-bar ul").append('<span class="sideline"></span>');
        $(".sideline").css({
            "left": leftDistance + NAVLEFT
        });

        $(".nav-bar li").hover(function() {
            $(".sideline").stop(true);
            $(".sideline").animate({
                left: $(this).position().left + NAVLEFT
            }, 300);
            $(this).addClass('active').siblings().removeClass("active");
        }, function() {
            $nav_link.addClass('active').siblings().removeClass("active");
            $(".sideline").animate({
                left: leftDistance + NAVLEFT
            }, 300);
        });
    </script>
</div>
    <div class="s-m-banner"></div>
    <div class="s-m-my-wrapper">
        <div class="zr_layout">
            <div class="s-my-contain">
                <div class="tabhead">
                    <button class="base-data active">资料信息</button>
                    <button class="safe-set">安全设置</button>
                </div>
                <div class="tab-contain">
                    <div class="tab tab-1 user-data">
                        <div class="base-data">
                            <div class="clearfix">
                                <span class="user-name"><?php echo ($student_msg['zc_nickname']); ?></span>
                                <div class="change-head-picture">
                                    <input accept="image/png,image/jpeg" type="file">
                                    <button>更换头像</button>
                                </div>
                            </div>
                            <div class="user-id">
                                <span>身份证号码:<?php echo ($student_msg['zc_idcard']); ?></span>
                            </div>
                            <div class="preview-picture">
                                <img src="<?php echo ($student_msg['zc_headimg']); ?>" alt="">
                                <span>建议尺寸:200*200</span>
                            </div>
                        </div>
                        <div class="other-data">
                            <div class="row">
                                <p class="caption" style="padding-top: 29px;">所在学校</p>
                                <div class="select-school">
                                    <div class="c-school">
                                        <input class="c-school" type="button" value="<?php echo ($student_msg['zc_school']); ?>">
                                    </div>
                                    <input class="write-school-input" type="text" placeholder="列表找不到？我来补充" value="<?php echo ($student_msg['zc_school']); ?>">
                                    <div class="school-contain">
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <p class="caption">性别</p>
                                <div class="select-sex">
                                    <label>
                                        <?php if($student_msg['zl_sex'] == 1): ?><input sex="1" type="radio" name="sex"  checked>
                                            <?php else: ?>
                                            <input sex="1"  type="radio" name="sex" ><?php endif; ?>
                                        <i></i> 我是男生
                                    </label>
                                    <label>
                                        <?php if($student_msg['zl_sex'] == 2): ?><input sex="2" type="radio" name="sex" checked>
                                            <?php else: ?>
                                            <input sex="2" type="radio" name="sex"><?php endif; ?>
                                        <i></i> 我是女生
                                    </label>
                                </div>
                            </div>
                            <div class="row clearfix">
                                <p class="caption">出生日期</p>
                                <div class=" select-born-date">
                                    <select class="select-year" default_year="<?php echo ($student_msg['year']); ?>" name="year1">
                                        <!--<option value="<?php echo ($student_msg['year']); ?>" selected><?php echo ($student_msg['year']); ?>年</option>-->
                                    </select>
                                    <select class="select-moon" default_month="<?php echo ($student_msg['month']); ?>" name="month1">
                                        <!--<option value="<?php echo ($student_msg['month']); ?>" selected><?php echo ($student_msg['month']); ?>月</option>-->
                                    </select>
                                    <select class="select-day" defaul_day="<?php echo ($student_msg['day']); ?>" name="day1">
                                        <!--<option value="<?php echo ($student_msg['day']); ?>" selected><?php echo ($student_msg['day']); ?></option>-->
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="save-btn">
                            <input class="save" type="button" value="保存">
                        </div>
                    </div>
                    <div class="tab tab-2 ">
                        <div class="modify-password">
                            <div class="row">
                                <p>原密码</p>
                                <input class="old-word" type="password" placeholder="请输入">
                            </div>
                            <div class="row">
                                <p>新密码</p>
                                <input class="new-word1" type="password" placeholder="请输入">
                            </div>
                            <div class="row">
                                <p>重复新密码</p>
                                <input class="new-word2" type="password" placeholder="请输入">
                            </div>
                        </div>
                        <div class="save-btn">
                            <input class="modify-password-btn" type="button" value="保存">
                        </div>
                    </div>
                </div>
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
<script>
require(["my"], function(my) {
   
    my()
})
</script>