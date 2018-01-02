<?php if (!defined('THINK_PATH')) exit();?><head>
    <meta charset="UTF-8">
    <title><?php echo ($seoData["title"]); ?></title>
	<meta name="author" content="" />
    <meta name="keywords" content="<?php echo ($seoData["keywords"]); ?>">
    <meta name="description" content="<?php echo ($seoData["description"]); ?>">
    <link rel="stylesheet" href="/Tpl-website/pc/res/plugins/swiper/swiper.css">
    <link rel="stylesheet" href=/Tpl-website/pc/res/css/student/student.css>
    <script src="/Tpl-website/pc/res/js/lib/jquery-3.1.1.min.js"></script>
    <script src="https://cdn.bootcss.com/Swiper/3.4.2/js/swiper.min.js"></script>
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
    <!-- <div class="s-sx-login">
    <div class="mask">
        <div class="login-box">
            <div class="tabs">
                <span class="active">使用孜尔账号登录</span>
                <span>使用绑定手机号登录</span>
            </div>
            <div class="account-login">
                <input class="ipt account-btn has_boxshaw" type="text" placeholder="请输入孜尔账号/身份证" value="313768239@qq.com">
                <input class="ipt password-btn has_boxshaw" type="password" placeholder="请输入密码" value="123456">
                <a class="forget-password" href="<?php echo U('/Forget/step1');?>">忘记密码?</a>
            </div>
            <div class="account-login register-form">
                <input value="13631479553" class="ipt account-btn phoneNumber" type="text" placeholder="请输入注册手机号码">
                <input class="ipt password-btn code-text" type="text" placeholder="请输入手机验证码">
                <input class="ipt send-code-btn" type="button" value="发送验证码">
            </div>
            <div class="rlf-group">
                <input class="go-login-btn btn" type="button" value="立即登录">
                <input class="register-new-account btn" type="button" value="注册账号">
            </div>
            <i class="close-login-form"></i>
        </div>
    </div>
</div> -->
    <div class="s-sx-banner">
        <div class="zr_layout">
            <div class="logo-wrapper">
                <!--  已经登录的状态-->
                <?php if($student_message['id'] > 0): ?><div class="Logged-in">
                        <a class="user-info" href="<?php echo U('/Student');?>">
                        <img src="<?php echo ($student_message['zc_headimg']); ?>" alt="">
                        <span class="user-name"><?php echo ($student_message['zc_nickname']); ?></span>
                    </a>
                        <div class="s-statistics clearfix">
                            <div class="enroll-count enroll-count-left">
                                <span><?php echo ($student_message['enroll']); ?></span>
                                <span>报名数</span>
                            </div>
                            <div class="enroll-count">
                                <span><?php echo ($student_message['favorite']); ?></span>
                                <span>收藏</span>
                            </div>
                        </div>
                        <a class="s-my-course" href="<?php echo U('/Student/livecourse');?>">我的课程</a>
                        <?php if($student_message['live_id'] > 0): ?><a href="<?php echo U('/Live/livedetail',array('tnid' => $student_message['zn_cat_id']));?>" class="recently-broadcast">
                            <span class="recently-broadcast-icon">最近直播</span>
                            <span href="" style="width:120px;"><?php echo ($student_message['zc_title']); ?></span>
                            <span class="right-number">></span>
                           <div>
                                <span class="date"><?php echo ($student_message['zc_date']); ?></span>
                            <span class="time"><?php echo ($student_message['zc_start_time']); ?></span>
                           </div>
                        </a><?php endif; ?>
                    </div>
                    <!--  没有登录的状态-->
                    <?php else: ?>
                    <div class="login-status">
                        <div class="login-pic"></div>
                        <a class="h-login-btn login-btn">登录</a>
                    </div><?php endif; ?>
            </div>
        </div>
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <?php if(is_array($ad_big)): foreach($ad_big as $key=>$value): ?><div class="swiper-slide">
                        <a href="<?php echo ($value['url']); ?>">
                            <!-- <img src="<?php echo ($value['image']); ?>" alt=""> -->
                            <!-- <div style="background: url( <?php echo ($value['image']); ?> )"></div> -->
                            <div class="item-pic-02" style="background-image: url(<?php echo ($value['image']); ?>)"></div>
                        </a>
                    </div><?php endforeach; endif; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
    <div class="s-sx-main">
        <?php if($ad): ?><div class="advert-bar zr_layout">
                <ul>
                    <?php if(is_array($ad)): foreach($ad as $key=>$value): ?><li>
                            <a href="<?php echo ($value['url']); ?>">
                        <img src="<?php echo ($value['image']); ?>" alt="" title="<?php echo ($value['title']); ?>">
                    </a>
                        </li><?php endforeach; endif; ?>
                </ul>
            </div><?php endif; ?>
        <div class="s-sx-foreshow-live zr_layout">
            <div class="foreshow-left-box">
                <h3>今天直播预告</h3>
                <div class="foreshow-date-box">
                    <span class="week"><?= "星期" . C('CAPITAL_WEEK')[date('w')] ?></span>
                    <div class="yg-live-date">
                        <span><?= date('m') ?></span>
                        <span>.&nbsp</span>
                        <span class="span3"><?= date('d') ?></span>
                    </div>
                </div>
            </div>
            <div class="foreshow-right-box">
                <div class="swiper-container">
                    <div class="swiper-wrapper yb-swiper-wrapper">
                        <?php if(is_array($today_live)): foreach($today_live as $key=>$value): ?><div class="swiper-slide yb-live-swiper" live_id="<?php echo ($value['id']); ?>">
                                <div class="yg-live-item">
                                    <a href="<?php echo U('/Live/livedetail',array('tnid' => $value['live_id']));?>" class="item-link has-item-boxshadow">
                                    <img class="page-pic" src="<?php echo ($value['zc_image']); ?>" alt="">
                                    <h5><?php echo ($value['lesson_title']); ?></h5>
                                </a>
                                    <div class="line"></div>
                                    <span class="show-live-time"><?php echo ($value['zc_start_time']); ?>-<?php echo ($value['zc_end_time']); ?></span>
                                    <span class="color999"><?php echo ($value['zc_school']); ?></span>
                                    <span class="color999"><?php echo ($value['zc_nickname']); ?></span>
                                </div>
                            </div><?php endforeach; endif; ?>
                        <script>
                        $(function() {
                            var len = $(".yb-live-swiper").length;
                            var forEachCount = 4 - len;
                            var SwiperItemHtml = "";

                            if (forEachCount <= 0) {
                                return false;
                            }

                            for (var i = 0; i < forEachCount; i++) {
                                SwiperItemHtml += '<div class="swiper-slide"><div class="yg-live-item"><a href="javascript:void(0)" class="item-link"><div class="no-live-pic"></div></a></div></div>'
                            }
                            $(".yb-swiper-wrapper").append(SwiperItemHtml)

                            var foreshowSwiperLen = $(".foreshow-right-box .swiper-slide").length;
                            if (foreshowSwiperLen <= 4) {
                                $(".swiper-button").hide();
                            }
                        })
                        </script>
                    </div>
                    <div class="top-line"></div>
                </div>
                <div class="swiper-button swiper-button-prev"></div>
                <div class="swiper-button swiper-button-next"></div>
            </div>
        </div>
        <!-- 中间 遍历开始 -->
        <div class="s-sx-course zr_layout">
            <?php if(is_array($lesson_message)): $key = 0; $__LIST__ = $lesson_message;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$value): $mod = ($key % 2 );++$key;?><div class="course-wapper course-wapper<?php echo ($key); ?>">
                    <div class="course-title">
                        <h3><?php echo ($value['zc_caption']); ?></h3>
                        <ul class="course-title-middle tab-header">
                            <?php if(is_array($value['DRs'])): $k = 0; $__LIST__ = $value['DRs'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($k % 2 );++$k; if($k == 1): ?><li class="active"><?php echo ($val['zc_caption']); ?></li>
                                    <?php else: ?>
                                    <li><?php echo ($val['zc_caption']); ?></li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                        <div class="course-title-right">
                            <a href="javascript:gotoUrl(0,<?php echo ($value['id']); ?>)">查看全部</a>
                            <i>></i>
                        </div>
                    </div>
                    <div class="course-content">
                        <?php if(is_array($value['DRs'])): $i = 0; $__LIST__ = $value['DRs'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><ul class="clearfix tab-contain tab-contain1">
                                <?php if(is_array($val['thr'])): $i = 0; $__LIST__ = $val['thr'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><li class="list-coures" vod_id="<?php echo ($v['id']); ?>">
                                        <a href="<?php echo U('/Vod/videodetail',array('tnid' => $v['id']));?>" class="course-item-link">
                                            <div class="mask">
                                                <p class="course-describe">
                                                    <?php echo ($v['zc_summary']); ?>
                                                </p>
                                                <img class="course-picture" src="<?php echo ($v['zc_image']); ?>" alt="">
                                            </div>
                                            <h5><?php echo ($v['zc_title']); ?></h5>
                                            <span class="course-count"><?php echo ($v['all_class']); ?>课时</span>
                                        </a>
                                        <div class="item-first-row clearfix">
                                            <span><?php echo ($v['favorite']); ?>人收藏</span>
                                            <span style="margin-left:13px;"><?php echo ($v['enroll']); ?>人报名</span>
                                        </div>
                                        <div class="item-second-row clearfix">
                                            <span><?php echo ($v['zc_teacher_name']); ?></span>
                                            <span style="margin-left:16px;"><?php echo ($v['school']); ?></span>
                                        </div>
                                    </li><?php endforeach; endif; else: echo "" ;endif; ?>
                            </ul><?php endforeach; endif; else: echo "" ;endif; ?>
                    </div>
                </div><?php endforeach; endif; else: echo "" ;endif; ?>
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
require(["studentIndex"], function(studentIndex) {
    studentIndex()
});

$(function() {
    $('.register-new-account').click(function() {
        var url = "<?php echo U('student/register_step1');?>";
        location.href = url;
    })
})
</script>