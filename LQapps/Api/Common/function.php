<?php //前端公共函数

/*将CONTROLLER_NAME转化为数据表*/
function CONTROLLER_TO_TABLE($str){
		$accessControl=F('accessControl','',COMMON_ARRAY);
		return strtolower($accessControl["controller_to_table"][$str]);
}

//前端继承S方法
function PAGE_S($name,$value='',$options=null){
		if(!$options) $options=array('open'=>1,'prefix'=>'page_','expire'=>(3600*24*30));
		if($options["open"]){
			return S($name,$value,$options);
		}else{
			return 0;	
		}
}


//判断手机访问
function is_mobile_check_substrs($substrs,$text){    
	foreach($substrs as $substr)    
	if(false!==strpos($text,$substr)){return true;}    
	return false;    
}
function is_mobile_access(){    
    $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';    
    $useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';      
    $mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');  
    $mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');    
    $found_mobile=is_mobile_check_substrs($mobile_os_list,$useragent_commentsblock)||is_mobile_check_substrs($mobile_token_list,$useragent);    
    if ($found_mobile){    
        return true;    
    }else{    
        return false;    
    }  
}

//判断API来路
function is_weixin(){ 
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
        return true;
    }  
        return false;
}
function get_api_agent(){
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $is_pc = (strpos($agent, 'windows nt')) ? true : false;
        $is_mac = (strpos($agent, 'mac os')) ? true : false;
        $is_iphone = (strpos($agent, 'iphone')) ? true : false;
        $is_android = (strpos($agent, 'android')) ? true : false;
        $is_ipad = (strpos($agent, 'ipad')) ? true : false;
        
        if($is_pc) return  false;
        if($is_mac) return  true;
        if($is_iphone) return  true;
        if($is_android) return  true;
        if($is_ipad) return  true;
}
/////返回设备类型
function get_agent(){
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $is_pc = (strpos($agent, 'windows nt')) ? true : false;
    $is_mac = (strpos($agent, 'mac os')) ? true : false;
    $is_iphone = (strpos($agent, 'iphone')) ? true : false;
    $is_android = (strpos($agent, 'android')) ? true : false;
    $is_ipad = (strpos($agent, 'ipad')) ? true : false;
    $is_wechat = (strpos($agent, 'micromessenger')) ? true : false;
///WECHAT,PC,IOS,ANDROID
    if($is_pc) return  'PC';
    else if($is_mac) return  'IOS';
    else if($is_iphone) return  'IOS';
    else if($is_android) return  'ANDROID';
    else if($is_ipad) return  'IOS';
    else if($is_wechat) return  'WECHAT';
    else return 'OTHER';
}

function ch_num($num,$mode=true) {
    $char = array("零","一","二","三","四","五","六","七","八","九");
    $dw = array("","十","百","千","","万","亿","兆");
    $dec = "点";
    $retval = "";
    if($mode)
        preg_match_all("/^0*(\d*)\.?(\d*)/",$num, $ar);
    else
        preg_match_all("/(\d*)\.?(\d*)/",$num, $ar);
    if($ar[2][0] != "")
        $retval = $dec . ch_num($ar[2][0],false); //如果有小数，则用递归处理小数
    if($ar[1][0] != "") {
        $str = strrev($ar[1][0]);
        for($i=0;$i<strlen($str);$i++) {
            $out[$i] = $char[$str[$i]];
            if($mode) {
                $out[$i] .= $str[$i] != "0"? $dw[$i%4] : "";
                if($str[$i]+$str[$i-1] == 0)
                    $out[$i] = "";
                if($i%4 == 0)
                    $out[$i] .= $dw[4+floor($i/4)];
            }
        }
        $retval = join("",array_reverse($out)) . $retval;
    }
    return $retval;
}


////二维数组重新排序
function multi_array_sort($multi_array,$sort_key,$sort=SORT_DESC){
    if(is_array($multi_array)){
        foreach ($multi_array as $row_array){
            if(is_array($row_array)){
                $key_array[] = $row_array[$sort_key];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    array_multisort($key_array,$sort,$multi_array);
    return $multi_array;
}
//////工程步骤对应订单显示状态
function get_order_status($step=0)
{
    $status = "";
   if($step < 6){
       $status = "方案";
   }elseif($step == 6){
       $status = "签约";
   }elseif($step == 7){
       $status = "设计";
   }elseif($step > 7 && $step < 12){
       $status = "进场";
   }elseif($step == 12){
       $status = "拆改";
   }elseif($step > 12 && $step < 15){
       $status = "水电安";
   }elseif($step == 15){
       $status = "泥木";
   }elseif($step == 16){
       $status = "墙面";
   }elseif($step == 17){
       $status = "软装";
   }elseif($step > 17 && $step < 20){
       $status = "净化";
   }elseif($step == 20){
       $status = "验房";
   }else{
       $status = "完工";
   }
    return $status;
}
//////工程步骤
function change_step($arr,$now=1)
{
    $progress_step_12 = array();

    foreach($arr as $lnKey => $laValue){

        if($lnKey < 5){
            $step = 1;

            if($laValue['status'] == 1) {
                $progress_step_12[$step]['time'] = date("Y.m.d",$laValue['cdate']);
                $progress_step_12[$step]['step_now'] = 1;
            }
        }elseif($lnKey == 5)
        {
            $step = 2;

            if($laValue['status'] == 1) {
                $progress_step_12[$step]['time'] = date("Y.m.d",$laValue['cdate']);
                $progress_step_12[$step]['step_now'] = 1;
            }
        }elseif($lnKey == 6)
        {
            $step = 3;

            if($laValue['status'] == 1) {
                $progress_step_12[$step]['time'] = date("Y.m.d",$laValue['cdate']);
                $progress_step_12[$step]['step_now'] = 1;
            }
        }elseif($lnKey > 6 and $lnKey < 11)
        {
            $step = 4;

            if($laValue['status'] == 1) {
                $progress_step_12[$step]['time'] = date("Y.m.d",$laValue['cdate']);
                $progress_step_12[$step]['step_now'] = 1;
            }
        }elseif($lnKey == 11)
        {
            $step = 5;

            if($laValue['status'] == 1) {
                $progress_step_12[$step]['time'] = date("Y.m.d",$laValue['cdate']);
                $progress_step_12[$step]['step_now'] = 1;
            }
        }
        elseif($lnKey > 11 and $lnKey < 14)
        {
            $step = 6;

            if($laValue['status'] == 1) {
                $progress_step_12[$step]['time'] = date("Y.m.d",$laValue['cdate']);
                $progress_step_12[$step]['step_now'] = 1;
            }
        }
        elseif($lnKey == 14)
        {
            $step = 7;

            if($laValue['status'] == 1) {
                $progress_step_12[$step]['time'] = date("Y.m.d",$laValue['cdate']);
                $progress_step_12[$step]['step_now'] = 1;
            }
        }
        elseif($lnKey == 15)
        {
            $step = 8;

            if($laValue['status'] == 1) {
                $progress_step_12[$step]['time'] = date("Y.m.d",$laValue['cdate']);
                $progress_step_12[$step]['step_now'] = 1;
            }
        }
        elseif($lnKey == 16)
        {
            $step = 9;

            if($laValue['status'] == 1) {
                $progress_step_12[$step]['time'] = date("Y.m.d",$laValue['cdate']);
                $progress_step_12[$step]['step_now'] = 1;
            }
        }
        elseif($lnKey > 16 and $lnKey < 19)
        {
            $step = 10;
            $progress_step_12[$step]['step_now'] = 0;
            if($laValue['status'] == 1) {
                $progress_step_12[$step]['time'] = date("Y.m.d",$laValue['cdate']);
                $progress_step_12[$step]['step_now'] = 1;
            }
        }
        elseif($lnKey == 19)
        {
            $step = 11;
            $progress_step_12[$step]['step_now'] = 0;
            if($laValue['status'] == 1) {
                $progress_step_12[$step]['time'] = date("Y.m.d",$laValue['cdate']);
                $progress_step_12[$step]['step_now'] = 1;
            }
        }
        elseif($lnKey == 20)
        {
            $step = 12;

            if($laValue['status'] == 1) {
                $progress_step_12[$step]['time'] = date("Y.m.d",$laValue['cdate']);
                $progress_step_12[$step]['step_now'] = 1;
            }
        }

    }
    return $progress_step_12;
}

////去除数组中不使用的字段
function clean_no_use($array,$no_use_array=array()){
    if(!$array) return;

   foreach($array as $lnKey => $laValue){
        foreach($no_use_array as $k => $v)
        {
            unset($array[$lnKey][$v]);
        }
    }
    return $array;
}

//获取 微信openid
function lq_return_openid($url=''){
	//接入微信类
	import('Vendor.Wechat.TPWechat');   
	$WxObj = new \Wechat(C("WECHAT"));
	if(!$url) $url=$_SERVER["REQUEST_URI"];
	$url="http://".$_SERVER['HTTP_HOST'].$url;
	if(!isset($_GET['code'])){
		$url=$WxObj->getOauthRedirect($url);
		lq_header("Location:".$url);
	}
	$user_oauth = $WxObj->getOauthAccessToken();
	if($user_oauth){
		$model_member = new \Member\Api\MemberApi;//实例化会员
		$UserInfo=$WxObj->getUserInfo($user_oauth["openid"]);
		if($UserInfo['subscribe']!=1){
			$UserInfo=$WxObj->getOauthUserinfo($user_oauth["access_token"],$user_oauth["openid"]);
			//入粉丝库
			$dataFollow=array();
			$dataFollow["zl_type"]=2;//页面授权
			$dataFollow["zc_openid"]=$UserInfo["openid"];
			$dataFollow["zc_nickname"]=lq_set_nickname($UserInfo["nickname"]); 
			$dataFollow["zn_sex"]=$UserInfo["sex"];
			$dataFollow["zc_country"]=$UserInfo["country"];
			$dataFollow["zc_province"]=$UserInfo["province"];
			$dataFollow["zc_city"]=$UserInfo["city"];
			$dataFollow["zc_language"]=$UserInfo["language"];
			$dataFollow["zc_headimg_url"]=$UserInfo["headimgurl"];
			$dataFollow["zc_remark"]=lqNull($UserInfo["remark"]);
			$dataFollow["zn_groupid"]=intval($UserInfo["groupid"]);
			$dataFollow["zn_subscribe_time"]=$dataFollow["zn_unsubscribe_time"]=0;
			$dataFollow["zl_visible"]=0;
			$dataFollow["zn_cdate"]=NOW_TIME;
			if(!$model_member->apiFollowCount("zc_openid='".$user_oauth["openid"]."'")){
				$model_member->apiInsertFollow($dataFollow);
			}			
		}
		
		session('openid', $UserInfo["openid"]);
		session('nickname',$UserInfo["nickname"]);
		$login_member_info =$model_member->loginByopenid($user_oauth["openid"]);
		if($login_member_info!=-1){
				$token=$model_member->apiGetToken($login_member_info["id"]);
				if($token){
					
						exit('<!DOCTYPE html><head><meta charset="utf-8"><title>登录中</title>
						<script type="text/javascript" src="/Public/Static/js/lib/jquery-1.11.1.min.js"></script>
						<script type="text/javascript" src="/Public/Static/js/lib/cookie.js"></script>
						<meta name="viewport" content="initial-scale=1, maximum-scale=1"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black"><style>body,ul,ol,li,p,h1,h2,h3,h4,h5,h6,form,fieldset,table,td,img,div{margin:0;padding:0;border:0;}body{color:#333; font-size:14px;font-family:"Microsoft YaHei";}ul,ol{list-style-type:none;}.msg{ margin:1.2rem auto 0; width:100%; font-size:1.5rem; color:#F00;}</style></head><body>
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
						  <tr><td height="200" align="center" valign="middle"><img src="/Public/Static/images/loading-bar.gif" alt="登录中..." width="214" height="15" /></td></tr>
						  <tr><td height="73" align="center"><p>正在进入，请稍后...</p></td></tr>
						</table>
						</body></html>
						<script type="text/javascript">
							var referer_url=$.cookie("referer");
							$.cookie("openid","'.$user_oauth["openid"].'"); 
							$.cookie("uid","'.$token["zn_member_id"].'"); 
							$.cookie("token","'.$token["zc_token"].'");
							if(typeof referer_url=="undefined"|referer_url==""|referer_url==null){
							location.href="http://wx.lxjjz.cn/wx/views/my/index.html";
							}else{
							location.href=referer_url;
							}
						</script>');					
					
				}else{

						$data = array();
						$data["client_type"] = 'WECHAT';
						$data["id"] = $login_member_info["id"];
						$data["zc_account"] = $login_member_info["zc_account"];
						$data["zl_role"] = $login_member_info["zl_role"];
						$data["zn_last_login_time"] = $login_member_info["zn_last_login_time"];						
						$data["zc_openid"] = $login_member_info["zc_openid"];
						$token = $model_member->apiInsertToken($data);
						
						exit('<!DOCTYPE html><head><meta charset="utf-8"><title>登录中</title>
							<script type="text/javascript" src="/Public/Static/js/lib/jquery-1.11.1.min.js"></script>
							<script type="text/javascript" src="/Public/Static/js/lib/cookie.js"></script>
							<meta name="viewport" content="initial-scale=1, maximum-scale=1"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black"><style>body,ul,ol,li,p,h1,h2,h3,h4,h5,h6,form,fieldset,table,td,img,div{margin:0;padding:0;border:0;}body{color:#333; font-size:14px;font-family:"Microsoft YaHei";}ul,ol{list-style-type:none;}.msg{ margin:1.2rem auto 0; width:100%; font-size:1.5rem; color:#F00;}</style></head><body>
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
							  <tr><td height="200" align="center" valign="middle"><img src="/Public/Static/images/loading-bar.gif" alt="登录中..." width="214" height="15" /></td></tr>
							  <tr><td height="73" align="center"><p>正在进入，请稍后...</p></td></tr>
							</table>
							</body></html>
							<script type="text/javascript">
							$.cookie("openid","'.$user_oauth["openid"].'"); 
							$.cookie("uid","'.$login_member_info["id"].'"); 
							$.cookie("token","'.$token.'");
							location.href="http://wx.lxjjz.cn/wx/views/my/index.html";
						</script>');

				}
		}else{
					exit('<!DOCTYPE html><head><meta charset="utf-8"><title>处理中</title>
						<script type="text/javascript" src="/Public/Static/js/lib/jquery-1.11.1.min.js"></script>
						<script type="text/javascript" src="/Public/Static/js/lib/cookie.js"></script>
						<meta name="viewport" content="initial-scale=1, maximum-scale=1"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black"><style>body,ul,ol,li,p,h1,h2,h3,h4,h5,h6,form,fieldset,table,td,img,div{margin:0;padding:0;border:0;}body{color:#333; font-size:14px;font-family:"Microsoft YaHei";}ul,ol{list-style-type:none;}.msg{ margin:1.2rem auto 0; width:100%; font-size:1.5rem; color:#F00;}</style></head><body>
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
						  <tr><td height="200" align="center" valign="middle"><img src="/Public/Static/images/loading-bar.gif" alt="处理中..." width="214" height="15" /></td></tr>
						  <tr><td height="73" align="center"><p>正在进入，请稍后...</p></td></tr>
						</table>
						</body></html>
						<script type="text/javascript">
							var referer_url=$.cookie("referer");
							$.cookie("openid","'.$user_oauth["openid"].'"); 
							$.cookie("uid",""); 
							$.cookie("token","");
							if(typeof referer_url=="undefined"|referer_url==""|referer_url==null){
							location.href="http://wx.lxjjz.cn/wx/views/my/login.html";
							}else{
							location.href=referer_url;
							}						
					</script>');			
		}

	}
} 

//返回微信 jssdk
function lq_get_jssdk($config,$data){
	import('Vendor.Wechat.JSSDK');
	$jssdk = new \JSSDK($config["appid"],$config['appsecret'],$data["url"]);
	$signPackage=$jssdk->GetSignPackage();
	//朋友圈
	$timeline_link = str_replace("&lqtype=1&","&lqtype=2&",$data["link"]);
	
	return 'wx.config({
					debug: false,
					appId: "'.$signPackage["appId"].'",
					timestamp: "'.$signPackage["timestamp"].'",
					nonceStr: "'.$signPackage["nonceStr"].'",
					signature: "'.$signPackage["signature"].'",
					jsApiList: ["onMenuShareTimeline","onMenuShareAppMessage"]
				});	
				wx.ready(function () {
					wx.onMenuShareTimeline({
					title: "'.$data["title"].'",
					link: "'.$data["link"].'",
					imgUrl: "'.$data["imgUrl"].'",
					desc: "'.$data["desc"].'",
					success: function () { 
					// 用户确认分享后执行的回调函数
					
					},
					cancel: function () { 
					// 用户取消分享后执行的回调函数
					}
					});

					wx.onMenuShareAppMessage({
					title: "'.$data["title"].'",
					desc: "'.$data["desc"].'",
					link: "'.$timeline_link.'",
					imgUrl: "'.$data["imgUrl"].'",
					success: function () { 
					// 用户确认分享后执行的回调函数
					
					},
					cancel: function () { 
					// 用户取消分享后执行的回调函数
					}
					});	
					
			});';
				
}


//寻址失败
function go_error_page($url=''){
	$page_url='http://'.$_SERVER['HTTP_HOST'] .'/';
	
	exit('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>数据出错,寻址失败</title><meta content="width=device-width,initial-scale=1" name="viewport"><meta content="" name="description"><meta content="" name="author"><link href="/Public/Static/css/bootstrap.min.css" rel="stylesheet"><link href="/Public/Static/css/font-awesome.min.css" rel="stylesheet"><style>body{padding-top:40px}.logo h1{color:#000;font-size:100px}@media (max-width:767px){.logo h1{font-size:55px}}</style><script type="text/javascript" src="/Public/Static/js/lib/jquery-1.11.1.min.js"></script></head><body><div class="container"><div class="col-lg-8 col-lg-offset-2 text-center"><div class="logo"><h1>Error!</h1></div><p class="lead text-muted">数据出错,寻址失败</p><p class="lead text-muted">页面将在<span id="wait">5</span>秒后自动跳转到之前页面，如未跳转请点击返回按钮</p><div class="clearfix"></div><div class="col-lg-6 col-lg-offset-3"></div><div class="clearfix"></div><br><div class="col-lg-6 col-lg-offset-3"><div class="btn-group btn-group-justified"><a href="#" onclick="window.history.back();" class="btn btn-primary">返回上一步</a> <a href="'.$page_url.'" class="btn btn-success">返回首页</a></div></div></div></div></body><script language="javascript" type="text/javascript">function countDown(secs){if(--secs>0){$("#wait").html(secs-1);setTimeout("countDown("+secs+")",1000)}else{window.history.back()}}window.onload=function(){var wait=5;$("#wait").html(wait);countDown(wait)}</script></html>');		
	
}
?>
