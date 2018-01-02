<?php //  - 404页面
$floor=""; 
$page_url='http://'.$_SERVER['HTTP_HOST'] .'/';
$page_title='对不起，您访问的地址不存在或已被清理  -  狸想家平台';
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
<meta charset="UTF-8" />
<title><?php echo $page_title;?></title>
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<meta content="" name="description" />
<meta content="" name="author" />
<!--[if IE]>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<![endif]-->
<link href="/Public/Static/css/bootstrap.min.css" rel="stylesheet">
<link href="/Public/Static/css/font-awesome.min.css" rel="stylesheet">
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->
<style>
body {padding-top: 40px;}
.logo h1 {color: #000000;font-size: 100px;}
@media (max-width: 767px){
  .logo h1 {font-size: 55px;}
}
</style>
<script type="text/javascript" src="/Public/Static/js/lib/jquery-1.11.1.min.js"></script>
</head>
<body >
<div class="container">
  <div class="col-lg-8 col-lg-offset-2 text-center">
    <div class="logo">
      <h1>Error 404 !</h1>
    </div>
    <p class="lead text-muted">对不起，你找的页面不存在</p>
    <p class="lead text-muted">页面将在<span id="wait">5</span>秒后自动跳转到之前页面，如未跳转请点击返回按钮</p>
    <div class="clearfix"></div>
    <div class="col-lg-6 col-lg-offset-3">
      <form action="index.html">
        <div class="input-group">
          <input type="text" placeholder="search .." class="form-control" />
          <span class="input-group-btn">
          <button class="btn btn-primary" type="button"><i class="fa fa-search"></i></button>
          </span> </div>
      </form>
    </div>
    <div class="clearfix"></div>
    <br />
    <div class="col-lg-6  col-lg-offset-3">
      <div class="btn-group btn-group-justified"> <a href="index.html" class="btn btn-primary">集团官网</a> <a href="/" class="btn btn-success">返回平台首页</a> </div>
    </div>
  </div>
</div>

</body>
<script language="javascript" type="text/javascript">
function countDown(secs){ 
 	 if(--secs>0){
	 $("#wait").html(secs-1);
     setTimeout("countDown("+secs+")",1000); 
     }else{
     location.href="<?php echo $page_url;?>";
     }
} 	
window.onload=function(){
	$("#back_a").attr("href","<?php echo $page_url;?>");
	var wait=5;
	$("#wait").html(wait);
	countDown(wait);
}
</script>
</html>
