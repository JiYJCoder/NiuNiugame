<?php if (!defined('THINK_PATH')) exit();?><head>
    <meta charset="utf-8">
    <title>
        直播
    </title>
	<meta name="author" content="" />
    <link href="/Tpl-website/pc/res/css/student/student.css" rel="stylesheet">
    <script src="/Tpl-website/pc/res/js/lib/jquery-3.1.1.min.js"></script>
    <script src="/Tpl-website/pc/res/js/common.js"></script>
    <script src="/Tpl-website/pc/res/js/lib/require.js"></script>
    <script src="/Tpl-website/pc/res/js/lib/main.js"></script>
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
    <div class="s-sn-recordlist" class_url="Live">
     <div class="zr_layout">
         <?php if($is_login == 1): ?><div class="z-xr-live-header">
                <div class="contain">
                 <!--    <div class="left">
                        <div class="time">
                            <p class="lock"><?php echo date('H:i')?></p>
                            <div class="date">
                                <span><?php echo date('Y年m月')?></span>
                                <span>周<?php echo C('CAPITAL_WEEK')[date('w')]?></span>
                            </div>
                        </div>
                        <div class="lesson">
                            <div class="hang-in-the-lesson">
                                <div class="text">今日未上课</div>
                                <span><?php echo ($today_count); ?></span>
                            </div>
                        </div>
                          <div class="ling"></div>
                    </div> -->
                    <div class="left">
                        <div class="ready-live-text">
                            <i></i>
                            <span>准备开始的直播</span>
                        </div>
                        <div class="live-time">
                              <div class="clock"><?php echo date('H:i')?></div>
                              <div class="live-date">
                                  <span><?php echo date('Y年m月')?></span>
                                  <span>星期<?php echo C('CAPITAL_WEEK')[date('w')]?></span>
                              </div>
                        </div>
                        <div class="animate-wave">
                            <div class="deep-color-wave">
                                <div class="wave"></div>
                                <div class="wave"></div>
                            </div>
                            <div class="linght-color-wave">
                                <div class="wave"></div>
                                <div class="wave"></div>
                            </div>
                        </div>
                    </div>
                    <div class="right">
                        <?php if(($today_data["0"]["zn_object_id"]) != ""): ?><div class="swiper-button swiper-button-prev"></div>
                        <div class="swiper-button swiper-button-next"></div><?php endif; ?>
                        <div class="live-list s-sr-live-list swiper-container">
                            <ul class="clearfix swiper-wrapper">
                                <?php if(($today_data["0"]["zn_object_id"]) != ""): if(is_array($today_data)): foreach($today_data as $key=>$value): if($value['living'] == 1): ?><!--正在直播-->
                                <li class="swiper-slide">
                                    <a href="<?php echo U('/Live/livedetail',array('tnid'=>$value['zn_object_id']));?>">
                                    <!-- 如果正在直播的课程请添加liveing类名来显示状态 -->
                                    <div class="picturebox liveing">
                                        <i class="live-icon"></i>
                                        <i class="paly-icon"></i>
                                        <img src="<?php echo ($value['zc_image']); ?>" alt="">
                                        <div class="time">
                                            <span class="date">正在直播</span>
                                            <span class="clock"><?php echo ($value['online_num']); ?>人观看</span>
                                        </div>
                                    </div>
                                    <div class="course-info">
                                        <h2>【<?php echo ($value['fid_name']); ?>.<?php echo ($value['cat_name']); ?>】<?php echo ($value['zc_title']); ?></h2>
                                        <p><?php echo ($value['zc_school']); ?> <?php echo ($value['zc_nickname']); ?></p>
                                    </div>
                                </a>
                            </li>
                            <?php else: ?>
                                <li class="swiper-slide">
                                    <a href="<?php echo U('/Live/livedetail',array('tnid'=>$value['zn_object_id']));?>">
                                        <!-- 如果正在直播的课程请添加liveing类名来显示状态 -->
                                        <div class="picturebox ">
                                            <i class="live-icon"></i>
                                            <i class="paly-icon"></i>
                                            <img src="<?php echo ($value['zc_image']); ?>" alt="">
                                            <div class="time">
                                                <?php if($value['next_time']): ?><span class="date">距离开始还有</span>
                                                <span class="clock"><?php echo ($value['next_time']); ?></span>
                                                    <?php else: ?>
                                                    <span class="date">直播马上开始,请耐心等待</span><?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="course-info">
                                            <h2>【<?php echo ($value['fid_name']); ?>.<?php echo ($value['cat_name']); ?>】<?php echo ($value['zc_title']); ?></h2>
                                            <p><?php echo ($value['zc_school']); ?> <?php echo ($value['zc_nickname']); ?></p>
                                        </div>
                                    </a>
                                </li><?php endif; endforeach; endif; ?>
                                    <?php else: ?>
                                            <img style="position: absolute;left:89px;top: 43px;" src="/Tpl-website/pc/res/images/teacher/20170906321.png"><?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div><?php endif; ?>
        </div>
        <div class="zr_layout" style="margin-top: 44px; border-top: 1px dashed #e8e8e8;">
            <div class="record-classify">
                <div class="classify-text fl">
                    全部分类 >
                </div>
                <div class="select-grade fl">
                    <button>
                        <?php if($fid_label != ''): echo ($fid_label); ?>
                            <?php else: ?>请选择<?php endif; ?>
                    </button>
                    <i class="develop">
                </i>
                </div>
                <div class="select-grade-panel">
                    <button val="0">
                        全部
                    </button>
                    <?php if(is_array($cat)): $i = 0; $__LIST__ = $cat;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cat): $mod = ($i % 2 );++$i;?><button val="<?php echo ($cat["id"]); ?>">
                            <?php echo ($cat["title"]); ?>
                        </button><?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            <div class="record-title">
                <div class="classify l-classify">
                    <button class="btn <?php if( $search_content_array[ 'cat_id'] <= 0 ): ?>active<?php endif; ?>">
                        全部(<?php echo ($catTotal); ?>)
                    </button>
                    <?php if(isset($catList)): if(is_array($catList)): $i = 0; $__LIST__ = $catList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$catList): $mod = ($i % 2 );++$i;?><button class="btn <?php if( $search_content_array[ 'cat_id'] == $catList[ 'id'] ): ?>active<?php endif; ?>" cat_id="<?php echo ($catList["id"]); ?>">
                                <?php echo ($catList["title"]); ?>(<?php echo ($catList["total"]); ?>)
                            </button><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                </div>
            </div>
            <a href="<?php echo ($ad['url']); ?>" class="record-banner">
        <img src="<?php echo ($ad['image']); ?>" alt="">
        </a>
            <div class="record-content">
                <div class="record-statistics l-record-statistics">
                    <div class="statistics-left">
                        <div class="item <?php if(($orderby == 1) or ($orderby == 2)): ?>active<?php endif; ?>">
                            <button class="btn">
                                收藏数
                            </button>
                            <span class="develop <?php if(($orderby) == "2"): ?>develop-up<?php endif; ?>">
                            </span>
                        </div>
                        <div class="item <?php if(($orderby == 3) or ($orderby == 4)): ?>active<?php endif; ?>">
                            <button class="btn">
                                报名数
                            </button>
                            <span class="develop <?php if(($orderby) == "4"): ?>develop-up<?php endif; ?>">
                            </span>
                        </div>
                        <div class="item <?php if(($orderby == 5) or ($orderby == 6)): ?>active<?php endif; ?>" style="width: 75px;">
                            <button class="btn">
                                开始时间
                            </button>
                            <span class="develop <?php if(($orderby) == "6"): ?>develop-up<?php endif; ?>">
                            </span>
                        </div>
                    </div>
                    <label class="statistics-right">
                        <?php if($login_studnet_info['id'] > 0): ?><input class="l-select-school-teacher" type="checkbox" name="teacher" value="1" <?php if(($search_content_array["school"]) == "1"): ?>checked<?php endif; ?>>
                            <i></i> 本校老师<?php endif; ?>
                    </label>
                    <input class="l-select-grade-input" type="hidden" name="fid" id="fid" value="<?php echo ($search_content_array["fid"]); ?>">
                    <input class="l-cat-id" type="hidden" name="cat_id" id="cat_id" value="<?php echo ($search_content_array["cat_id"]); ?>">
                    <input class="l-school" type="hidden" name="school" id="school" value="<?php echo ($search_content_array["school"]); ?>">
                    <input class="l-orderby" type="hidden" name="orderby" id="orderby" value="<?php echo ($search_content_array["orderby"]); ?>">
                </div>
                <div class="course-content s-sr-live-list">
                    <ul class="clearfix">
                        <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i; if($list['living'] == 1): ?><li class="swiper-slide">
                            <a href="<?php echo U('/Live/livedetail',array('tnid'=>$list['id']));?>">
                                <div class="picturebox liveing">
                                    <i class="live-icon"></i>
                                    <i class="paly-icon"></i>
                                    <img src="<?php echo ($list["zc_image"]); ?>" alt="">
                                    <div class="time">
                                        <span class="date"><?php echo ($list['zc_date']); ?></span>
                                        <span class="clock"><?php echo ($list['zc_start_time']); ?> - <?php echo ($list['zc_end_time']); ?></span>
                                    </div>
                                </div>
                                <div class="course-info">
                                    <h2>【<?php echo ($list["fid_name"]); ?>.<?php echo ($list["cat_name"]); ?>】<?php echo ($list["zc_title"]); ?></h2>
                                    <p><?php echo ($list["zc_school"]); ?> <?php echo ($list["zc_nickname"]); ?>老师</p>
                                    <div class="s-statistics">
                                        <span><?php echo ($list["zn_fav_num"]); ?>人收藏</span>
                                        <span><?php echo ($list["zn_enroll_num"]); ?>人报名</span>
                                    </div>
                                </div>
                            </a>
                        </li>
                            <?php else: ?>
                        <li>
                                <a href="<?php echo U('/Live/livedetail',array('tnid'=>$list['id']));?>">
                                    <div class="picturebox ">
                                        <i class="live-icon"></i>
                                        <i class="paly-icon"></i>
                                        <img src="<?php echo ($list["zc_image"]); ?>" alt="">
                                        <div class="time">
                                            <span class="date"><?php echo ($list['zc_date']); ?></span>
                                            <span class="clock"><?php echo ($list['zc_start_time']); ?> - <?php echo ($list['zc_end_time']); ?></span>
                                        </div>
                                    </div>
                                    <div class="course-info">
                                        <h2>【<?php echo ($list["fid_name"]); ?>.<?php echo ($list["cat_name"]); ?>】<?php echo ($list["zc_title"]); ?></h2>
                                        <p><?php echo ($list["zc_school"]); ?> <?php echo ($list["zc_nickname"]); ?>老师</p>
                                        <div class="s-statistics">
                                            <span><?php echo ($list["zn_fav_num"]); ?>人收藏</span>
                                            <span><?php echo ($list["zn_enroll_num"]); ?>人报名</span>
                                        </div>
                                    </div>
                                </a>
                            </li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                    </ul>
                    <div class="list_page">
                        <?php echo ($page); ?>
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
require(["live"], function(live) {
    live()
})
</script>