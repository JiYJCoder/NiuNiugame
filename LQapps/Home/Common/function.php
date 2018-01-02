<?php //前端公共函数

/*将CONTROLLER_NAME转化为数据表*/
function CONTROLLER_TO_TABLE($str){
		$accessControl=F('accessControl','',COMMON_ARRAY);
		return strtolower($accessControl["controller_to_table"][$str]);
}

/**
 * 设置主题
 */
function lq_set_theme($theme=''){
        //判断是否存在设置的模板主题
        if(empty($theme)){
           $theme_name=C('DEFAULT_THEME');
        }else{
           if(is_dir(HOME_VIEW_PATH.$theme)){
              $theme_name=$theme;             
           }else{
              $theme_name=C('DEFAULT_THEME');
           }           
        }
        //替换COMMON模块中设置的模板值    
        C('DEFAULT_THEME', $theme_name);
		C('CACHE_PATH',RUNTIME_PATH . "Cache/".MODULE_NAME."/".$theme_name."/");	
}

//前端继承S方法
function PAGE_S($name,$value='',$options=null){
		if(!$options) $options=array('prefix'=>'page_','expire'=>(3600*24*30));
		return S($name,$value,$options);
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
		$UserInfo=$WxObj->getUserInfo($user_oauth["openid"]);
		if($UserInfo['subscribe']!=1){
			//exit('<!DOCTYPE html><head><meta charset="utf-8"><title>请先关注公众号</title><meta name="viewport" content="initial-scale=1, maximum-scale=1"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black"><style>body,ul,ol,li,p,h1,h2,h3,h4,h5,h6,form,fieldset,table,td,img,div{margin:0;padding:0;border:0;}body{color:#333; font-size:14px;font-family:"Microsoft YaHei";}ul,ol{list-style-type:none;}.msg{ margin:1.2rem auto 0; width:100%; font-size:1.5rem; color:#F00;}</style></head><body><div class="msg">请先关注公众号</div></body></html>');
		}		 
		session('openid', $user_oauth["openid"]);
		session('nickname',$UserInfo["nickname"]);
		lq_header("Location:http://wx.lxjjz.cn/wx/views/my/login.html");
	}
} 
 
?>
