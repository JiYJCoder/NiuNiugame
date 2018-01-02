<?php if (!defined('THINK_PATH')) exit();?><head>
    <meta charset="UTF-8">
    <title>申请直播</title>
	<meta name="author" content="" />
    <meta name="keywords" content="<?php echo ($seoData["keywords"]); ?>">
    <meta name="description" content="<?php echo ($seoData["description"]); ?>">
    <link rel="stylesheet" href=/Tpl-website/pc/res/css/teacher/teacher.css>
    <script src="/Tpl-website/pc/res/js/lib/jquery.js"></script>
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
    <div class="t_info_wrapper s_q_live_wrapper">
        <div class="zr_layout">
            <div class="t_s_content s_q_live" style="top: 0;">
                <h1 class="s_q_title">
                申请直播
            </h1>
                <div class="s_q_option">
                    <div class="s-w-addCircle classify clearfix">
                        <span class="sub_title fl">课程分类</span>
                        <div class="fl select_option">
                            <input class="select_class_btn one-catalog-btn" type="button" value="请选择一级分类">
                            <i class="sjx"></i>
                            <div class="s_option_input select-one-catalog">
                                <?php if(is_array($parentCat)): $i = 0; $__LIST__ = $parentCat;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$parentCat): $mod = ($i % 2 );++$i;?><span val="<?php echo ($parentCat["id"]); ?>"><?php echo ($parentCat["title"]); ?></span><?php endforeach; endif; else: echo "" ;endif; ?>
                            </div>
                        </div>
                        <div class="fl select_option">
                            <input class="select_class_btn two-catalog-btn" type="button" value="请选择二级分类">
                            <i class="sjx"></i>
                            <div class="s_option_input select-two-option">

                            </div>
                        </div>
                    </div>
                    <div class="s-w-addCircle course_title clearfix">
                        <h1 class="sub_title fl">课题名称</h1>
                        <input class="fl" type="text" placeholder="请输入课题名称">
                    </div>
                    <div class="s-w-addCircle coures_gh_pic clearfix">
                        <h1 class="sub_title fl">课题封面</h1>
                        <div class="img_box fl">
                            <!-- 初始状态没有上传图片在mask添加 -->
                            <div class="mask init_pic">
                                <span>点击更换</span>
                                <span class="jy_fbl">建议尺寸:800*600</span>
                                <input class="uploadPic" accept="image/*" type="file">
                            </div>
                            <img class="coures_img" src="" alt="">
                        </div>
                    </div>
                    <div class="s-w-addCircle course_summary clearfix">
                        <h1 class="sub_title fl">课题简介</h1>
                        <textarea type="text" placeholder="对课题作简单介绍"></textarea>
                    </div>
                    <div class="s-w-addCircle course_syllabus clearfix">
                        <div class="clearfix">
                            <h1 class="sub_title fl">课程表</h1>
                            <div class="fl" style="position: relative;top: -10px;left:13px">
                                <div class="fl" style="position:relative">
                                    <input class="add_btn add_one_btn" type="button" value="添加单节">
                                    <div class="add_one_content add_one_content1">
                                        <i class="close"></i>
                                        <div class="clearfix course_time">
                                            <span class="fl">课节日期</span>
                                            <input class="fl btn has_boxshaw add_one_timeBtn" type="button" value="选择日期">
                                        </div>
                                        <div class="clearfix course_time course_sr_title">
                                            <span class="fl">课节标题</span>
                                            <input class="fl btn has_boxshaw add_sligle_title" type="text" value="" placeholder="请输入标题">
                                        </div>
                                        <div class="clearfix course_time coures_house">
                                            <div class="coures_house_container">
                                                <span class="fl">直播时间</span>
                                            </div>
                                            <div style="position: relative;">
                                                <input class="fl btn has_boxshaw sr_start_time1" type="button" value="请输入">
                                            </div>
                                            <span class="fl" style="margin-left: 16px;">至</span>
                                            <div style="position: relative;">
                                                <input class="fl btn has_boxshaw sr_start_time2" type="button" value="请输入">
                                            </div>
                                        </div>
                                        <div class="add_one_save">
                                            <input class="hover_btn save_sigle_btn" type="button" value="确定">
                                        </div>
                                    </div>
                                </div>
                                <div class="fl">
                                    <input class="add_btn add_double_btn" type="button" value="添加多节">
                                    <div class="add_one_content add_many_content">
                                        <i class="close"></i>
                                        <div class="clearfix course_time">
                                            <span class="fl">添加节数</span>
                                            <input maxlength="3" onkeyup="(this.v=function(){this.value=this.value.replace(/[^0-9-]+/,'');}).call(this)" onblur="this.v();" class="fl btn has_boxshaw add_course_count add_course_count1" type="text" value="1">
                                            <span style="padding-left:12px">节</span>
                                        </div>
                                        <div class="clearfix course_time">
                                            <span class="fl">间隔天数</span>
                                            <input  maxlength="3" onkeyup="(this.v=function(){this.value=this.value.replace(/[^0-9-]+/,'');}).call(this)" onblur="this.v();" class="fl btn has_boxshaw add_course_count add_course_day" type="text" value="1">
                                            <span style="padding-left:12px">天</span>
                                        </div>
                                        <div class="clearfix course_time">
                                            <span class="fl">课节日期</span>
                                            <input   class="fl btn has_boxshaw add_many_timeBtn" type="button" value="选择日期">
                                        </div>
                                        <div class="clearfix course_time course_sr_title">
                                            <span class="fl">课节标题</span>
                                                <input class="fl btn has_boxshaw add_double_title" type="text" value="" placeholder="请输入标题">
                                        </div>
                                        <div class="clearfix course_time coures_house">
                                            <div class="coures_house_container">
                                                <span class="fl">直播时间</span>
                                            </div>
                                            <div style="position: relative;">
                                                <input class="fl btn has_boxshaw sr_many_time1" type="button" value="请输入">
                                            </div>
                                            <span class="fl" style="margin-left: 16px;">至</span>
                                            <div style="position: relative;">
                                                <input class="fl btn has_boxshaw sr_many_time2" type="button" value="请输入">
                                            </div>
                                        </div>
                                        <div class="clearfix course_time sq_suffix">
                                            <span class="fl">后缀识别</span>
                                            <label  class="fl" style="margin-left: 16px;" >
                                                <input type="radio" value="number" checked name="numberOrCharacter">
                                                <i></i>
                                                数字(N)
                                            </label>
                                            <label class="fl" style="margin-left: 25px;">
                                                <input type="radio" value="char" name="numberOrCharacter">
                                                <i></i>
                                                字母(N)
                                            </label>
                                        </div>
                                        <div class="add_one_save">
                                            <input class="hover_btn save_double_btn" type="button" value="确定">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sq_syllabus">
                            <!-- 没有课程安排现在这个状态 -->
                             <div class="arrange_course">
                                <i></i>
                            </div>
                            <!-- <div class="syllabus_list clearfix">
                                <div class="list_left fl">
                                    <span class="sq_syllabus_no fl">1.</span>
                                    <span class="sq_syllabus_title fl">勾股定理的练习题练习题习题习题习题df(1)</span>
                                    <div class="syllabus_title fl">
                                        <span style="padding-right:4px;"class="sq_syllabus_date">2017-05-15</span>
                                        <span>22:00</span>
                                        <span>-</span>
                                        <span>23:00</span>
                                    </div>
                                </div>
                                <div class="syllabus_right fr">
                                    <i class="revise"></i>
                                    <i class="del"></i>
                                </div>
                            </div>
                            <div class="syllabus_list clearfix">
                                <div class="list_left fl">
                                    <span class="sq_syllabus_no fl">1.</span>
                                    <span class="sq_syllabus_title fl">勾股定理的练习题(1)</span>
                                    <div class="syllabus_title fl">
                                        <span style="padding-right:4px;"class="sq_syllabus_date">2017-05-15</span>
                                        <span>22:00</span>
                                        <span>-</span>
                                        <span>23:00</span>
                                    </div>
                                </div>
                                <div class="syllabus_right fr">
                                    <i class="revise"></i>
                                    <i class="del"></i>
                                </div>
                            </div>
                            <div class="syllabus_list clearfix">
                                <div class="list_left fl">
                                    <span class="sq_syllabus_no fl">1.</span>
                                    <span class="sq_syllabus_title fl">勾股定理的练习题(1)</span>
                                    <div class="syllabus_title fl">
                                        <span style="padding-right:4px;"class="sq_syllabus_date">2017-05-15</span>
                                        <span>22:00</span>
                                        <span>-</span>
                                        <span>23:00</span>
                                    </div>
                                </div>
                                <div class="syllabus_right fr">
                                    <i class="revise"></i>
                                    <i class="del"></i>
                                </div>
                            </div>
                            <div class="syllabus_list clearfix">
                                <div class="list_left fl">
                                    <span class="sq_syllabus_no fl">1.</span>
                                    <span class="sq_syllabus_title fl">勾股定理的练习题(1)</span>
                                    <div class="syllabus_title fl">
                                        <span style="padding-right:4px;"class="sq_syllabus_date">2017-05-15</span>
                                        <span>22:00</span>
                                        <span>-</span>
                                        <span>23:00</span>
                                    </div>
                                </div>
                                <div class="syllabus_right fr">
                                    <i class="revise"></i>
                                    <i class="del"></i>
                                </div>
                            </div>
                            <div class="syllabus_list clearfix">
                                <div class="list_left fl">
                                    <span class="sq_syllabus_no fl">1.</span>
                                    <span class="sq_syllabus_title fl">勾股定理的练习题(1)</span>
                                    <div class="syllabus_title fl">
                                        <span style="padding-right:4px;"class="sq_syllabus_date">2017-05-15</span>
                                        <span>22:00</span>
                                        <span>-</span>
                                        <span>23:00</span>
                                    </div>
                                </div>
                                <div class="syllabus_right fr">
                                    <i class="revise"></i>
                                    <i class="del"></i>
                                </div>
                            </div>
                            <div class="syllabus_list clearfix">
                                <div class="list_left fl">
                                    <span class="sq_syllabus_no fl">1.</span>
                                    <span class="sq_syllabus_title fl">勾股定理的练习题(1)</span>
                                    <div class="syllabus_title fl">
                                        <span style="padding-right:4px;"class="sq_syllabus_date">2017-05-15</span>
                                        <span>22:00</span>
                                        <span>-</span>
                                        <span>23:00</span>
                                    </div>
                                </div>
                                <div class="syllabus_right fr">
                                    <i class="revise"></i>
                                    <i class="del"></i>
                                </div>
                            </div>
                            <div class="syllabus_list clearfix">
                                <div class="list_left fl">
                                    <span class="sq_syllabus_no fl">1.</span>
                                    <span class="sq_syllabus_title fl">勾股定理的练习题(1)</span>
                                    <div class="syllabus_title fl">
                                        <span style="padding-right:4px;"class="sq_syllabus_date">2017-05-15</span>
                                        <span>22:00</span>
                                        <span>-</span>
                                        <span>23:00</span>
                                    </div>
                                </div>
                                <div class="syllabus_right fr">
                                    <i class="revise"></i>
                                    <i class="del"></i>
                                </div>
                            </div>
                            <div class="syllabus_list clearfix">
                                <div class="list_left fl">
                                    <span class="sq_syllabus_no fl">1.</span>
                                    <span class="sq_syllabus_title fl">勾股定理的练习题(1)</span>
                                    <div class="syllabus_title fl">
                                        <span style="padding-right:4px;"class="sq_syllabus_date">2017-05-15</span>
                                        <span>22:00</span>
                                        <span>-</span>
                                        <span>23:00</span>
                                    </div>
                                </div>
                                <div class="syllabus_right fr">
                                    <i class="revise"></i>
                                    <i class="del"></i>
                                </div>
                            </div>
                            <div class="syllabus_list clearfix">
                                <div class="list_left fl">
                                    <span class="sq_syllabus_no fl">1.</span>
                                    <span class="sq_syllabus_title fl">勾股定理的练习题(1)</span>
                                    <div class="syllabus_title fl">
                                        <span style="padding-right:4px;"class="sq_syllabus_date">2017-05-15</span>
                                        <span>22:00</span>
                                        <span>-</span>
                                        <span>23:00</span>
                                    </div>
                                </div>
                                <div class="syllabus_right fr">
                                    <i class="revise"></i>
                                    <i class="del"></i>
                                </div>
                            </div>
                            <div class="syllabus_list clearfix">
                                <div class="list_left fl">
                                    <span class="sq_syllabus_no fl">1.</span>
                                    <span class="sq_syllabus_title fl">勾股定理的练习题(1)</span>
                                    <div class="syllabus_title fl">
                                        <span style="padding-right:4px;"class="sq_syllabus_date">2017-05-15</span>
                                        <span>22:00</span>
                                        <span>-</span>
                                        <span>23:00</span>
                                    </div>
                                </div>
                                <div class="syllabus_right fr">
                                    <i class="revise"></i>
                                    <i class="del"></i>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
                    <div class="save_sq_mask">
                        <div class="save_sq_box">
                            <i></i>
                            <span>工作人员正加班审核中，请耐心等候</span>
                            <a class="hover_btn" href="<?php echo U('teacher/index');?>">返回首页</a>
                        </div>
                    </div>
                    <input class="save_sq_btn" type="button" value="提交申请">
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

require(['sqlive'], function(sqlive) {
    sqlive.loadTip();
});
</script>