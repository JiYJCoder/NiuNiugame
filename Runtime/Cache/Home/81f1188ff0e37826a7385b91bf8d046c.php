<?php if (!defined('THINK_PATH')) exit();?><head>
    <meta charset="UTF-8">
    <title><?php echo ($seoData["title"]); ?></title>
	<meta name="author" content="" />
    <meta name="keywords" content="<?php echo ($seoData["keywords"]); ?>">
    <meta name="description" content="<?php echo ($seoData["description"]); ?>">
    <link rel="stylesheet" href=/Tpl-website/pc/res/css/teacher/teacher.css>
    <script src="/Tpl-website/pc/res/js/lib/jquery-3.1.1.min.js"></script>
    <script src="/Tpl-website/pc/res/js/common.js"></script>
    <script src='/Tpl-website/pc/res/js/lib/require.js'></script>
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
                <span class="active">使用账号登录</span>
                <span>使用绑定手机号登录</span>
            </div>
            <div class="account-login">
                <input class="ipt account-btn " type="text" placeholder="请输入账号/身份证" value="313768239@qq.com">
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



<div class="t_nav_bar">
    <div class='zr_layout'>
        <div class="fl nav_bar_f">
            <a class="nav_link<?php echo ($index_active); ?>" href="<?php echo U('index');?>">老师首页</a>
            <a class="nav_link<?php echo ($mylive_active); ?>" href="<?php echo U('mylive');?>">我的直播</a>
            <a class="nav_link<?php echo ($myrecorded_active); ?>" href="<?php echo U('myrecorded');?>">我的录播</a>
            <a class="nav_link<?php echo ($myset_active); ?>" href="<?php echo U('myset');?>">个人设置</a>
            <a class="nav_link<?php echo ($certification_active); ?>" href="<?php echo U('certification');?>">教师验证</a>
        </div>
       <!--  <div class="fr user_info nav_bar_r">
            <div class="fl">
                <span><?php echo ($member_info["zc_nickname"]); ?></span>,您好！
            </div>
            <div class="fl user_pic">
                <img src="<?php echo ($member_info["zc_headimg"]); ?>" alt="">
            </div>
        </div> -->
    </div>
</div>
<script>
navAnimate();
    function navAnimate() {
    var $nav_link = $('.nav_bar_f').find('.active');
    var $index = $nav_link.index();
    var leftDistance = $nav_link.position().left;
    var NAVLEFT = 16;

    $(".nav_bar_f").append('<span class="sideline"></span>');
    $(".sideline").css({
        "left": leftDistance + NAVLEFT
    });

    $(".nav_link").hover(function () {
        $(".sideline").stop(true);
        $(".sideline").animate({
            left: $(this).position().left + NAVLEFT
        }, 300);
        $(this).addClass('active').siblings().removeClass("active");
    }, function () {
        $nav_link.addClass('active').siblings().removeClass("active");
        $(".sideline").animate({
            left: leftDistance + NAVLEFT
        }, 300);
    });
}
</script>
    <div class="banner_bg mylive">
        <div class="zr_layout">
            <div style="position: relative;top:73px;">
                <h1>我的直播</h1>
                <?php if(($no_end_lesson) < "2"): ?><span><a href="<?php echo U('sqlive');?>"> 申请直播</a></span>
                    <?php else: ?><span id="gx_live" style="cursor:not-allowed"><a style="color:#fff;"  > 申请直播</a></span>
                    <script>
                        $('#gx_live').hover(function(){
                            layer.tips('最多只能存在两个未完结的直播哦', '#gx_live', {
                                tips: [1, '#0FA6D8'] //还可配置颜色
                            });
                        },function(){

                        })
                    </script><?php endif; ?>

            </div>
        </div>
    </div>
    <div class="my_live_wrapper">
        <div class="zr_layout mylive">
            <!-- 无直播时候 -->
            <?php if($has_Mylive == 0): ?><div class="no_live_stutas"></div>
            <?php else: ?>
            <!-- 有直播课 -->
                <?php if(is_array($message)): foreach($message as $key=>$value): if($value['zl_status'] == 6): ?><div class="lb_list">
                            <div class="lb_list_item clearfix">
                                <div class="lb_head_sculpture fl">
                                    <img src="<?php echo ($value["image"]); ?>" alt="">
                                </div>
                                <div class="lb_info fl clearfix">
                                    <div class="fl">
                                        <div>
                                            <h3 class="lb_list_title"><?php echo ($value["zc_title"]); ?></h3>
                                            <div>
                                                <div class="number_of_courses">
                                                    <div class="p_r_23" style="padding-right: 23px;">
                                                        <span><?php echo ($value["cat_id_label"]); ?></span>
                                                    </div>
                                                    <div>
                                                        <span>共上</span>
                                                        <span class="orange_color"><?php echo ($value["all_class"]); ?></span>
                                                        <span>节，</span>
                                                    </div>
                                                    <div>
                                                        <span>已上</span>
                                                        <span class="orange_color"><?php echo ($value["finish_class"]); ?></span>
                                                        <span>节</span>
                                                    </div>
                                                    <div class="m-l-address">
                                                        <p style="display:none"><?php echo ($value['zc_push_url_first']); ?></p>
                                                        <p style="display:none"><?php echo ($value['zc_push_url_second']); ?></p>
                                                        <!-- <button class="address-text" data-clipboard-text="<?php echo ($value["zc_push_url_second"]); ?>"
                                               aria-label="复制成功！">推流地址</button> -->
                                                         <div class="address-text"><span>推流地址</span>
                                                              <div class="address-cr">
                                              <div class="rtmp-address-y">
                                                    <span class="sub_title_ts">rtmp地址：</span>
                                                    <input class="address-inn" type="text" value="<?php echo ($value['zc_push_url_first']); ?>">
                                                    <input data-clipboard-text="<?php echo ($value['zc_push_url_first']); ?>"
                                                   aria-label="复制成功！" class="copy-btn-uk" type="button" value="复制">
                                                </div>
                                                <div class="rtmp-address-y">
                                                    <span class="sub_title_ts">直播码：</span>
                                                    <input class="address-inn" type="text" value="<?php echo ($value['zc_push_url_second']); ?>">
                                                    <input data-clipboard-text="<?php echo ($value['zc_push_url_second']); ?>"
                                                   aria-label="复制成功！" class="copy-btn-uk" type="button" value="复制">
                                                </div>
                                            </div>
            
                                                         </div>
                                                    </div>
                                                </div>
                                                <div class="stra_number">
                                                    <div>
                                                        <span class="sc_number"><?php echo ($value["favorite"]); ?></span>
                                                        <span class="sc_number_text">人收藏</span>
                                                    </div>
                                                    <div>
                                                        <span class="bm_number"><?php echo ($value["enroll"]); ?></span>
                                                        <span class="bm_number_text">人报名</span>
                                                    </div>
                                                    <div>
                                                        <span class="kc_number_text">课程排名第</span>
                                                        <span><?php echo ($value['rank']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="curriculum_name_list">
                                            <?php echo ($value["btn"]); ?>
                                        </div>
                                    </div>
                                    <div class="fr lb_sq_status">
                                        <?php if($value['living'] == 1): ?><span>正在上课</span>
                                            <?php elseif($value['living'] == 2): ?>
                                            <span>未安排下次课程</span>
                                            <?php else: ?>
                                        <div class="next_zb_titme">
                                            <span class="next_zb_text1">距离下次开播还有</span>
                                            <span class="djs_text"><?php echo ($value["next_d1"]); ?></span>
                                            <span class="djs_text"><?php echo ($value["next_d2"]); ?></span>
                                            <span>天</span>
                                            <span class="djs_text"><?php echo ($value["next_h1"]); ?></span>
                                            <span class="djs_text"><?php echo ($value["next_h2"]); ?></span>
                                            <span>时</span>
                                            <span class="djs_text"><?php echo ($value["next_i1"]); ?></span>
                                            <span class="djs_text"><?php echo ($value["next_i2"]); ?></span>
                                            <span>分</span>
                                        </div><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- 其他模板 -->
                        <div class="lb_list">
                            <div class="lb_list_item clearfix">
                                <div class="lb_head_sculpture fl">
                                    <img src="<?php echo ($value["image"]); ?>" alt="">
                                </div>
                                <div class="lb_info fl clearfix">
                                    <div class="fl">
                                        <div>
                                            <h3 class="lb_list_title"><?php echo ($value["zc_title"]); ?></h3>
                                            <div>
                                                <div class="number_of_courses">
                                                    <div class="p_r_23" style="padding-right: 23px;">
                                                        <span><?php echo ($value["cat_id_label"]); ?></span>
                                                    </div>
                                                    <div>
                                                        <span>共上</span>
                                                        <span class="orange_color"><?php echo ($value["all_class"]); ?></span>
                                                        <span>节，</span>
                                                    </div>
                                                    <div>
                                                        <span>已上</span>
                                                        <span class="orange_color"><?php echo ($value["finish_class"]); ?></span>
                                                        <span>节</span>
                                                    </div>
                                                </div>
                                                <div class="stra_number">
                                                    <div>
                                                        <span class="sc_number"><?php echo ($value["favorite"]); ?></span>
                                                        <span class="sc_number_text">人收藏</span>
                                                    </div>
                                                    <div>
                                                        <span class="bm_number"><?php echo ($value["enroll"]); ?></span>
                                                        <span class="bm_number_text">人报名</span>
                                                    </div>
                                                    <div>
                                                        <span class="kc_number_text">课程排名第</span>
                                                        <span><?php echo ($value['rank']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="curriculum_name_list">
                                            <?php echo ($value["btn"]); ?>
                                        </div>
                                    </div>
                                    <div class="fr lb_sq_status">
                                        <?php echo ($value['msg']); ?>
                                        <!-- <i class="Be_through_with"></i> -->
                                        <i class="<?php echo ($value['pic']); ?>"></i>
                                        <!--<img src="__IMAGES__/live_status1.png"-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </else><?php endif; endforeach; endif; endif; ?>
            <div class="list_page">
                <?php echo ($page); ?>
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
    require(['teacher'], function(teacher) {
        teacher();
    });
</script>