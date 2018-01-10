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


//寻址失败
function go_error_page($url=''){
	$page_url='http://'.$_SERVER['HTTP_HOST'] .'/';
	
	exit('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>数据出错,寻址失败</title><meta content="width=device-width,initial-scale=1" name="viewport"><meta content="" name="description"><meta content="" name="author"><link href="/Public/Static/css/bootstrap.min.css" rel="stylesheet"><link href="/Public/Static/css/font-awesome.min.css" rel="stylesheet"><style>body{padding-top:40px}.logo h1{color:#000;font-size:100px}@media (max-width:767px){.logo h1{font-size:55px}}</style><script type="text/javascript" src="/Public/Static/js/lib/jquery-1.11.1.min.js"></script></head><body><div class="container"><div class="col-lg-8 col-lg-offset-2 text-center"><div class="logo"><h1>Error!</h1></div><p class="lead text-muted">数据出错,寻址失败</p><p class="lead text-muted">页面将在<span id="wait">5</span>秒后自动跳转到之前页面，如未跳转请点击返回按钮</p><div class="clearfix"></div><div class="col-lg-6 col-lg-offset-3"></div><div class="clearfix"></div><br><div class="col-lg-6 col-lg-offset-3"><div class="btn-group btn-group-justified"><a href="#" onclick="window.history.back();" class="btn btn-primary">返回上一步</a> <a href="'.$page_url.'" class="btn btn-success">返回首页</a></div></div></div></div></body><script language="javascript" type="text/javascript">function countDown(secs){if(--secs>0){$("#wait").html(secs-1);setTimeout("countDown("+secs+")",1000)}else{window.history.back()}}window.onload=function(){var wait=5;$("#wait").html(wait);countDown(wait)}</script></html>');		
	
}
?>
