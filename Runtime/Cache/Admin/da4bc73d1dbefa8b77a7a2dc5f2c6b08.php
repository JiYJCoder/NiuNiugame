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
          <li><a href="/do?g=api&m=document&a=index" target="_blank"><i class="fa fa-gears"></i> 接口文档</a></li>
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
    <a href="#"> <i class="fa fa-weixin"></i> <span>粉丝列表</span> </a> 
    <a href="#"> <i class="fa fa-users"></i> <span>会员列表</span> </a> 
    <a href="#"> <i class="fa fa-database"></i> <span>会员日志</span> </a> 
    <a href="#"> <i class="fa fa-comments"></i> <span>会员反馈</span> </a> 
    <a href="#"> <i class="fa fa-reorder"></i> <span>装修贷订单</span> </a> 
    <a href="#"> <i class="fa fa-reorder"></i> <span>咨询订单</span> </a> 
    <a href="#"> <i class="fa fa-reorder"></i> <span>家装订单</span> </a> 
    <a href="#" style="color:rgba(203,48,48,1);"> <i class="fa fa-users"></i> <span>管理员列表</span> </a> 
    <a href="#" style="color:rgba(203,48,48,1);"> <i class="fa fa-database"></i> <span>管理员日志</span> </a> 
    </div>


        <div class="panel panel-default" id="scroll" style="margin-top:20px;">
            <div class="panel-heading">
                今日关注指数
            </div>
            <div class="account-stat">
                <div class="account-stat-btn">
                    <div>今日新关注<span id="today_new">0</span></div>
                    <div>今日取消关注<span id="today_cancel">0</span></div>
                    <div>今日净增关注<span id="today_increase">0</span></div>
                    <div>累积关注<span id="today_cumulate">0</span></div>
                    <div>累积授权<span id="today_page_auth">0</span></div>
                    <div>会员注册<span id="today_member">0</span></div>
                </div>
            </div>
        </div>
            
        <div class="panel panel-default" id="scroll" style="margin-top:20px;">
            <div class="panel-heading">
                昨日关注指数
            </div>
            <div class="account-stat">
                <div class="account-stat-btn">
                    <div>昨日新关注<span id="yesterday_new">0</span></div>
                    <div>昨日取消关注<span id="yesterday_cancel">0</span></div>
                    <div>昨日净增关注<span id="yesterday_increase">0</span></div>
                    <div>累积关注<span id="yesterday_cumulate">0</span></div>
                    <div>累积授权<span id="yesterday_page_auth">0</span></div>
                    <div>会员注册<span id="yesterday_member">0</span></div>
                </div>
            </div>
        </div>    
    
    
<div class="panel panel-default">
	<div class="panel-heading">关注指数详解(周度)
	<a class="text-danger" href="/sys-index.php/Follow/index">查看更多</a>
    </div>
	<div class="panel-body">
		<div class="pull-right">
			<div class="checkbox" id="subscribe">
				<label style="color:#57B9E6;"><input checked type="checkbox"> 新关注人数</label>&nbsp;
				<label style="color:#00439d"><input checked type="checkbox"> 取消关注人数</label>&nbsp;
				<label style="color:rgba(149,192,0,1);"><input checked type="checkbox"> 净增人数</label>&nbsp;
				<label style="color:#e7a017;"><input type="checkbox"> 累积关注人数</label>
				<label style="color:#ff0000;"><input type="checkbox"> 会员注册</label>
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
				label: '新关注人数',
				fillColor : "rgba(36,165,222,0.1)",
				strokeColor : "rgba(36,165,222,1)",
				pointColor : "rgba(36,165,222,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(36,165,222,1)",
			},
			flow2: {
				label: '取消关注人数',
				fillColor : "rgba(0,67,157,0.1)",
				strokeColor : "rgba(0,67,157,1)",
				pointColor : "rgba(0,67,157,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(0,67,157,1)",
			},
			flow3: {
				label: '净增人数',
				fillColor : "rgba(149,192,0,0.1)",
				strokeColor : "rgba(149,192,0,1)",
				pointColor : "rgba(149,192,0,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(149,192,0,1)",
			},
			flow4: {
				label: '累计人数',
				fillColor : "rgba(231,160,23,0.1)",
				strokeColor : "rgba(231,160,23,1)",
				pointColor : "rgba(231,160,23,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(231,160,23,1)"
			},
			flow5: {
				label: '会员注册',
				fillColor : "rgba(255,0,0,0.1)",
				strokeColor : "rgba(255,0,0,1)",
				pointColor : "rgba(255,0,0,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(255,0,0,1)"
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

		var url = '<?php echo U("Index/ajaxSearch/tcop/subscribe");?>';
		$.post(url, function(data){
			//今日关注指数
			$("#today_new").html(data.today_datasets.new);
			$("#today_cancel").html(data.today_datasets.cancel);
			$("#today_increase").html(data.today_datasets.increase);
			$("#today_cumulate").html(data.today_datasets.cumulate);
			$("#today_page_auth").html(data.today_datasets.page_auth);
			$("#today_member").html(data.today_datasets.member);
			//昨日关注指数
			$("#yesterday_new").html(data.yesterday_datasets.new);
			$("#yesterday_cancel").html(data.yesterday_datasets.cancel);
			$("#yesterday_increase").html(data.yesterday_datasets.increase);
			$("#yesterday_cumulate").html(data.yesterday_datasets.cumulate);
			$("#yesterday_page_auth").html(data.yesterday_datasets.page_auth);
			$("#yesterday_member").html(data.yesterday_datasets.member);
						
			//关注指数详解
			var datasets = data.datasets;
			if(!chart_subscribe) {
				var label = data.label;
				var ds = $.extend(true, {}, templates_subscribe);
				ds.flow1.data = datasets.new;
				ds.flow2.data = datasets.cancel;
				ds.flow3.data = datasets.increase;
				ds.flow4.data = datasets.cumulate;
				ds.flow5.data = datasets.member;
				var lineChartData = {
					labels : label,
					datasets : [ds.flow1, ds.flow2, ds.flow3, ds.flow4, ds.flow5]
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
    

<div class="panel panel-default">
	<div class="panel-heading">家装订单指数详解(月度)
    <a class="text-danger" href="/sys-index.php/HdOrder/index">查看更多</a>
    </div>
	<div class="panel-body">
		<div class="pull-right">
			<div class="checkbox" id="hd_order">
				<label style="color:#57B9E6;"><input checked type="checkbox"> 咨询</label>&nbsp;
				<label style="color:rgba(149,192,0,1);"><input checked type="checkbox"> 订单</label>&nbsp;
				<label style="color:rgba(203,48,48,1)"><input type="checkbox"> 售后</label>&nbsp;
			</div>
		</div>
		<div style="margin-top:20px">
			<canvas id="myChartHdOrder" width="1200" height="300"></canvas>
		</div>
	</div>
</div>
<script>
	require(['chart', 'daterangepicker'], function(c) {
		var chart_hd_order = null;
		var chart_datasets_hd_order = null;
		var templates_hd_order = {
			flow1: {
				label: '咨询',
				fillColor : "rgba(36,165,222,0.1)",
				strokeColor : "rgba(36,165,222,1)",
				pointColor : "rgba(36,165,222,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(36,165,222,1)",
			},
			flow2: {
				label: '订单',
				fillColor : "rgba(149,192,0,0.1)",
				strokeColor : "rgba(149,192,0,1)",
				pointColor : "rgba(149,192,0,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(149,192,0,1)",
			},
			flow3: {
				label: '售后',
				fillColor : "rgba(203,48,48,0.1)",
				strokeColor : "rgba(203,48,48,1)",
				pointColor : "rgba(203,48,48,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(203,48,48,1)",
			}			
		};

		function refreshDataHdOrder() {
			if(!chart_hd_order || !chart_datasets_hd_order) {
				return;
			}
			var visables = [];
			var i = 0;
			$('#hd_order input[type="checkbox"]').each(function(){
				if($(this).attr('checked')) {
					visables.push(i);
				}
				i++;
			});
			var ds = [];
			$.each(visables, function(){
				var o = chart_datasets_hd_order[this];
				ds.push(o);
			});
			chart_hd_order.datasets = ds;
			chart_hd_order.update();
		}

		var url = '<?php echo U("Index/ajaxSearch/tcop/hdorder");?>';
		$.post(url, function(data){
			//关注指数详解
			var datasets = data.datasets;
			if(!chart_hd_order) {
				var label = data.label;
				var ds = $.extend(true, {}, templates_hd_order);
				ds.flow1.data = datasets.hd_application;
				ds.flow2.data = datasets.hd_order;
				ds.flow3.data = datasets.hd_order_service;
				var lineChartData = {
					labels : label,
					datasets : [ds.flow1, ds.flow2, ds.flow3]
				};
				var ctx = document.getElementById("myChartHdOrder").getContext("2d");
				chart_hd_order = new Chart(ctx).Line(lineChartData, {
					responsive: true
				});
				chart_datasets_hd_order = $.extend(true, {}, chart_hd_order.datasets);
			}
			refreshDataHdOrder();
		});

		$('#hd_order input[type="checkbox"]').on('click', function(){
			$(this).attr('checked', !$(this).attr('checked'))
			refreshDataHdOrder();
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
  <p>Powered by <a href="#"><b><?php echo L('PROJECT_TEAM');?></b></a> v2.0 &copy; 2016-2020 <a href="http://www.jianyuly.com/" target="_blank">www.jianyuly.com</a></p>
  </span> 
  <span class="pull-right">
  <p class="label label-info">{__RUNTIME__} </p>
  </span> 
 </div>

<?php if(ACTION_NAME == 'index'): ?><div class="TopBottomMenu">
	<ul>
		<li><a href="/sys-index.php/Member/index" title="会员列表">会员列表</a></li>
		<li><a href="/sys-index.php/LoanApply/index" title="装修贷订单">装修贷订单</a></li>
		<li><a href="/sys-index.php/HdApplication/index" title="咨询订单">咨询订单</a></li>
		<li><a href="/sys-index.php/HdOrder/index" title="家装订单">家装订单</a></li>
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