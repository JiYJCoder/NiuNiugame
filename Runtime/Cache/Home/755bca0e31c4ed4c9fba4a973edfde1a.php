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
    <div class="detail-wrapper">
        <div class="zr_layout" style="margin-bottom: 20px;">
            <div class="live-detail-top">
            <!--     <div class="live-detail-bread">
                    <span><?php echo ($detailInfo["fid_label"]); ?></span>
                    <span>></span>
                    <span><?php echo ($detailInfo["cat_id_label"]); ?></span>
                </div> -->
                <div class="play-info">
                    <a  class="video-info">
                        <img class="video-bg-picture" src="<?php echo ($detailInfo["zc_image"]); ?>" alt="">
                        <div class="mask"></div>
                        <div class="playe-video-icon" teacherId="<?php echo ($teacherInfo["id"]); ?>" liveId="<?php echo ($detailInfo["id"]); ?>"></div>
                        <div class="describe">
                            <h3 class="title-video"><?php echo ($detailInfo["fid_label"]); ?>.<?php echo ($detailInfo["cat_id_label"]); ?></h3>
                            <h4 class="sub-title"><?php echo ($detailInfo["zc_title"]); ?></h4>
                            <div class="surplus-course">
                                <span>剩余课节:</span>
                                <span style="color: #e99201"><?php echo ($detailInfo["lesson_left"]); ?></span>
                            </div>
                        </div>
                    </a>
                    <div class="teaher-info" >
                        <div class="teaher-info-card">
                            <div class="card-top clearfix">
                                <img class="teacher-hander-pictrue" src="<?php echo ($teacherInfo["zc_headimg"]); ?>" alt="">
                                <div class="teacher-name">
                                    <span>主讲人:</span>
                                    <a href="<?php echo U('/Search/teacherdetail',array('tnid'=>$teacherInfo['id']));?>"><?php echo ($teacherInfo["zc_nickname"]); ?></a>
                                    <?php if(($teacherInfo["is_auth"]) == "1"): ?><span class="identification">认证教师</span><?php endif; ?>
                                    <p><?php echo ($teacherInfo["zc_school"]); ?></p>
                                </div>
                            </div>
                            <div class="card-statistics">
                                <div class="item">
                                    <span class="number"><?php echo ($teacherInfo["student_num"]); ?></span>
                                    <span>学生数</span>
                                </div>
                                <div class="item">
                                    <span class="number"><?php echo ($teacherInfo["live_num"]); ?></span>
                                    <span>直播课程</span>
                                </div>
                                <div class="item">
                                    <span class="number"><?php echo ($teacherInfo["vod_num"]); ?></span>
                                    <span>录播课程</span>
                                </div>
                            </div>
                        </div>
                        <div class="teacher-live-descript">
                            <div class="teacher-live-time">
                                <span class="recently-live-title">上一节课直播时间</span>
                                <span><?php echo ($detailInfo["newest_time_date"]); ?></span>
                                <span><?php echo ($detailInfo["newest_time_hour"]); ?></span>
                            </div>
                            <p class="course-descript">课程描述：<?php echo ($detailInfo["zc_summary"]); ?></p>
                            <div class="fc">
                                <?php if(($detailInfo["is_enroll"]) == "1"): ?><a class="btn sign-up active" href="javascript:;" val="<?php echo ($detailInfo["id"]); ?>">已报名</a>
                                    <?php else: ?>
                                    <a class="btn sign-up enroll" href="javascript:;" val="<?php echo ($detailInfo["id"]); ?>">立即报名</a><?php endif; ?>
                                <?php if(($detailInfo["is_fav"]) == "1"): ?><a href="javascript:;" class="btn collect "  val="<?php echo ($detailInfo["id"]); ?>">已收藏</a>
                                    <?php else: ?>
                                    <a  href="javascript:;" class="btn collect active"  val="<?php echo ($detailInfo["id"]); ?>">立即收藏</a><?php endif; ?>

                            </div>
                        </div>
                        <span class="small-round round1"></span>
                        <span class="small-round round2"></span>
                        <span class="small-round round3"></span>
                        <span class="small-round round4"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="zr_layout">
            <div class="live-detail-content">
                <div class="course-tabs-top">
                    <ul>
                        <li>
                            课程简介
                        </li>
                        <li>
                            课程表
                        </li>
                        <li <?php if(($detailInfo["is_enroll"]) == "1"): ?>isclick=1<?php else: ?>isclick="0"<?php endif; ?>>
                            资料下载
                        </li>
                    </ul>
                    <div class="line">
                        <i></i>
                    </div>
                </div>
                <div class="course-tabs">
                    <ul class="clearfix">
                        <li class="tabs tabs-course-synopsis">
                            <?php echo (lq_format_content($detailInfo["zc_content"])); ?>
                        </li>
                        <li class="tabs tabs-course-table">

                            <!-- 未参加的添加no-join-course类名 -->
                            <?php if(is_array($detailInfo["lessonInfo"])): $i = 0; $__LIST__ = $detailInfo["lessonInfo"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$lesson): $mod = ($i % 2 );++$i;?><div class="row <?php if(($lesson["is_study"]) != "1"): ?>no-join-course<?php endif; ?>">
                                <!--已经报名的才显示状态-->
                                <?php if(($detailInfo["is_enroll"]) == "1"): if($lesson['url']): ?><a href="<?php echo ($lesson['url']); ?>" class="playback">查看回放</a><?php endif; ?>
                                    <div class="join">
                                        <i class="join-icon"></i>
                                        <?php if(($lesson["is_study"]) != "1"): ?><span>未參加</span><?php else: ?>
                                            <span>已參加</span><?php endif; ?>
                                    </div><?php endif; ?>
                                <div class="row-round">
                                    <div class="line"></div>
                                    <div class="round-heart"></div>
                                </div>
                                <div class="course-title">
                                    <h3><?php echo ($lesson["lesson_title"]); ?></h3>
                                    <span><?php echo ($lesson["zc_date"]); ?></span>
                                    <span><?php echo ($lesson["zc_start_time"]); ?>-<?php echo ($lesson["zc_end_time"]); ?></span>
                                </div>
                            </div><?php endforeach; endif; else: echo "" ;endif; ?>

                        </li>
                        <li class="tabs tabs-course-download">
                            <?php if(is_array($detailInfo["oss_file"])): $i = 0; $__LIST__ = $detailInfo["oss_file"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ossFile): $mod = ($i % 2 );++$i;?><a class="item file-type-<?php echo ($ossFile["style"]); ?>" download="filename"  href="<?php echo ($ossFile["zc_file_path"]); ?>">
                                <span class="time"><?php echo (date("Y-m-d",$ossFile["zn_cdate"])); ?></span>
                                <p class="file-name"><?php echo ($ossFile["zc_file_name"]); ?></p>
                            </a><?php endforeach; endif; else: echo "" ;endif; ?>

                        </li>
                    </ul>
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
 require(["live"], function(live) {
    live()
})
</script>
<script>
var nav_w = $(".course-tabs-top li").first().width();
var $slideline = $(".course-tabs-top .line")
$slideline.width(nav_w);
$slideline.css("left", "61")
$(".course-tabs-top li").on('click', function() {
    var index = $(this).index();
    var isclick = $(this).attr("isclick")
    
    if(index == 2 && isclick ==0) {
        layer.msg("请报名课程")
        return false;
    }

    nav_w = $(this).width();
    $slideline.stop(true);
    $slideline.animate({ left: $(this).position().left + 30 }, 300);
    $slideline.animate({ width: nav_w });

    //tab切换
    $(".course-tabs .tabs").eq(index).fadeIn().siblings().fadeOut();
});

</script>