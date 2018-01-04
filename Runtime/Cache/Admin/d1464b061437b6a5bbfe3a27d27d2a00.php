<?php if (!defined('THINK_PATH')) exit();?> 
<!DOCTYPE html>
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
<style>
	@media screen and (max-width:767px){.login .panel.panel-default{width:90%; min-width:300px;} .login .logo a,.register .logo a{width:300px;}}
	@media screen and (min-width:768px){.login .panel.panel-default{width:70%;}}
	@media screen and (min-width:1200px){.login .panel.panel-default{width:50%;}}
</style>
<div class="login">
  <div class="logo"> <a href="javascript:;" ></a> </div>
  <div class="clearfix" style="margin-bottom:5em;">
    <div class="panel panel-default container">
      <div class="panel-body">
        <form name="LQForm" id="LQForm">
          <input type="hidden" name="admpassword" id="admpassword" value="" />  
          <div class="form-group input-group">
          <div class="input-group-addon"><i class="fa fa-user"></i></div>
          <input maxlength="30" type="text" id="admaccount" name="admaccount" class="form-control input-lg" required controlName="请输入用户帐号" placeholder="请输入用户帐号" dataType="account" value="" />
          </div>
          
          <div class="form-group input-group">
            <div class="input-group-addon"><i class="fa fa-unlock-alt"></i></div>
        	<input maxlength="30" type="password" id="F_cadmpassword" class="form-control input-lg" required controlName="请输入用户密码" placeholder="请输入用户密码" dataType="password" value=""/>
          </div>
 
            
          <div class="form-group input-group">
				<span class="input-group-addon"><i class="fa fa-key"></i></span>
         	    <input maxlength="30" type="text" id="verifycode" name="code" class="form-control input-lg" placeholder="请输入验证码" value="" />
				<span class="input-group-addon" style="padding:0px 3px;background-color:#FFF;"><img title="看不清，点击换一张" id="code_img" alt="" src="<?php echo U('Login/checkCode');?>"></span>
				<span class="input-group-addon"><a href="javascript:;" id="get_code_img">看不清?</a></span>
		  </div>             

             
          <div class="form-group">
            <label class="checkbox-inline input-lg">
              <input type="checkbox" value="true" name="rember">
              记住用户名 </label>
            <div class="pull-right">
              <input type="button" id="lqFormLogin"  name="lqFormLogin"  value="登录" class="btn btn-primary btn-lg" />
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="center-block footer" role="footer">
    <div class="text-center"> Powered by <a href="#"><b><?php echo L('PROJECT_TEAM');?></b></a> v2.0 &copy; 2016-2020 <a href="http://www.jianyu020.net/">www.jianyu020.net</a> </div>
  </div>
</div>
<script>
function iGetInnerText(testStr) {
        var resultStr = testStr.replace(/\ +/g, ""); //去掉空格
        resultStr = testStr.replace(/[ ]/g, "");    //去掉空格
        resultStr = testStr.replace(/[\r\n]/g, ""); //去掉回车换行
        return resultStr;
}
	
$(document).ready(function(){
	$(".login").css('min-height',$(window).height());
	//表单提交 start
	$("#lqFormLogin").click(function(){
	var password=$("#F_cadmpassword").val();
	util.Rsha1(iGetInnerText(password),$("#admpassword"));
	util.commonAjaxSubmit('<?php echo U("Login/login");?>');});
	//看不清
	$("#get_code_img").click(function(){document.getElementById('code_img').src="<?php echo U('Login/checkCode');?>/v/"+Math.random();void(0);});
	//回车
	$(document).keydown(function(){if (event.keyCode == "13") {$('#lqFormLogin').click();}});	
});
</script>
</body>
</html>