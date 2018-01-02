<?php if (!defined('THINK_PATH')) exit();?><head>
    <meta charset="UTF-8">
    <title>老师详情</title>
	<meta name="author" content="" />
    <meta name="keywords" content="<?php echo ($seoData["keywords"]); ?>">
    <meta name="description" content="<?php echo ($seoData["description"]); ?>">
    <link rel="stylesheet" href="/Tpl-website/pc/res/css/student/student.css">
    <link rel="stylesheet" href="/Tpl-website/pc/res/plugins/swiper/swiper.css">
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



    <div class="h-teacher-detail-page">

        <div class="h-teacher-detail">
            <div class="h-teacher-wapper">
                <div class="h-teacher-info">
                    <div class="base-data clearfix">
                        <img src="<?php echo ($teacher_msg['zc_headimg']); ?>" alt="">
                        <div class="user-name">
                            <span class="text1">主讲人：</span>
                            <span class="text2"><?php echo ($teacher_msg['zc_nickname']); ?></span>
                            <p><?php echo ($teacher_msg['zc_school']); ?></p>
                            <span class="teacher-gold">认证教师</span>
                        </div>
                    </div>
                    <div class="statistics clearfix">
                        <div class="row">
                            <span class="count"><?php echo ($teacher_msg['live_num']); ?></span>
                            <span>直播课程</span>
                        </div>
                        <div class="row">
                            <span class="count"><?php echo ($teacher_msg['vod_num']); ?></span>
                            <span>录播课程</span>
                        </div>
                        <div class="row">
                            <span class="count"><?php echo ($teacher_msg['student_num']); ?></span>
                            <span>学生数</span>
                        </div>
                        <div class="row" style="border-right:none;">
                            <span class="count"><?php echo ($teacher_msg['favorite_num']); ?></span>
                            <span>收藏</span>
                        </div>
                    </div>
                    <div class="introduce">
                        <span>擅长：</span>
                        <span style="font-size: 13"><?php echo ($teacher_msg['zc_good_at']); ?></span>
                    </div>
                    <div class="introduce">
                        <span>简介：</span>
                        <span style="font-size: 13;"><?php echo ($teacher_msg['zc_intro']); ?></span>
                    </div>
                </div>
                <div class="backgroung-icon-flower"></div>
                <div class="backgroung-icon-book"></div>
                <div class="backgroung-icon-lamp"></div>
                <div class="backgroung-icon-mark"></div>
            </div>
        </div>
        <div class="h-teacher-course-content">
            <div class="zr_layout">
                <div class="course-tabs-top">
                    <button class="oLi active">录播课程</button>
                    <button class="oLi">历史直播</button>
                </div>

                <div class="course-tabs-content">
                    <div class="tabs-contain recorded-course-tabs1">
                        <h3>课程(<?php echo ($count); ?>)</h3>
                        <?php if(is_array($vod_msg)): foreach($vod_msg as $key=>$value): ?><div class="record-list row">
                            <div class="big-course">
                                <img class="course-picture" src="<?php echo ($value['zc_image']); ?>" alt="">
                                <div class="descript">
                                    <h3><?php echo ($value['zn_fid_name']); ?>.<?php echo ($value['zn_cat_name']); ?></h3>
                                    <p>勾股定理的快捷算法</p>
                                    <div>
                                        <span>浏览量:<?php echo ($value['pageviews']); ?></span>
                                        <span>视频数:<?php echo ($value['count']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="small-course swiper-container">
                                <ul class="swiper-wrapper">
                                    <?php if(is_array($value['vod_lesson'])): foreach($value['vod_lesson'] as $key=>$val): ?><li class="swiper-slide">
                                        <a href="">
                                            <div class="card">
                                                <div class="mask"></div>
                                                <img src="<?php echo ($value['zc_image']); ?>" alt="">
                                                <h3><?php echo ($key + 1); ?></h3>
                                                <i>节课</i>
                                                <div class="main-teacher-name">
                                                    主讲人: <?php echo ($teacher_msg['zc_nickname']); ?>
                                                </div>
                                            </div>
                                            <div class="course-descript">
                                                <p>【<?php echo ($value['zn_fid_name']); ?>.<?php echo ($value['zn_cat_name']); ?>】<?php echo ($val['lesson_title']); ?></p>
                                                <span><?php echo ($teacher_msg['zc_school']); ?></span>
                                            </div>
                                        </a>
                                    </li><?php endforeach; endif; ?>
                                </ul>
                            </div>
                        </div><?php endforeach; endif; ?>
                        <div  class="list_page" style="position:relative;">
                            <?php echo ($page); ?>
                        </div>
                    </div>
                    <div class="tabs-contain recorded-course-tabs2">
                        <?php if($live_msg): ?><ul class="clearfix">
                            <?php if(is_array($live_msg)): foreach($live_msg as $key=>$value): if($value['zl_status'] == 6): ?><li>
                            <!-- 未完结需要加 finish类名来改变状态-->
                                <div class="course-status ">
                                    报名：<?php echo ($value['enroll']); ?>
                                </div>
                                <a href="<?php echo U('/Live/livedetail',array('tnid' => $value['id']));?>">
                                    <div class="picture-box">
                                        <img src="<?php echo ($value['zc_image']); ?>" alt="">
                                        <p>主讲人:<?php echo ($teacher_msg['zc_nickname']); ?></p>
                                    </div>
                                    <div class="course-descript">
                                        <p><?php echo ($value['zc_title']); ?></p>
                                        <div class="time">
                                            <span><?php echo ($value['time']); ?></span>
                                            <!--<span>18:00</span>-->
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <?php else: ?>
                            <li>
                                <div class="course-status finish">
                                    已完结
                                </div>
                                <a href="<?php echo U('/Live/livedetail',array('tnid' => $value['id']));?>">
                                    <div class="picture-box">
                                        <img src="<?php echo ($value['zc_image']); ?>" alt="">
                                        <p>主讲人:<?php echo ($teacher_msg['zc_nickname']); ?></p>
                                    </div>
                                    <div class="course-descript">
                                        <p><?php echo ($value['zc_title']); ?></p>
                                        <div class="time">
                                            <span><?php echo ($value['time']); ?></span>
                                            <!--<span>18:00</span>-->
                                        </div>
                                    </div>
                                </a>
                            </li><?php endif; endforeach; endif; ?>
                        </ul>
                        <div  class="list_page" style="position:relative;">
                            <?php echo ($page_live); ?>
                        </div>
                            <?php else: ?>
                            <!-- 没有数据的时候  -->
                            <!-- 没有数据的时候  -->
                            <!-- 没有数据的时候  -->
                            <!-- 没有数据的时候  -->
                            <div>暂无数据</div><?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
<script>
var nav_w = $(".course-tabs-top li").first().width();
var $slideline = $(".course-tabs-top .line")
$slideline.width(nav_w);
$slideline.css("left", "61")
$(".course-tabs-top li").on('click', function() {
    nav_w = $(this).width();
    $slideline.stop(true);
    $slideline.animate({ left: $(this).position().left + 30 }, 300);
    $slideline.animate({ width: nav_w });
    //tab切换
    var index = $(this).index();
    $(".course-tabs .tabs").eq(index).fadeIn().siblings().fadeOut();
});
var $oLi = $(".course-tabs-top .oLi");
var $tabsContain = $(".tabs-contain")
$oLi.on("click",function(){
    var index = $(this).index();
    $tabsContain.eq(index).show().siblings().hide();
    $(this).addClass('active').siblings().removeClass("active")
})
</script>
<script>
require(["teacherdetail"], function(teacherdetail) {
    teacherdetail()
})
</script>