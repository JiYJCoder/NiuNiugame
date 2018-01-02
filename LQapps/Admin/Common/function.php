<?php //管理员公共函数

/*将CONTROLLER_NAME转化为数据表*/
function CONTROLLER_TO_TABLE($str){
    $returnstr='';
    for($lnindex=0;$lnindex<strlen($str);$lnindex++){
      $letters=substr($str,$lnindex,1);
      if(preg_match('/^[A-Z]+$/',$letters)&&$lnindex>0){
        $returnstr.="_".$letters;
      }else{
        $returnstr.=$letters;
      }
    } 
    return strtolower($returnstr);
}
/*将数据表转化为CONTROLLER_NAME的相对应的数列*/
function system_controller_to_key($str){
  if(!$str) return'';
  $arr = explode("_",$str);
  $key_str_temp='';
  $key_str='';
  foreach ($arr as $k => $v) {
    $key_str_temp.=$v;
  }
  for($lnindex=0;$lnindex<strlen($key_str_temp);$lnindex++){
    if($lnindex==0){
      $key_str.=strtoupper(substr($key_str_temp,$lnindex,1));
    }else{  $key_str.=substr($key_str_temp,$lnindex,1); }
  } 
  return $key_str;
}


//当前位置的显示
function lqSysmuenLocation($tcStr=''){
  $lcLocation='<ol class="breadcrumb" style="padding:10px 0px;margin:5px 0px;"><span><a><i class="fa fa-location-arrow"></i> 当前位置：</a></span><li><a href="'.U("Index/index").'" title="">'.L("ADMIN_DESKTOP").'</a></li>';
  
  $lasysArray1=explode("|@1@|",$tcStr);
  $lasysArray2=array_reverse($lasysArray1) ;
  $lnsyscount=(count($lasysArray2)-2);
  
  foreach($lasysArray2 as $lnKey=>$lcValue){
    $laRecords=explode("|@2@|",$lcValue);
    if($lnKey==$lnsyscount){
      $active=" class=\"active\"";
    }else{   $active=""; }
    
    if($laRecords[0]!=""){
      if($laRecords[1]=='nourl'){
        $lcLocation.="<li$active><a href=\"javascript:;\">".$laRecords[0]."</a></li>"; 
      }else{
        if($laRecords[1]==__APP__){
            $lcLocation.="<li$active><a target=\"_parent\" href=\"".$laRecords[1]."\">".$laRecords[0]."</a></li>";
        }else{
            $lcLocation.="<li$active>".$laRecords[0]."</li>";
        }
      }
    }
  }
  $lcLocation.="</ol>";
  return $lcLocation;
}


/**
  +----------------------------------------------------------
 * 原样输出print_r的内容
  +----------------------------------------------------------
 * @param string    $content   待print_r的内容
  +----------------------------------------------------------
 */
function pre($content) {
    echo "<pre>";
    print_r($content);
    echo "</pre>";
}

/**
  +----------------------------------------------------------
 * 加密密码
  +----------------------------------------------------------
 * @param string    $data   待加密字符串
  +----------------------------------------------------------
 * @return string 返回加密后的字符串
 */
function encrypt($data) {
    return md5(C("AUTH_CODE") . md5($data));
}

/**
  +----------------------------------------------------------
 * 将一个字符串转换成数组，支持中文
  +----------------------------------------------------------
 * @param string    $string   待转换成数组的字符串
  +----------------------------------------------------------
 * @return string   转换后的数组
  +----------------------------------------------------------
 */
function strToArray($string) {
    $strlen = mb_strlen($string);
    while ($strlen) {
        $array[] = mb_substr($string, 0, 1, "utf8");
        $string = mb_substr($string, 1, $strlen, "utf8");
        $strlen = mb_strlen($string);
    }
    return $array;
}


/**
  +-----------------------------------------------------------------------------------------
 * 删除目录及目录下所有文件或删除指定文件
  +-----------------------------------------------------------------------------------------
 * @param str $path   待删除目录路径
 * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
  +-----------------------------------------------------------------------------------------
 * @return bool 返回删除状态
  +-----------------------------------------------------------------------------------------
 */
function delDirAndFile($path, $delDir = FALSE) {
    $handle = opendir($path);
    if ($handle) {
        while (false !== ( $item = readdir($handle) )) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
        }
        closedir($handle);
        if ($delDir)
            return rmdir($path);
    }else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return FALSE;
        }
    }
}

//PHP正则表达式提取超链接及其标题
function lq_get_links($content) {  
 $pattern = '/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/i';  
 preg_match_all($pattern, $content, $m);  
 return $m;  
}  


/**
  +----------------------------------------------------------
 * 功能：系统邮件发送函数
  +----------------------------------------------------------
 * @param string $to    接收邮件者邮箱
 * @param string $name  接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body    邮件内容
 * @param string $attachment 附件列表
  +----------------------------------------------------------
 * @return boolean
  +----------------------------------------------------------
 */
function lq_send_mail($to, $name, $subject = '', $body = '', $attachment = null, $config = '') {
    $config = is_array($config) ? $config : C('SYSTEM_EMAIL');
    import('PHPMailer.phpmailer', VENDOR_PATH);         //从PHPMailer目录导class.phpmailer.php类文件
  $mail = new \LQLibs\PHPMaile\phpmailer();//PHPMailer对象
    $mail->CharSet = 'UTF-8';                         //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                                   // 设定使用SMTP服务
//    $mail->IsHTML(true);
    $mail->SMTPDebug = 0;                             // 关闭SMTP调试功能 1 = errors and messages2 = messages only
    $mail->SMTPAuth = true;                           // 启用 SMTP 验证功能
    if ($config['smtp_port'] == 465)
        $mail->SMTPSecure = 'ssl';                    // 使用安全协议
    $mail->Host = $config['smtp_host'];                // SMTP 服务器
    $mail->Port = $config['smtp_port'];                // SMTP服务器的端口号
    $mail->Username = $config['smtp_user'];           // SMTP服务器用户名
    $mail->Password = $config['smtp_pass'];           // SMTP服务器密码
    $mail->SetFrom($config['from_email'], $config['from_name']);
    $replyEmail = $config['reply_email'] ? $config['reply_email'] : $config['reply_email'];
    $replyName = $config['reply_name'] ? $config['reply_name'] : $config['reply_name'];
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($to, $name);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            if (is_array($file)) {
                is_file($file['path']) && $mail->AddAttachment($file['path'], $file['name']);
            } else {
                is_file($file) && $mail->AddAttachment($file);
            }
        }
    } else {
        is_file($attachment) && $mail->AddAttachment($attachment);
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}


function checkCharset($string, $charset = "UTF-8") {
    if ($string == '')
        return;
    $check = preg_match('%^(?:
                                [\x09\x0A\x0D\x20-\x7E] # ASCII
                                | [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
                                | \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
                                | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
                                | \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
                                | \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
                                | [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
                                | \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
                                )*$%xs', $string);

    return $charset == "UTF-8" ? ($check == 1 ? $string : iconv('gb2312', 'utf-8', $string)) : ($check == 0 ? $string : iconv('utf-8', 'gb2312', $string));
}


//前端继承S方法
function PAGE_S($name,$value='',$options=null){
		$options=array('prefix'=>'page_','expire'=>(3600*24*30) );
		return S($name,$value,$options);
}

?>
