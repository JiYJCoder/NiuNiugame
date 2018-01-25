<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo L('ADMIN_TITLE');?></title>
<link rel="shortcut icon" href="/Public/Static/images/favicon.ico" />
<link href="/Public/Static/css/bootstrap.min.css" rel="stylesheet">
<link href="/Public/Static/css/font-awesome.min.css" rel="stylesheet">
<link href="/Public/Static/css/common.css" rel="stylesheet">
<script type="text/javascript">
if(navigator.appName == 'Microsoft Internet Explorer'){
		if(navigator.userAgent.indexOf("MSIE 5.0")>0 || navigator.userAgent.indexOf("MSIE 6.0")>0 || navigator.userAgent.indexOf("MSIE 7.0")>0) {
			alert('您使用的 IE 浏览器版本过低, 推荐使用 Chrome 浏览器或 IE8 及以上版本浏览器.');
		}
}
</script>
<script>var require = { urlArgs: 'v=<?php echo date('YmdH'); ?>' };</script>
<script type="text/javascript" src="/Public/Static/js/lib/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="/Public/Static/js/app/lqCommonFun.js"></script>
<script type="text/javascript" src="/Public/Static/js/require.js"></script>
<script type="text/javascript" src="/Public/Static/js/app/config.js"></script>
</head>
<body>
 <div class="navbar navbar-inverse navbar-fixed-top">
  <div class="container-fluid">
    <ul class="nav navbar-nav" id="top-nav">
      <li id="top-key-0"><a lqid="0" href="#"><i class="fa fa-home"></i>首页</a></li>
      <?php if(is_array($system_top_menu)): $key_top = 0; $__LIST__ = $system_top_menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data_top): $mod = ($key_top % 2 );++$key_top;?><li id="top-key-<?php echo ($key_top); ?>"><a lqid="<?php echo ($key_top); ?>" href="#"><i class="fa fa-cog"></i><?php echo ($data_top["zc_caption"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
      <li><a href="javascript:;" class="getUrl" lqHref="<?php echo U('Index/quickClearCache');?>" title="快速清缓存"><i class="fa fa-retweet"></i>快速清缓存</a></li>
    </ul>
    
    <ul class="nav navbar-nav navbar-right">
      <li class="dropdown topbar-notice"> <a type="button" data-toggle="dropdown"> <i class="fa fa-qrcode"></i> </a>
        <div class="dropdown-menu" aria-labelledby="dLabel">
          <div class="topbar-notice-panel">
            <div class="topbar-notice-arrow"></div>
            <div class="topbar-notice-head"> <span>平台二维码</span></div>
            <div class="topbar-notice-body" style="text-align:center;">
              <img src="/Public/Static/images/system-qrcode.jpg" width="300" />
            </div>
          </div>
        </div>
      </li>    
      <li class="dropdown topbar-notice"> <a type="button" data-toggle="dropdown"> <i class="fa fa-bell"></i> <span class="badge" id="notice-total">0</span> </a>
        <div class="dropdown-menu" aria-labelledby="dLabel">
          <div class="topbar-notice-panel">
            <div class="topbar-notice-arrow"></div>
            <div class="topbar-notice-head"> <span>系统公告</span> <a href="javascript:;" class="pull-right">更多公告>></a> </div>
            <div class="topbar-notice-body">
              <ul id="notice-container">
              </ul>
            </div>
          </div>
        </div>
      </li>
      
      <li class="dropdown"> <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" style="display:block;max-width:205px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><i class="fa fa-user"></i><?php echo ($login_admin_info["zc_account"]); ?>(<?php echo ($login_admin_info["zn_role_id_label"]); ?>)<b class="caret"></b></a>
        <ul class="dropdown-menu">
          <li><a href="<?php echo U('Index/modifyMyself/action/info');?>"><i class="fa fa-user"></i> 修改账号</a></li>
          <li><a href="<?php echo U('Index/modifyMyself/action/pass');?>"><i class="fa fa-lock"></i> 修改密码</a></li>
          <li class="divider"></li>
          <?php if($login_admin_info["id"] == '1' ): ?><li><a href="<?php echo U('SystemMenu/index');?>"><i class="fa fa-sitemap fa-fw"></i> 系统菜单</a></li><?php endif; ?>
          <?php if($login_admin_info["zn_role_id"] == '1' ): ?><li><a href="<?php echo U('Index/clearCache');?>"><i class="fa fa-refresh fa-fw"></i> 更新缓存</a></li><?php endif; ?>
          <li><a href="/document" target="_blank"><i class="fa fa-gears"></i> 接口文档</a></li>
          <li class="divider"></li>
          <li><a href="javascript:;" class="getUrl" lqhref="<?php echo U('Login/opLoginOut');?>" title="退出当前登陆"><i class="fa fa-sign-out fa-fw"></i> 退出系统</a></li>
        </ul>
      </li>
    </ul>
  </div>
</div>

<div class="container-fluid"  style="padding-top:55px;">
  <div class="row">
    <div class="col-xs-12 col-sm-3 col-lg-2 big-menu" id="left-menu">
      <div id="search-menu">
        <input class="form-control input-lg" style="border-radius:0; font-size:14px; height:43px;" type="text" placeholder="输入菜单名称可快速查找">
      </div>
      <?php if(is_array($system_left_menu)): $key1 = 0; $__LIST__ = $system_left_menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data1): $mod = ($key1 % 2 );++$key1;?><span class="left-menu" id="left-menu-<?php echo ($key1); ?>" <?php echo ($data1["system_style"]); ?>>
        <?php if(is_array($data1["system_menu"])): $key2 = 0; $__LIST__ = $data1["system_menu"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data2): $mod = ($key2 % 2 );++$key2;?><div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title"><?php echo ($data2["zc_caption"]); ?></h4>
              <a class="panel-collapse collapsed" data-toggle="collapse" href="#frame-<?php echo ($data2["id"]); ?>"> <i class="fa fa-chevron-circle-down"></i> </a> </div>
            <ul class="list-group collapse in" id="frame-<?php echo ($data2["id"]); ?>">
              <?php if(is_array($data2["child"])): $key_child = 0; $__LIST__ = $data2["child"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data_child): $mod = ($key_child % 2 );++$key_child; if($data_child["zc_run"] == '' ): ?><a id="left-key-<?php echo ($data_child["id"]); ?>" lqid="<?php echo ($data_child["id"]); ?>" class="list-group-item" href="javascript:;" kw="<?php echo ($data_child["zc_caption"]); ?>"><?php echo ($data_child["zc_caption"]); ?></a>
              <?php else: ?>
              <?php if($data_child["zc_target"] == 'openWin' ): ?><a id="left-key-<?php echo ($data_child["id"]); ?>" lqid="<?php echo ($data_child["id"]); ?>" class="list-group-item" href="javascript:;" lqHref="<?php echo ($data_child["run"]); ?>" kw="<?php echo ($data_child["zc_caption"]); ?>"><?php echo ($data_child["zc_caption"]); ?></a>
              <?php else: ?>
              <a id="left-key-<?php echo ($data_child["id"]); ?>" lqid="<?php echo ($data_child["id"]); ?>" class="list-group-item" href="<?php echo ($data_child["run"]); ?>" kw="<?php echo ($data_child["zc_caption"]); ?>" target="<?php echo ($data_child["zc_target"]); ?>"><?php echo ($data_child["zc_caption"]); ?></a><?php endif; endif; endforeach; endif; else: echo "" ;endif; ?>
            </ul>
          </div><?php endforeach; endif; else: echo "" ;endif; ?>
        </span><?php endforeach; endif; else: echo "" ;endif; ?>
      <script type="text/javascript">
		require(['bootstrap'], function(){
			$('#search-menu input').keyup(function() {
				var a = $(this).val();
				$('.big-menu .list-group-item, .big-menu .panel-heading').hide();
				$('.big-menu .list-group-item').each(function() {
				$(this).css('border-left', '0');
				if(a.length > 0 && $(this).attr('kw').indexOf(a) >= 0) {
					$(this).parents(".panel").find('.panel-heading').show();
					$(this).show().css('border-left', '3px #428bca double');
				}
				});
				if(a.length == 0) {
					$('.big-menu .list-group-item, .big-menu .panel-heading').show();
				}
			});
		});
      </script> 
    </div>


<div class="col-xs-12 col-sm-9 col-lg-10">
  <ul class="nav nav-tabs">
    <li class="active"><a href="#">账号概况 - 平台相关数据</a></li>
  </ul>
  <div class="clearfix welcome-container">
    <div class="page-header">
      <h4><i class="fa fa-plane"></i> 快捷操作</h4>
    </div>
    
    
    <div class="shortcut clearfix"> 
    <a href="<?php echo U('/RoomHarging');?>"> <i class="fa fa-car"></i> <span>收费模式</span> </a>
    <a href="<?php echo U('/Member');?>"> <i class="fa fa-users"></i> <span>会员列表</span> </a>
    <a href="<?php echo U('/Chat');?>"> <i class="fa fa-weixin"></i> <span>会员聊天</span> </a>
    <a href="<?php echo U('/Room');?>"> <i class="fa fa-comments"></i> <span>房间记录</span> </a>
    <a href="<?php echo U('/GameLog');?>"> <i class="fa fa-gamepad"></i> <span>游戏记录</span> </a>
    <a href="#<?php echo U('/PayLog');?>"> <i class="fa fa-instagram"></i> <span>充值记录</span> </a>
    <a href="#"> <i class="fa fa-paypal"></i> <span>消费记录</span> </a>
    <a href="<?php echo U('/Admin');?>" style="color:rgba(203,48,48,1);"> <i class="fa fa-users"></i> <span>管理员列表</span> </a>
    <a href="<?php echo U('/AdminLog');?>" style="color:rgba(203,48,48,1);"> <i class="fa fa-database"></i> <span>管理员日志</span> </a>
    </div>


        <div class="panel panel-default" id="scroll" style="margin-top:20px;">
            <div class="panel-heading">
                今日指数
            </div>
            <div class="account-stat">
                <div class="account-stat-btn">
                    <div>新会员注册<span id="today_member">0</span></div>
                    <div>房间开设数<span id="today_new_room">0</span></div>
                    <div>游戏局数<span id="today_game_log">0</span></div>
                    <div>充值金额<span id="today_recharge">0</span></div>
                    <div>消费金额<span id="today_consume">0</span></div>
                    <div>聊天记录<span id="today_chat">0</span></div>
                </div>
            </div>
        </div>

      <div class="panel panel-default" id="scroll" style="margin-top:20px;">
          <div class="panel-heading">
              昨日指数
          </div>
          <div class="account-stat">
              <div class="account-stat-btn">
                  <div>新会员注册<span id="yesterday_member">0</span></div>
                  <div>房间开设数<span id="yesterday_new_room">0</span></div>
                  <div>游戏局数<span id="yesterday_game_log">0</span></div>
                  <div>充值金额<span id="yesterday_recharge">0</span></div>
                  <div>消费金额<span id="yesterday_consume">0</span></div>
                  <div>聊天记录<span id="yesterday_chat">0</span></div>
              </div>
          </div>
      </div>

    
<div class="panel panel-default">
	<div class="panel-heading">指数详解(周度)
	<a class="text-danger" href="/sys-index.php/Member/index">查看更多</a>
    </div>
	<div class="panel-body">
		<div class="pull-right">
			<div class="checkbox" id="subscribe">
				<label style="color:#57B9E6;"><input checked type="checkbox"> 新会员注册</label>&nbsp;
				<label style="color:#843534"><input checked type="checkbox"> 房间开设数</label>&nbsp;
				<label style="color:rgba(149,192,0,1);"><input checked type="checkbox"> 游戏局数</label>&nbsp;
				<label style="color:#e7a017;"><input checked type="checkbox"> 充值金额</label>
				<label style="color:#ff0000;"><input checked type="checkbox"> 消费金额</label>
				<label style="color:#1006F1;"><input checked type="checkbox"> 聊天记录</label>
			</div>
		</div>
		<div style="margin-top:20px">
			<canvas id="myChartSubscribe" width="1200" height="300"></canvas>
		</div>
	</div>
</div>
<script>
	require(['chart', 'daterangepicker'], function(c) {
		var chart_subscribe = null;
		var chart_datasets_subscribe = null;
		var templates_subscribe = {
			flow1: {
				label: '新会员注册',
				fillColor : "rgba(87,185,230,0.1)",
				strokeColor : "rgba(87,185,230,1)",
				pointColor : "rgba(87,185,230,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(87,185,230,1)"
			},
			flow2: {
				label: '房间开设数',
				fillColor : "rgba(132,53,52,0.1)",
				strokeColor : "rgba(132,53,52,1)",
				pointColor : "rgba(132,53,52,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(132,53,52,1)"
			},
			flow3: {
				label: '游戏局数',
				fillColor : "rgba(149,192,0,0.1)",
				strokeColor : "rgba(149,192,0,1)",
				pointColor : "rgba(149,192,0,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(149,192,0,1)"
			},
			flow4: {
				label: '充值金额',
				fillColor : "rgba(231,160,23,0.1)",
				strokeColor : "rgba(231,160,23,1)",
				pointColor : "rgba(231,160,23,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(231,160,23,1)"
			},
			flow5: {
				label: '消费金额',
				fillColor : "rgba(255,0,0,0.1)",
				strokeColor : "rgba(255,0,0,1)",
				pointColor : "rgba(255,0,0,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(255,0,0,1)"
			}	,
            flow6: {
                label: '聊天数',
                fillColor : "rgba(16,6,241,0.1)",
                strokeColor : "rgba(16,6,241,1)",
                pointColor : "rgba(16,6,241,1)",
                pointStrokeColor : "#fff",
                pointHighlightFill : "#fff",
                pointHighlightStroke : "rgba(16,6,241,1)"
            }
        };

		function refreshDataSubscribe() {
			if(!chart_subscribe || !chart_datasets_subscribe) {
				return;
			}
			var visables = [];
			var i = 0;
			$('#subscribe input[type="checkbox"]').each(function(){
				if($(this).attr('checked')) {
					visables.push(i);
				}
				i++;
			});
			var ds = [];
			$.each(visables, function(){
				var o = chart_datasets_subscribe[this];
				ds.push(o);
			});
			chart_subscribe.datasets = ds;
			chart_subscribe.update();
		}

		var url = '<?php echo U("Index/ajaxSearch/tcop/text");?>';
		$.post(url, function(data){
            console.log(data)
			//今日关注指数
			$("#today_member").html(data.today_datasets.member);
			$("#today_new_room").html(data.today_datasets.room);
			$("#today_game_log").html(data.today_datasets.gamelog);
			$("#today_recharge").html(data.today_datasets.recharge);
			$("#today_consume").html(data.today_datasets.consume);
			$("#today_chat").html(data.today_datasets.chat);
			//昨日关注指数
            $("#yesterday_member").html(data.yesterday_datasets.member);
            $("#yesterday_new_room").html(data.yesterday_datasets.room);
            $("#yesterday_game_log").html(data.yesterday_datasets.gamelog);
            $("#yesterday_recharge").html(data.yesterday_datasets.recharge);
            $("#yesterday_consume").html(data.yesterday_datasets.consume);
            $("#yesterday_chat").html(data.yesterday_datasets.chat);
						
			//关注指数详解
			var datasets = data.datasets;
			if(!chart_subscribe) {
				var label = data.label;
				var ds = $.extend(true, {}, templates_subscribe);
				ds.flow1.data = datasets.member;
				ds.flow2.data = datasets.room;
				ds.flow3.data = datasets.gamelog;
				ds.flow4.data = datasets.recharge;
				ds.flow5.data = datasets.consume;
				ds.flow6.data = datasets.chat;
				var lineChartData = {
					labels : label,
					datasets : [ds.flow1, ds.flow2, ds.flow3, ds.flow4, ds.flow5,ds.flow6]
				};
				var ctx = document.getElementById("myChartSubscribe").getContext("2d");
				chart_subscribe = new Chart(ctx).Line(lineChartData, {
					responsive: true
				});
				chart_datasets_subscribe = $.extend(true, {}, chart_subscribe.datasets);
			}
			refreshDataSubscribe();
		});

		$('#subscribe input[type="checkbox"]').on('click', function(){
			$(this).attr('checked', !$(this).attr('checked'))
			refreshDataSubscribe();
		});
	});
</script>    

   
    <div class="account">
      <div class="panel panel-default row">
        <div class="panel-body">
          <div class="clearfix">
            <div class="col-sm-7">
              <p> 
              <strong><?php echo L('PROJECT_NAME');?></strong> 
              <span class="label label-success" style="display:inline-block; margin-right:10px;"> 授权使用中 </span> 
              </p>
              
              <p><strong>授权链接： </strong> http://www.xxx.com/do?g=api&amp;m=auth&amp;a=index</p>
              <p><strong>授权码： </strong> <a href="javascript:;" title="点击复制Token" style="color:#66667C;">omJNpZEhZeHj1ZxFECKkP48B5VFbk1HP</a></p>
              
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



	</div>
</div>
    
<div class="container-fluid footer" role="footer">
  <div class="page-header"></div>
  <span class="pull-left">
  <p>Powered by <a href="#"><b><?php echo L('PROJECT_TEAM');?></b></a> v2.0 &copy; 2016-2020 <a href="http://www.games.com/" target="_blank">www.games.com</a></p>
  </span> 
  <span class="pull-right">
  <p class="label label-info">{__RUNTIME__} </p>
  </span> 
 </div>

<?php if(ACTION_NAME == 'index'): ?><div class="TopBottomMenu">
	<ul>
		<li><a href="/sys-index.php/Member/index" title="会员列表">会员列表</a></li>
		<li><a href="/sys-index.php/Recharge/index" title="充值记录">充值记录</a></li>
		<li><a href="/sys-index.php/Consume/index" title="消费记录">消费记录</a></li>
		<li><a href="/sys-index.php/Room/index" title="房间列表">房间列表</a></li>
	</ul>
</div>
<script type="text/javascript" src="/Public/Static/js/dwsee.top.bottom.menu.min.js" ></script>
<script type="text/javascript">
$(document).ready(function() {$(this).dwseeTopBottomMenu()})
require(['layer'], function(){layer.photos({photos: '.imgtd',anim: 1});});	
</script><?php endif; ?> 
<script type="text/javascript" src="/Public/Static/js/admin.function.js"></script>
<script>
$(document).ready(function(){
//系统菜单的展示
var top_nav_id=util.cookie.get('top_nav_id');
var left_nav_id=util.cookie.get('left_nav_id');
if(top_nav_id) util.menuDisplay(top_nav_id,left_nav_id);
<?php if(ACTION_NAME=='index') { echo "$('#list-tbody tr:odd').addClass('tr_odd');//单双行样式\n"; echo "$('.opStatus').click(function(){util.visible($(this),'/sys-index.php/Index/opVisible');});//快捷启用禁用操作\n"; echo "$('.opDelete').click(function(){util.delete($(this),'/sys-index.php/Index/opDelete');});//单记录删除操作\n"; echo "$('tbody>tr>td[op]').dblclick(function(){util.ajaxEdit($(this),'/sys-index.php/Index');});//单项编辑\n"; echo "$('tbody>tr>td>a[op]').click(function(){util.ajaxPropertyA($(this),'/sys-index.php/Index');});//单项属性切换\n"; } ?>	
	//ajax点击响应href
	$(".getUrl").click(function(){util.getUrl($(this).attr("lqHref"));});
	//顶部菜单展示
	$("#top-nav a").click(function(){
		top_nav_id=$(this).attr("lqid");
		$("#top-nav").find("li").removeClass("active");
		if(top_nav_id){
			if(top_nav_id==0){
				 util.R(0,"<?php echo U('Index/index');?>");
				$("#left-menu-2").css({"display":"block"});
				return;
			}
			util.cookie.set('top_nav_id',top_nav_id);
			$("#left-menu").find(".left-menu").css({"display":"none"});
			$("#left-menu-"+top_nav_id).css({"display":"block"});
		}
	});
	//左则菜单焦点记录
	$(".list-group-item").click(function(){
		var lcHref=$(this).attr("lqHref");
		if(typeof lcHref!='undefined'){
		 util.openWin(lcHref,$(this).attr("kw"),'80%','80%');
		}else{
		 left_nav_id=$(this).attr("lqid");util.cookie.set('left_nav_id',left_nav_id);$(this).addClass("active");
		}
	});	
});
</script>
</body>
</html>