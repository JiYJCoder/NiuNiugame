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
      
  <div class="row">
	<div class="col-sm-6 common-search-btn">
    	<?php if(ACTION_NAME == 'index'): ?><label class="btn label-primary" title="<?php echo L('PAGE_LIST');?>"><i class="fa fa-list"></i> <?php echo L('PAGE_LIST');?></label>
            <?php if($os_lock["edit"] == '1'): ?><a class="btn btn-primary" href="<?php echo U('add');?>" title="<?php echo L('PAGE_ADD');?>"><i class="fa fa-plus"></i> <?php echo L('PAGE_ADD');?></a><?php endif; ?>
        <?php elseif(ACTION_NAME == 'add'): ?>
        	<a class="btn btn-primary" href="<?php echo U('index');?>" title="<?php echo L('PAGE_LIST');?>"><i class="fa fa-list"></i> <?php echo L('PAGE_LIST');?></a>
            <label class="btn label-primary" title="<?php echo L('PAGE_ADD');?>"><i class="fa fa-plus"></i> <?php echo L('PAGE_ADD');?></label>
        <?php elseif(ACTION_NAME == 'edit'): ?>
			<a class="btn btn-primary" href="<?php echo ($edit_index_url); ?>" title="<?php echo L('PAGE_LIST');?>"><i class="fa fa-list"></i> <?php echo L('PAGE_LIST');?></a>
            <label class="btn label-primary" title="<?php echo L('PAGE_EDIT');?>"><i class="fa fa-edit"></i> <?php echo L('PAGE_EDIT');?></label>   
        <?php elseif(ACTION_NAME == 'sort'): ?>
        	<a class="btn btn-primary" href="<?php echo U('index');?>" title="<?php echo L('PAGE_LIST');?>"><i class="fa fa-list"></i> <?php echo L('PAGE_LIST');?></a>
            <label class="btn label-primary" title="<?php echo L('PAGE_SORT');?>"><i class="fa fa-sort-numeric-asc"></i> <?php echo L('PAGE_SORT');?></label>   
        <?php else: ?>
        	<a class="btn btn-primary" href="" title="刷新本页"> <i class="fa fa-refresh"></i> 刷新本页 </a><?php endif; ?> 
	</div>
	<div class="col-sm-6" style="float:right;text-align:right;padding:0;margin:0px;"><?php echo ($sys_current); ?></div>
  </div>
  
  <form name="<?php echo ($lq_form_list); ?>" id="<?php echo ($lq_form_list); ?>" class="form-horizontal" onsubmit="return false">
  <input type="hidden" id="thinkphpurl" name="thinkphpurl" value="<?php echo ($page_config["thinkphpurl"]); ?>" />
  <input type="hidden" id="keymode" value="<?php echo ($search_content["keymode"]); ?>" />
  <input type="hidden" id="pagesize" value="<?php echo ($search_content["pagesize"]); ?>" />
  <div class="clearfix welcome-container">
  	<?php if($os_lock["search"] == '1'): ?>    
    <div class="clearfix">
      <div class="panel panel-info">
        <div class="panel-heading"><i class="fa fa-paw"></i> 筛选</div>
        <div class="panel-body">
          <div class="input-group">
            <input type="text" class="form-control" id="fkeyword" value="<?php echo ($search_content["fkeyword"]); ?>" placeholder="<?php echo ($keywordDefault); ?>" onclick="lqKeywordOnclick(this)" onblur="lqKeywordOnblur(this)"/>
            <div class="input-group-btn">
              <button class="btn btn-default" id="commonSearch"><i class="fa fa-search"></i> 搜索</button>
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
              <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li><a href="javascript:;" onclick="$('#fkeyword').attr('placeholder','模糊搜索：请输入关键字');$('#keymode').val(0);">模糊搜索</a></li>
                <li><a href="javascript:;" onclick="$('#fkeyword').attr('placeholder','精准搜索：请输入关键字');$('#keymode').val(1);">精准搜索</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
	<script language="javascript">   
    $(function(){
        //通用搜索
        $("#commonSearch").click(function(){	
            require(['layer'], function(){
                var fkeyword=$("#fkeyword").val();
                if(fkeyword!='<?php echo ($keywordDefault); ?>'){
                    var searchurl=$("#thinkphpurl").val()+'index/s/';
                    var urlpara="fkeyword/"+encodeURIComponent(fkeyword)+"/";
                    urlpara+="keymode/"+encodeURIComponent($("#keymode").val())+"/";
					urlpara+="pagesize/"+encodeURIComponent($("#pagesize").val())+"/";
                    location.href=searchurl+base64encode(urlpara);
                }else{
                    layer.msg("请输入关键字！",{icon:5,time:2000});
                }
            });			
        });
    });
    </script>      
    </div>

    <?php else: ?>
    <?php endif; ?> 
    <div class="row">
      <div class="clearfix template">
        <div class="panel panel-default">
		  
	<div class="panel-body">
          
            <span><i class="fa fa-list-ol"></i> <?php echo ($sys_heading); ?></span>
            <div class="btn-group pull-right">
              <span class="label label-danger blink" id="os_warning" style="font-size:1em;display:none;padding:6px 10px; "> 请操作 <i class="fa fa-hand-o-right"></i></span>
              <button type="button" data-toggle="dropdown" id="os_button"> <i class="fa fa-chevron-down"></i> 操作 </button>
              <ul class="dropdown-menu slidedown" id="os_button_list">
              <?php if(ACTION_NAME == 'index'): if($os_lock["edit"] == '1'): ?><li> <a href="<?php echo U('add');?>" title="<?php echo L('ALT_BUTTON_ADD_RECORD');?>"> <i class="fa fa-plus-circle"></i> 新增 </a> </li>
                <?php else: ?>
                <li class="line-th"> <a href="javascript:;" title="<?php echo L('ALT_BUTTON_ADD_RECORD');?>"> <i class="fa fa-plus-circle"></i> 新增 </a> </li><?php endif; ?>              
                <li class="divider"></li>
              	<?php if($os_lock["cache"] == '1'): ?><li> <a href="<?php echo U('index?clearcache=1');?>" title="<?php echo L('ALT_BUTTON_REFRESH');?>"> <i class="fa fa-refresh"></i> 刷新 </a> </li>
                <?php else: ?>
                <li> <a href="<?php echo U('index');?>" title="<?php echo L('ALT_BUTTON_REFRESH');?>"> <i class="fa fa-refresh"></i> 刷新 </a> </li><?php endif; ?>  
                <li class="divider"></li>
              	<?php if($os_lock["sort"] == '1'): ?><li> <a href="<?php echo U('sort');?>" title="<?php echo L('ALT_BUTTON_SORT_RECORD');?>"> <i class="fa fa-sort-numeric-asc"></i> 排序 </a> </li>
                <?php else: ?>
                <li class="line-th"> <a href="javascript:;" title="<?php echo L('ALT_BUTTON_SORT_RECORD');?>"> <i class="fa fa-sort-numeric-asc"></i> 排序 </a> </li><?php endif; ?>                
                <li class="divider"></li>
                <?php if($os_lock["delete"] == '1'): ?><li> <a href="javascript:;" op="opDeleteCheckbox" title="<?php echo L('ALT_BUTTON_DELETE_RECORD');?>"> <i class="fa fa-times-circle"></i> 删除 </a> </li>
                <?php else: ?>
                <li class="line-th"> <a href="javascript:;" title="<?php echo L('ALT_BUTTON_DELETE_RECORD');?>"> <i class="fa fa-plus-circle"></i> 删除 </a> </li><?php endif; ?>  
                <?php if($os_lock["visible"] == '1'): ?><li> <a href="javascript:;" op="opVisibleCheckbox" state=1 title="<?php echo L('ALT_BUTTON_VISIBLE_YES');?>"> <i class="fa fa-check-square"></i> 审核通过 </a> </li>
                <li> <a href="javascript:;" op="opVisibleCheckbox" state=0 title="<?php echo L('ALT_BUTTON_VISIBLE_NO');?>"> <i class="fa fa-minus-square"></i> 审核否决 </a> </li>
                <?php else: ?>
   				<li class="line-th"> <a href="javascript:;" title="<?php echo L('ALT_BUTTON_VISIBLE_YES');?>"> <i class="fa fa-check-square"></i> 审核通过 </a> </li>
                <li class="line-th"> <a href="javascript:;" title="<?php echo L('ALT_BUTTON_VISIBLE_NO');?>"> <i class="fa fa-minus-square"></i> 审核否决 </a> </li><?php endif; ?>                 
                <li class="divider"></li>
                <li> <a href="javascript:;" title="每页10条" op="pagesize" val="10"> <i class="fa fa-repeat"></i> 每页10条 </a> </li>
   				<li> <a href="javascript:;" title="每页20条" op="pagesize" val="20"> <i class="fa fa-repeat"></i> 每页20条 </a> </li>
   				<li> <a href="javascript:;" title="每页30条" op="pagesize" val="30"> <i class="fa fa-repeat"></i> 每页30条 </a> </li>
              <?php elseif(ACTION_NAME == 'add'): ?>
                <li> <a href="javascript:;" title="<?php echo L('ALT_BUTTON_SAVE_RECORD');?>" id="aFormSubmit"> <i class="fa fa-floppy-o"></i> 保存数据 </a> </li>
                <li class="divider"></li>
                <li> <a href="<?php echo U('index');?>" title="返回列表"> <i class="fa fa-list"></i> 返回列表 </a> </li>
                <li class="divider"></li>
                <li> <a href="" title="刷新本页"> <i class="fa fa-refresh"></i> 刷新本页 </a> </li>
              <?php elseif(ACTION_NAME == 'edit'): ?>
                <li> <a href="javascript:;" title="<?php echo L('ALT_BUTTON_SAVE_RECORD');?>" id="aFormSubmit"> <i class="fa fa-floppy-o"></i> 保存数据 </a> </li>
                <li class="divider"></li>
                <li> <a href="<?php echo U('index');?>" title="返回列表"> <i class="fa fa-list"></i> 返回列表 </a> </li>
                <li class="divider"></li>
                <li> <a href="" title="刷新本页"> <i class="fa fa-refresh"></i> 刷新本页 </a> </li>
                <li class="divider"></li>
                <?php echo ($data_up_down_page); ?>
              <?php elseif(ACTION_NAME == 'sort'): ?>
                <li> <a href="" title="刷新本页"> <i class="fa fa-refresh"></i> 刷新本页 </a> </li>
                <li class="divider"></li>
                <li> <a href="<?php echo U('index');?>" title="返回列表"> <i class="fa fa-list"></i> 返回列表 </a> </li>
              <?php elseif(ACTION_NAME == 'config'): ?>
                <li> <a href="javascript:;" title="<?php echo L('ALT_BUTTON_SAVE_RECORD');?>" id="aFormSubmit"> <i class="fa fa-floppy-o"></i> 保存数据 </a> </li>
                <li class="divider"></li>
                <li> <a href="" title="刷新本页"> <i class="fa fa-refresh"></i> 刷新本页 </a> </li>
                <li class="divider"></li>
                <li> <a href="<?php echo U('add');?>" title="新增数据项"> <i class="fa fa-plus-circle"></i> 新增数据项 </a> </li>
                <li class="divider"></li>
                <li> <a href="<?php echo U('index');?>" title="数据列表"> <i class="fa fa-list"></i> 数据列表 </a> </li>
              <?php else: ?>
                <li> <a href="" title="刷新本页"> <i class="fa fa-refresh"></i> 刷新本页 </a> </li><?php endif; ?> 
              </ul>
            </div>

	</div>
          
          <div class="table-responsive panel-body">
            <table class="table table-hover">
                <thead>
                      <tr>
                        <th style="width:90px;text-align:center;">序号</th>
                        <th style="width:320px;" title="单击分类隐藏/显示该分类下在子类">ID/节点结构</th>
                        <th>排序</th>
                        <th style="width:280px;"><a href="javascript:;" id="model_msg">类型?</a></th>
                        <th>权限通道</th>
                        <th style="width:50px;">状态</th>
                        <th style="width:180px;text-align:center;"><a href="javascript:;" id="op_msg"><?php echo L("LABEL_OS");?>?</a></th>
                      </tr>
                </thead>
                <tbody id="list-tbody">
                      <?php if(is_array($list)): $key = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "$empty_msg" ;else: foreach($__LIST__ as $key=>$data): $mod = ($key % 2 );++$key;?><tr id="<?php echo ($data["id"]); ?>" pid="<?php echo ($data["zn_fid"]); ?>" visible="<?php echo ($data["zl_visible"]); ?>">
                          <td align="center"><?php echo ($data["no"]); ?></td>
                          <td align="left" class="tree" style="cursor: pointer;"><?php echo ($data["id"]); ?>/<?php echo ($data["fullname"]); ?></td>
                          <td edit="0" op="sort" title="<?php echo L('ALT_BUTTON_EDIT_SORT');?>"><?php echo ($data["zn_sort"]); ?></td>
                          <td edit="0" type="type" title="类型"><?php echo ($data["model"]); ?></td>
                          <td><?php echo ($data["pop_label"]); ?></td>
                          <td><?php echo ($data["visible_label"]); ?></td>
                          <td class="manage-menu list-os-a">
                          <a href="/sys-index.php/SystemMenu/edit/tnid/<?php echo ($data["id"]); ?>" title="<?php echo L("LABEL_OS_EDITID");?>[<?php echo ($data["id"]); ?>]"><i class="fa fa-edit"></i></a>
                          <a href="javascript:void(0);" class="opStatus" val="<?php echo ($data["zl_visible"]); ?>" title="<?php echo L("LABEL_OS_STATUS");?>[<?php echo ($data["id"]); ?>]"><?php echo ($data["visible_button"]); ?></a>
                          <a href="javascript:;" class="opDelete" title="<?php echo L("LABEL_OS_DEL");?>[<?php echo ($data["id"]); ?>]"><i class="fa fa-times-circle"></i></a>
                          </td>
                        </tr><?php endforeach; endif; else: echo "$empty_msg" ;endif; ?>
                </tbody>    
            </table>
            
          </div>
        </div>
      </div>
    </div>
  </div>
  </form>
  
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
<?php if(ACTION_NAME=='index') { echo "$('#list-tbody tr:odd').addClass('tr_odd');//单双行样式\n"; echo "$('.opStatus').click(function(){util.visible($(this),'/sys-index.php/SystemMenu/opVisible');});//快捷启用禁用操作\n"; echo "$('.opDelete').click(function(){util.delete($(this),'/sys-index.php/SystemMenu/opDelete');});//单记录删除操作\n"; echo "$('tbody>tr>td[op]').dblclick(function(){util.ajaxEdit($(this),'/sys-index.php/SystemMenu');});//单项编辑\n"; echo "$('tbody>tr>td>a[op]').click(function(){util.ajaxPropertyA($(this),'/sys-index.php/SystemMenu');});//单项属性切换\n"; } ?>	
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
<script type="text/javascript">
$(function(){
		//树状列表显示隐藏 click
		$(".tree").click(function(){lqTreeDisplay($(this));});
		//快捷改变操作类型 click
		$("tbody>tr>td[type]"). click(function(){
			var obj=$(this);
			var lqval = obj.html();
			var lqtype = obj.attr("type");
			var lqid =  obj.parents("tr").attr("id");
                    if(obj.attr('edit')==0){
                        obj.attr('edit','1').html("<input class='form-control' id='edit_"+lqtype+"_"+lqid+"' value='"+lqval+"' />").find("input").select();
                    }
                    $("#edit_"+lqtype+"_"+lqid).focus().bind("blur",function(){
						var editval = $(this).val();
						var pattern=/^[-\+]?\d+(\.\d+)?$/;
						if(!pattern.test(editval)){
							$(this).parents("td").html(lqval).attr('edit','0');
							return false;
						}						
						if(!/^[1-5]*$/.test(editval)){
							$(this).parents("td").html(lqval).attr('edit','0');
							util.sysMsg(0,'输入的数据不正确,只接受1-5');
							return false;
						}						
						var mytype=new Array();
						mytype[1]="项目(GROUP_NAME)";
						mytype[2]="系统(SYSTEM)";
						mytype[3]="归类(CLASSIFY)";
						mytype[4]="模块(CONTROLLER_NAME)";
						mytype[5]="操作(ACTION_NAME)";
                        $(this).parents("td").html(mytype[$(this).val()]).attr('edit','0');
                        if(lqval!=editval){
                            $.post('/sys-index.php/SystemMenu/opProperty',{tnid:lqid,tntype:editval},function(data){});
                        }
                    })	
			
		});	
});


$('#model_msg').on('click', function(){
	require(['layer'], function(){
			layer.tips("类型:<br>1、项目(GROUP_NAME)，<br>2、系统(SYSTEM)，<br>3、归类(CLASSIFY)，<br>4、模块(CONTROLLER_NAME)，<br>5、操作(ACTION_NAME)", '#model_msg');
	});
});
</script>