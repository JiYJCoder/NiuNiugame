<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>跳转提示</title>
</head>
<link rel="shortcut icon" href="/Public/Static/images/favicon.ico" />
<link href="/Public/Static/css/bootstrap.min.css" rel="stylesheet">
<link href="/Public/Static/css/font-awesome.min.css" rel="stylesheet">
<body>

<div class="row">

    <div class="container-fluid">
    <present name="message">
    
      <div class="jumbotron clearfix alert alert-success">
        <div class="row">
          <div class="col-xs-12 col-sm-3 col-lg-2"> <i class="fa fa-5x fa-check-circle"></i> </div>
          <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
            <h2>操作成功：</h2>
            <p><?php echo($message); ?></p>
            <p class="jump"> 页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 等待时间： <b id="wait">3</b>
          </div>
        </div>
      </div>
      
    <else/>
    
      <div class="jumbotron clearfix alert alert-danger">
        <div class="row">
          <div class="col-xs-12 col-sm-3 col-lg-2"> <i class="fa fa-5x fa-check-circle"></i> </div>
          <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
            <h2>操作失败</h2>
            <p>错误提示：<?php echo($error); ?></p>
            <p class="jump"> 页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 等待时间： <b id="wait">3</b>
          </div>
        </div>
      </div>  
      
    </present>
    </div>
    
</div>

<div class="container-fluid footer" role="footer">
  <div class="page-header"></div>
  <span class="pull-left">
  <p>Powered by <a href="#"><b>狸想家精英团队</b></a> v2.0 &copy; 2016-2020 <a href="#">www.jianyu020.com</a></p>
  </span> 
  <span class="pull-right">
  <a href="#"><b>集团网</b></a> |
  <a href="#"><b>狸想家</b></a>
  </span> 
</div>

    <script type="text/javascript">
        (function(){
            var wait = document.getElementById('wait'),
                href = document.getElementById('href').href;
            var interval = setInterval(function(){
                var time = --wait.innerHTML;
                if(time <= 0) {
                    location.href = href;
                    clearInterval(interval);
                };
            }, 1000);
        })();
    </script>
    
</body>
</html>