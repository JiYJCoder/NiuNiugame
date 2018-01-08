<?php
//项目函数库  @国雾院theone / date:2013/06/27

/**
 * 数据签名认证
 * @param  array $data 被认证的数据
 * @return string       签名
 */
function lq_data_auth_sign($data)
{
    //数据类型检测
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
 * 检测用户是否登录
 * @param  string $type 用户类型 admin:后台级 member:用户级
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function lq_is_login($type = 'admin')
{
    $user = session($type . '_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session($type . '_auth_sign') == lq_data_auth_sign($user) ? $user['id'] : 0;
    }
}

//设置session记录提交的次数，防止多次提交
function set_session_request($type = 'offer', $reset = '')
{
    if (C("REQUEST_SESSION")["$type"]) {
        $check_request = session('check_request');
        if ($reset == '') {
            $check_request[$type] = intval($check_request[$type]) + 1;
            $check_request[$type . "_endtime"] = NOW_TIME;
        } else {
            $check_request[$type] = 0;
            $check_request[$type . "_endtime"] = '';
        }
        session('check_request', $check_request);
    }
}

/*判断session记录提交的次数，防止多次提交
 @param  string $type 引用类别
 * @return integer 0-不可以请求，1-可以请求
*/
function check_session_request($type = 'offer')
{
    $times = C("REQUEST_SESSION")["$type"];//额定次数
    if (!$times) return 1;
    $check_request = session('check_request');
    if ($check_request[$type] >= $times) {//请求次数超过额定
        //判断间隔的时间
        if (($check_request[$type . "_endtime"] + C("REQUEST_INTERVAL")) < NOW_TIME) {
            set_session_request($type, 'reset');
            return 1;
        } else {
            return 0;
        }
    } else {
        return 1;
    }
}

/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string
$Wdbcon_JS=fopen(WEB_ROOT."txt.txt",'w');
 * fwrite($Wdbcon_JS,$str." - ".$key." - ".$salt." - ".$sha1." - ".$pass);
 */
function think_ucenter_md5($str, $key = 'ThinkUCenter')
{
    return '' === $str ? '' : md5(sha1($str) . $key);
}

function lq_ucenter_md5($str, $salt = '000000', $sha1 = 1)
{
    if ('' === $str) {
        return '';
    } else {
        if ($sha1 == 1) $str = sha1($str);
        $str = md5($str . AUTH_KEY . $salt);
        return $str;
    }
}


/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key 加密密钥
 * @param int $expire 过期时间 (单位:秒)
 * @return string
 */
function think_ucenter_encrypt($data, $key, $expire = 0)
{
    $key = md5($key);
    $data = base64_encode($data);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    $str = sprintf('%010d', $expire ? $expire + time() : 0);
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }
    return str_replace('=', '', base64_encode($str));
}

/**
 * 系统解密方法
 * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param string $key 加密密钥
 * @return string
 */
function think_ucenter_decrypt($data, $key)
{
    $key = md5($key);
    $x = 0;
    $data = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data = substr($data, 10);
    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}


////检查文件是否存在服务器
function LQFileExist($tcFilePath, $tnwwwroot = 1)
{
    if ($tnwwwroot) {
        $lcwwwroot = WEB_ROOT;
    } else {
        $lcwwwroot = '';
    }

    if (file_exists($lcwwwroot . $tcFilePath)) {
        return 1;
    } else {
        return 0;
    }
}

//用php从身份证中提取生日,包括15位和18位身份证 
function lqGetIDCardInfo($IDCard)
{
    $result['error'] = 0;//0：未知错误，1：身份证格式错误，2：无错误
    $result['flag'] = '';//0标示成年，1标示未成年
    $result['tdate'] = '';//生日，格式如：2012-11-15
    if (!eregi("^[1-9]([0-9a-zA-Z]{17}|[0-9a-zA-Z]{14})$", $IDCard)) {
        $result['error'] = 1;
        return $result;
    } else {
        if (strlen($IDCard) == 18) {
            $tyear = intval(substr($IDCard, 6, 4));
            $tmonth = intval(substr($IDCard, 10, 2));
            $tday = intval(substr($IDCard, 12, 2));
            if ($tyear > date("Y") || $tyear < (date("Y") - 100)) {
                $flag = 0;
            } elseif ($tmonth < 0 || $tmonth > 12) {
                $flag = 0;
            } elseif ($tday < 0 || $tday > 31) {
                $flag = 0;
            } else {
                $tdate = $tyear . "-" . $tmonth . "-" . $tday . " 00:00:00";
                if ((time() - mktime(0, 0, 0, $tmonth, $tday, $tyear)) > 18 * 365 * 24 * 60 * 60) {
                    $flag = 0;
                } else {
                    $flag = 1;
                }
            }
        } elseif (strlen($IDCard) == 15) {
            $tyear = intval("19" . substr($IDCard, 6, 2));
            $tmonth = intval(substr($IDCard, 8, 2));
            $tday = intval(substr($IDCard, 10, 2));
            if ($tyear > date("Y") || $tyear < (date("Y") - 100)) {
                $flag = 0;
            } elseif ($tmonth < 0 || $tmonth > 12) {
                $flag = 0;
            } elseif ($tday < 0 || $tday > 31) {
                $flag = 0;
            } else {
                $tdate = $tyear . "-" . $tmonth . "-" . $tday . " 00:00:00";
                if ((time() - mktime(0, 0, 0, $tmonth, $tday, $tyear)) > 18 * 365 * 24 * 60 * 60) {
                    $flag = 0;
                } else {
                    $flag = 1;
                }
            }
        }
    }
    $result['error'] = 2;//0：未知错误，1：身份证格式错误，2：无错误
    $result['isAdult'] = $flag;//0标示成年，1标示未成年
    $result['birthday'] = $tdate;//生日日期
    return $result;
}

/**
 * +----------------------------------------------------------
 * 功能：计算文件大小
 * +----------------------------------------------------------
 * @param int $bytes
+----------------------------------------------------------
 * @return string 转换后的字符串
 * +----------------------------------------------------------
 */
function byteFormat($bytes, $ext = 1)
{
    $sizetext = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
    if ($ext == 1) {
        return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $sizetext[$i];
    } else {
        return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2);
    }
}

//返回一维数组 id/value
function lq_return_array_one($tadata, $id = 'id', $value = 'value')
{
    $ladata = array();
    if ($tadata) {
        foreach ($tadata as $lnKey => $laValue) {
            $ladata[$laValue[$id]] = $laValue[$value];
        }
    }
    return $ladata;
}


// url 中文 反解函数
function lq_js_unescape($str)
{
    $str = trim($str);
    if (!$str) return '';
    $ret = '';
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        if ($str[$i] == '%' && $str[$i + 1] == 'u') {
            $val = hexdec(substr($str, $i + 2, 4));
            if ($val < 0x7f) $ret .= chr($val);
            else if ($val < 0x800) $ret .= chr(0xc0 | ($val >> 6)) . chr(0x80 | ($val & 0x3f));
            else $ret .= chr(0xe0 | ($val >> 12)) . chr(0x80 | (($val >> 6) & 0x3f)) . chr(0x80 | ($val & 0x3f));
            $i += 5;
        } else if ($str[$i] == '%') {
            $ret .= urldecode(substr($str, $i, 3));
            $i += 2;
        } else $ret .= $str[$i];
    }
    return $ret;
}

//时间戳转换成时间
// 参数： $tntime 时间  $tntimefull  1全部 0 day   $tnafterhour 小时之后（为空时作用）
function lq_cdate($tntime = '', $tntimefull = 1, $tnafterhour = 0)
{
    if ($tntimefull) {
        $gmdate_format = "Y-m-d H:i:s";
    } else {
        $gmdate_format = "Y-m-d";
    }
    if ($tntime) {
        return date($gmdate_format, $tntime + $tnafterhour);
    } else {
        return date($gmdate_format, time() + $tnafterhour);
    }
}

//时间戳转换成时间
// 参数： $tntime 时间  $tcformat 格式   $tnafterhour 小时之后（为空时作用）
function lq_cdate_format($tntime = '', $tcformat = 'Y-m-d H:i:s', $tnafterhour = 0)
{
    if ($tntime) {
        return date($tcformat, $tntime + $tnafterhour);
    } else {
        return date($tcformat, time() + $tnafterhour);
    }
}

//时间转换成时间戳
function lqMktime($tctime = '')
{
    if ($tctime) {
        return (strtotime($tctime));
    } else {
        return @time();
    }
}

//将字20000101变为2000-01-01
function lq_day_format($tcday = '20000101', $format = '-')
{
    return substr($tcday, 0, 4) . $format . substr($tcday, 4, 2) . $format . substr($tcday, 6, 2);
}

//将录入操作的数据 由数组变成字符串 方便下次录入使用
function lq_array_to_cookiestr($taarray = array())
{
    if (!$taarray) return '';
    $cookiestr = '';
    foreach ($taarray as $lcKey => $lcValue) {
        if (is_array($lcValue)) {
            $tmp = '';
            foreach ($lcValue as $k => $v) {
                $tmp .= $k . "#-02-#" . $v . "#-03-#";
            }
            $cookiestr .= $lcKey . "#-00-#" . $tmp . "#-01-#";
        } else {
            $cookiestr .= $lcKey . "#-00-#" . $lcValue . "#-01-#";
        }
    }
    return $cookiestr;
}

//由字符串变成数组
function lq_cookiestr_to_array($tcstr, $split = '#-01-#', $split_key = '#-00-#')
{
    if (!$tcstr) return array();
    $cookie_array = array();
    $tastr = explode($split, $tcstr);
    foreach ($tastr as $lcKey => $lcValue) {
        $items = explode($split_key, $lcValue);
        $cookie_array[$items[0]] = $items[1];
    }
    return $cookie_array;
}

//最近日期显示
function lq_return_soontime($tntime)
{
    $lntime = time() - $tntime;
    $lcfgstr = "..";
    if ($lntime <= 60) {
        $lcretime = $lcfgstr . $lntime . '秒前';
    } elseif ($lntime > 60 and $lntime <= 60 * 60) {
        $lcretime = $lcfgstr . round($lntime / 60) . '分前';
    } elseif ($lntime > 60 * 60 and $lntime <= 60 * 60 * 24) {
        $lcretime = $lcfgstr . round($lntime / 3600) . '小时前';
    } elseif ($lntime > 60 * 60 * 24 and $lntime <= 60 * 60 * 24 * 7) {
        $lcretime = $lcfgstr . round($lntime / 86400) . '天前';
    } elseif ($lntime > 60 * 60 * 24 * 7 and $lntime <= 60 * 60 * 24 * 30) {
        $lcretime = $lcfgstr . round($lntime / 604800) . '周前';
    } elseif ($lntime > 60 * 60 * 24 * 30 and $lntime <= 60 * 60 * 24 * 365) {
        $lcretime = $lcfgstr . round($lntime / 2592000) . '月前';
    } else {
        $lcretime = $lcfgstr . round($lntime / 31536000) . '年前';
    }
    return $lcretime;
}

//时间段问候语
function lq_time_welcome($talk = '')
{
    $strTimeToString = "000111222334455556666667";
    $strWenhou = array('夜深了，', '凌晨了，', '早上好！', '上午好！', '中午好！', '下午好！', '晚上好！', '夜深了，');
    return $strWenhou[(int)$strTimeToString[(int)date('G', time())]] . $talk;
}

//返回指定日期是星期几
function lq_date_to_week($date)
{
    $datearr = explode("-", $date);     //将传来的时间使用“-”分割成数组
    $year = $datearr[0];       //获取年份
    $month = sprintf('%02d', $datearr[1]);  //获取月份
    $day = sprintf('%02d', $datearr[2]);      //获取日期
    $hour = $minute = $second = 0;   //默认时分秒均为0
    $dayofweek = mktime($hour, $minute, $second, $month, $day, $year);    //将时间转换成时间戳
    $shuchu = date("w", $dayofweek);      //获取星期值
    $weekarray = array("星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六");
    return $weekarray[$shuchu];
}

//两个时间相差
function lq_time_diff($begin_time = 0, $end_time = 0, $title = '两个时间相差', $f = 'dh')
{
    $cle = $end_time - $begin_time; //得出时间戳差值
    $d = floor($cle / 3600 / 24);
    $h = floor(($cle % (3600 * 24)) / 3600);  //%取余
    $m = floor(($cle % (3600 * 24)) % 3600 / 60);
    $s = floor(($cle % (3600 * 24)) % 60);
    if ($f == 'dhms') {
        return $title . " $d 天 $h 小时 $m 分 $s 秒";
    } else {
        return $title . " $d 天 $h 小时";
    }
}

//返数数字0头开始字符
function lq_return_zero_start($max = 10, $thisnum = 1, $tcbefore = "NO.")
{
    $lcvalue = $thisnum;
    $lczoon = '';
    $thisnum_length = strlen($thisnum);
    for ($index = $thisnum_length; $index < $max; $index++) {
        $lczoon .= "0";
    }
    if ($lczoon != '') $lcvalue = $tcbefore . $lczoon . $thisnum;
    return $lcvalue;
}

/**
 * +----------------------------------------------------------
 * 生成随机字符串
 * +----------------------------------------------------------
 * @param int $length 要生成的随机字符串长度
 * @param string $type 随机码类型：0，数字+大写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
 * +----------------------------------------------------------
 * @return string
+----------------------------------------------------------
 */
function lq_random_string($length = 5, $type = 0)
{
    $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789", 5 => "~@#$%^&*(){}[]|");
    if ($type == 0) {
        array_pop($arr);
        $string = implode("", $arr);
    } else if ($type == "-1") {
        $string = implode("", $arr);
    } else {
        $string = $arr[$type];
    }
    $count = strlen($string) - 1;
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $str[$i] = $string[rand(0, $count)];
        $code .= $str[$i];
    }
    return $code;
}

//编码
function lq_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;    // 随机密钥长度 取值 0-32;
    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
    // 当此值为 0 时，则不产生随机密钥

    $key = md5($key ? $key : C("LQ_AUTH_KEY"));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

//可以对中文进行截取和计算长度
function lqAbslength($str)
{
    if (empty($str)) return 0;
    if (function_exists('mb_strlen')) {
        return mb_strlen($str, 'utf-8');
    } else {
        preg_match_all("/./u", $str, $ar);
        return count($ar[0]);
    }
}

/**
 * +----------------------------------------------------------
 * 将一个字符串部分字符用*替代隐藏
 * +----------------------------------------------------------
 * @param string $string 待转换的字符串
 * @param int $bengin 起始位置，从0开始计数，当$type=4时，表示左侧保留长度
 * @param int $len 需要转换成*的字符个数，当$type=4时，表示右侧保留长度
 * @param int $type 转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
 * @param string $glue 分割符
 * +----------------------------------------------------------
 * @return string   处理后的字符串
 * +----------------------------------------------------------
 */
function hideStr($string, $bengin = 0, $len = 4, $type = 0, $glue = "@")
{
    if (empty($string))
        return false;
    $array = array();
    if ($type == 0 || $type == 1 || $type == 4) {
        $strlen = $length = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string, 0, 1, "utf8");
            $string = mb_substr($string, 1, $strlen, "utf8");
            $strlen = mb_strlen($string);
        }
    }
    switch ($type) {
        case 1:
            $array = array_reverse($array);
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = "*";
            }
            $string = implode("", array_reverse($array));
            break;
        case 2:
            $array = explode($glue, $string);
            $array[0] = hideStr($array[0], $bengin, $len, 1);
            $string = implode($glue, $array);
            break;
        case 3:
            $array = explode($glue, $string);
            $array[1] = hideStr($array[1], $bengin, $len, 0);
            $string = implode($glue, $array);
            break;
        case 4:
            $left = $bengin;
            $right = $len;
            $tem = array();
            for ($i = 0; $i < ($length - $right); $i++) {
                if (isset($array[$i]))
                    $tem[] = $i >= $left ? "*" : $array[$i];
            }
            $array = array_chunk(array_reverse($array), $right);
            $array = array_reverse($array[0]);
            for ($i = 0; $i < $right; $i++) {
                $tem[] = $array[$i];
            }
            $string = implode("", $tem);
            break;
        default:
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = "*";
            }
            $string = implode("", $array);
            break;
    }
    return $string;
}


//UTF-8、GB2312都支持的汉字截取函数
//cut_str(字符串, 截取长度, 开始长度, 编码); 
//编码默认为 utf-8 
//开始长度默认为 0 
function lq_cutstr($string, $sublen, $start = 0, $code = 'UTF-8', $tcperstr = "...")
{
    if ($code == 'UTF-8') {
        $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
        preg_match_all($pa, $string, $t_string);

        if (count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen)) . $tcperstr;
        return join('', array_slice($t_string[0], $start, $sublen));
    } else {
        $start = $start * 2;
        $sublen = $sublen * 2;
        $strlen = strlen($string);
        $tmpstr = '';

        for ($i = 0; $i < $strlen; $i++) {
            if ($i >= $start && $i < ($start + $sublen)) {
                if (ord(substr($string, $i, 1)) > 129) {
                    $tmpstr .= substr($string, $i, 2);
                } else {
                    $tmpstr .= substr($string, $i, 1);
                }
            }
            if (ord(substr($string, $i, 1)) > 129) $i++;
        }
        if (strlen($tmpstr) < $strlen) $tmpstr .= $tcperstr;
        return $tmpstr;
    }
}

//清除 html
function lq_kill_html($lcValue, $sublen = 0, $tcnext = '...')
{
    $lcValue = html_entity_decode($lcValue);
    $lcValue = strip_tags($lcValue);
    $qian = array(" ", "　", "\t", "\n", "\r", "&nbsp;");
    $hou = array("", "", "", "", "", " ");
    $lcValue = str_replace($qian, $hou, $lcValue);
    if ($sublen > 0) {
        $lcValue = lq_cutstr($lcValue, $sublen, 0, 'UTF-8', $tcnext);
    }
    return $lcValue;
}

/**
 * +-----------------------------------------------------------------------------------------
 * 删除目录及目录下所有文件或删除指定文件
 * +-----------------------------------------------------------------------------------------
 * @param str $path 待删除目录路径
 * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
 * +-----------------------------------------------------------------------------------------
 * @return bool 返回删除状态
 * +-----------------------------------------------------------------------------------------
 */
function lqDelDirAndFile($path, $delDir = FALSE)
{
    $handle = opendir($path);
    if ($handle) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? lqDelDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
        }
        closedir($handle);
        if ($delDir)
            return rmdir($path);
    } else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return FALSE;
        }
    }
}

/**
 * +----------------------------------------------------------
 * 功能：检测一个目录是否存在，不存在则创建它
 * +----------------------------------------------------------
 * @param string $path 待检测的目录
 * +----------------------------------------------------------
 * @return boolean
+----------------------------------------------------------
 */
function makeDir($path)
{
    return is_dir($path) or (makeDir(dirname($path)) and @mkdir($path, 0777));
}

/**
 * +----------------------------------------------------------
 * 功能：剔除危险的字符信息
 * +----------------------------------------------------------
 * @param string $val
+----------------------------------------------------------
 * @return string 返回处理后的字符串
 * +----------------------------------------------------------
 */
function remove_xss($val)
{
    // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    // this prevents some character re-spacing such as <java\0script>
    // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=@avascript:alert('XSS')>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        // ;? matches the ;, which is optional
        // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
        // @ @ search for the hex values
        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
        // @ @ 0{0,7} matches '0' zero to seven times
        $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
    }

    // now the only remaining whitespace attacks are \t, \n, and \r
    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(&#0{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
            $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
            if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
            }
        }
    }
    return $val;
}

/**
 * +----------------------------------------------------------
 * 生成随机字符串
 * +----------------------------------------------------------
 * @param int $length 要生成的随机字符串长度
 * @param string $type 随机码类型：0，数字+大写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
 * +----------------------------------------------------------
 * @return string
+----------------------------------------------------------
 */
function lqRandCode($length = 5, $type = 0)
{
    $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
    if ($type == 0) {
        array_pop($arr);
        $string = implode("", $arr);
    } else if ($type == "-1") {
        $string = implode("", $arr);
    } else {
        $string = $arr[$type];
    }
    $count = strlen($string) - 1;
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $str[$i] = $string[rand(0, $count)];
        $code .= $str[$i];
    }
    return $code;
}

/**
 * 处理标签扩展
 * @param string $tag 标签名称
 * @param mixed $params 传入参数
 * @return mixed
 */
function lqTag($tag, &$params = NULL)
{
    return \Think\Hook::listen($tag, $params);
}

/**
 * 取得文件扩展
 * @param type $filename 文件名
 * @return type 后缀
 */
function lqFileExt($filename)
{
    $pathinfo = pathinfo($filename);
    return $pathinfo['extension'];
}


/**
 * 根据文件扩展名来判断是否为图片类型
 * @param type $file 文件名
 * @return type 是图片类型返回 true，否则返回 false
 */
function isImage($file)
{
    $ext_arr = array('jpg', 'gif', 'png', 'jpeg');
    //取得扩展名
    $ext = lqFileExt($file);
    return in_array($ext, $ext_arr) ? true : false;
}

/*微信M方法*/
function MM($name = '')
{
    return M($name, C('DB_PREFIX_WP'));
}


/*
———————————————————–
函数名称：lqNumber
简要描述：返回为数字
输入：string
输出： int
———————————————————–
*/
function lqNumber($val)
{
    if (ereg("^[0-9]+$", $val))
        return $val;
    return 0;
}

/*
———————————————————–
电话号码(国内外)
———————————————————–
*/
function is_common_mobile($val)
{
    if (ereg("^[0-9-]{6,30}$", $val))
        return $val;
    return 0;
}

//返回空字符
function lqNull($val)
{
    if (is_null($val)) return '';
    return $val;
}

/*
———————————————————–
函数名称：lqMoney
简要描述：返回货币
输入：string
输出： int
———————————————————–
*/
function lqMoney($val)
{
    return doubleval($val);
}

/*
———————————————————–
函数名称：lq_id_to_label
简要描述：将ID集转为LABEL集
输入：$cacheName,$value,$id,$label
输出： string
———————————————————–
*/
function lq_id_to_label($cacheName, $value, $label = 'zc_caption')
{
    $list = F($cacheName, '', COMMON_ARRAY);
    if (!$list | !$value) return '无';
    $string = '';
    $ids_array = explode(",", $value);
    foreach ($list as $lnKey => $laValue) {
        if (in_array($laValue["id"], $ids_array)) {
            $string .= ',' . $laValue[$label];
        }
    }

    return substr($string, 1);
}


//数据库处理 *******************************************************************************************************************************E


/*
生成 select- option
$laarray  option数姐
$tnid     选中的 option
*/
function lqCreatOption($laarray, $tnid = '', $tcempty = '')
{
    $lc_option_str = '';
    if ($tcempty) $lc_option_str .= '<option value="">' . $tcempty . '</option>';
    if ($laarray) {
        foreach ($laarray as $k => $v) {
            if ($tnid === '') {

            } else {
                if ($tnid == $k) {//&&$tnid
                    $s = ' selected="selected"';
                } else {
                    $s = '';
                }
            }
            $lc_option_str .= "<option value=\"" . $k . "\"$s>" . $v . "</option>";
        }
    }
    return $lc_option_str;
}

//Checkbox 获取的ID集，转安全数据
function lqSafeExplode($string = '')
{
    if ($string === '') return '';
    $idsArray = explode(",", $string);
    $str = '';
    foreach ($idsArray as $k => $v) {
        if ($k == 0) {
            $str .= intval($v);
        } else {
            $str .= ',' . intval($v);
        }
    }
    return $str;
}

/*
强转类型
*/
function lq_intval($val)
{
    return intval($val);
}

function lq_floatval($val)
{
    return floatval($val);
}

/**
 * @desc 根据两点间的经纬度计算距离
 * @param float $lat 纬度值
 * @param float $lng 经度值
 */
function lqGetDistance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6367000;
    $lat1 = ($lat1 * pi()) / 180;
    $lng1 = ($lng1 * pi()) / 180;
    $lat2 = ($lat2 * pi()) / 180;
    $lng2 = ($lng2 * pi()) / 180;
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;
    return round($calculatedDistance);
}

//在测试文本中输出测试数据
function lq_test($data, $log = 0)
{
    $time = lq_cdate();
    if (!is_string($data)) $data = var_export($data, true);
    if ($log == 1) {
        file_put_contents(WEB_ROOT . "Public/txt.txt", "------------------$time start------------------\r\n" . $data . "\r\n\r\n------------------$time end------------------\r\n\r\n\r\n\r\n", FILE_APPEND);
    } else {
        file_put_contents(WEB_ROOT . "txt.txt", "------------------$time start------------------\r\n" . $data . "\r\n\r\n------------------$time end------------------\r\n\r\n\r\n\r\n", FILE_APPEND);
    }
}


// 正则函数处理*************************************************************** s  ****************************************************************************************
/*
———————————————————–
函数名称：isAccount
简要描述：帐号规则:英文、数字
输入：string
输出：boolean
修改日志：——
———————————————————–
*/
//function isAccount($val){
//if( preg_match("/^[a-zA-Z0-9]{4,30}$/", $val) )  return true;
//return false;
//}

function isAccount_hun($val)
{ //中英混合
    if (preg_match("/^[\w\d\x{4e00}-\x{9fa5}-]+$/ui", $val)) return true;
    return false;
}

function isAccount($val)
{
    if (preg_match("/^[a-zA-Z0-9@.]{4,30}$/", $val)) return true;
    return false;
}

/*
———————————————————–
函数名称：isPassword
简要描述：帐号规则:英文、数字
输入：string
输出：boolean
修改日志：——
———————————————————–
*/
function isPassword($val)
{
    if (preg_match("/^[a-zA-Z0-9@#$!*]{6,50}$/", $val)) return true;
    return false;
}

/*
———————————————————–
函数名称：isNumber
简要描述：检查输入的是否为数字
输入：string
输出：boolean
修改日志：——
———————————————————–
*/
function isNumber($val)
{
    if (ereg("^[0-9]+$", $val))
        return true;
    return false;
}

/*
———————————————————–
函数名称：isPhone
简要描述：检查输入的是否为电话
输入：string
输出：boolean
修改日志：——
———————————————————–
*/
function isPhone($val)
{
//eg: xxx-xxxxxxxx-xxx | xxxx-xxxxxxx-xxx …
    if (ereg("^((0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$", $val))
        return true;
    return false;
}

/*
———————————————————–
函数名称：isMobile
简要描述：检查输入的是否为手机号
输入：string
输出：boolean
修改日志：——
———————————————————–
*/
function isMobile($val)
{
//该表达式可以验证那些不小心把连接符“-”写出“－”的或者下划线“_”的等等
    if (preg_match("/1[34578]{1}\d{9}$/", $val))
        return true;
    return false;
}

/*
———————————————————–
函数名称：isPostcode
简要描述：检查输入的是否为邮编
输入：string
输出：boolean
修改日志：——
———————————————————–
*/
function isPostcode($val)
{
    if (ereg("^[0-9]{4,6}$", $val))
        return true;
    return false;
}

/*
———————————————————–
函数名称：isEmail
简要描述：邮箱地址合法性检查
输入：string
输出：boolean
修改日志：——
———————————————————–
*/
function isEmail($val, $domain = "")
{
    if (!$domain) {
        if (preg_match("/^[a-z0-9-_.]+@[\da-z][\.\w-]+\.[a-z]{2,4}$/i", $val)) {
            return true;
        } else
            return false;
    } else {
        if (preg_match("/^[a-z0-9-_.]+@" . $domain . "$/i", $val)) {
            return true;
        } else
            return false;
    }
}//end func

/*
———————————————————–
函数名称：isName
简要描述：只能输入中文
输入：string
输出：boolean
修改日志：——
———————————————————–
*/
function isName($val)
{
    if (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $val))//2008-7-24
    {
        return true;
    }
    return false;
}//end func

/*
———————————————————–
函数名称:isDomain($Domain)
简要描述:检查一个（英文）域名是否合法
输入:string 域名
输出:boolean
修改日志:——
———————————————————–
*/
function isDomain($Domain)
{
    if (!eregi("^[0-9a-z]+[0-9a-z\.-]+[0-9a-z]+$", $Domain)) {
        return false;
    }
    if (!eregi("\.", $Domain)) {
        return false;
    }

    if (eregi("\-\.", $Domain) or eregi("\-\-", $Domain) or eregi("\.\.", $Domain) or eregi("\.\-", $Domain)) {
        return false;
    }

    $aDomain = explode(".", $Domain);
    if (!eregi("[a-zA-Z]", $aDomain[count($aDomain) - 1])) {
        return false;
    }

    if (strlen($aDomain[0]) > 63 || strlen($aDomain[0]) < 1) {
        return false;
    }
    return true;
}


/*
———————————————————–
函数名称:isNumberLength($theelement, $min, $max)
简要描述:检查字符串长度是否符合要求
输入:mixed (字符串，最小长度，最大长度)
输出:boolean
修改日志:——
———————————————————–
*/
function isEngLength($val, $min, $max)
{
    $theelement = trim($val);
    if (ereg("^[a-zA-Z]{" . $min . "," . $max . "}$", $val))
        return true;
    return false;
}

/*
———————————————————–
函数名称：isEnglish
简要描述：检查输入是否为英文
输入：string
输出：boolean
作者：——
修改日志：——
———————————————————–
*/
function isEnglish($theelement)
{
    if (ereg("[\x80-\xff].", $theelement)) return false;
    return true;
}

/*
———————————————————–
函数名称：isChinese
简要描述：检查是否输入为汉字
输入：string
输出：boolean
修改日志：——
———————————————————–
*/
function isChinese($sInBuf)
{
    $iLen = strlen($sInBuf);
    for ($i = 0; $i < $iLen; $i++) {
        if (ord($sInBuf{$i}) >= 0 * 80) {
            if ((ord($sInBuf{$i}) >= 0 * 81 && ord($sInBuf{$i}) <= 0xFE) && ((ord($sInBuf{$i + 1}) >= 0 * 40 && ord($sInBuf{$i + 1}) < 0x7E) || (ord($sInBuf{$i + 1}) > 0x7E && ord($sInBuf{$i + 1}) <= 0xFE))) {
                if (ord($sInBuf{$i}) > 0xA0 && ord($sInBuf{$i}) < 0xAA) {
//有中文标点
                    return false;
                }
            } else {
//有日文或其它文字
                return false;
            }
            $i++;
        } else {
            return false;
        }
    }
    return true;
}

/*
———————————————————–
函数名称：isDate
简要描述：检查日期是否符合0000-00-00
输入：string
输出：boolean
修改日志：——
———————————————————–
*/
function isDate($sDate)
{
    if (ereg("^[0-9]{4}\-[][0-9]{2}\-[0-9]{2}$", $sDate)) {
        return true;
    } else {
        return false;
    }
}

/*
———————————————————–
函数名称：isTime
简要描述：检查日期是否符合0000-00-00 00:00:00
输入：string
输出：boolean
修改日志：——
———————————————————–
*/
function isTime($sTime)
{
    if (ereg("^[0-9]{4}\-[][0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$", $sTime)) {
        return true;
    } else {
        return false;
    }
}

/*
———————————————————–
函数名称:isMoney($val)
简要描述:检查输入值是否为合法人民币格式
输入:string
输出:boolean
修改日志:——
———————————————————–
*/
function isMoney($val)
{
    if (ereg("^[0-9]{1,}$", $val)) return true;
    if (ereg("^[0-9]{1,}\.[0-9]{1,2}$", $val)) return true;
    return false;
}

/*
———————————————————–
函数名称:isIp($val)
简要描述:检查输入IP是否符合要求
输入:string
输出:boolean
修改日志:——
———————————————————–
*/
function isIp($val)
{
    return (bool)ip2long($val);
}

//—————————————————————————–

//url正则表达试
function isUrl($str)
{
    if (ereg("/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/", $str)) {
        return true;
    } else {
        return false;
    }
}

/*
———————————————————–
函数名称：isIDCard
简要描述：检查输入是否为英文
输入：string
输出：boolean
作者：——
修改日志：——
———————————————————–
*/
function isIDCard($str)
{
    if (preg_match("/^(?:\d{15}|\d{18})$/", $str)) return true;
    return false;
}

// 阿拉伯数字转中文大写金额
function lq_num_to_money($num, $mode = true, $sim = true)
{
    if (!is_numeric($num)) return '含有非数字非小数点字符！';
    $char = $sim ? array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九')
        : array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
    $unit = $sim ? array('', '十', '百', '千', '', '万', '亿', '兆')
        : array('', '拾', '佰', '仟', '', '萬', '億', '兆');
    $retval = $mode ? '元' : '点';
    //小数部分
    if (strpos($num, '.')) {
        list($num, $dec) = explode('.', $num);
        $dec = strval(round($dec, 2));
        if ($mode) {
            $retval .= "{$char[$dec['0']]}角{$char[$dec['1']]}分";
        } else {
            for ($i = 0, $c = strlen($dec); $i < $c; $i++) {
                $retval .= $char[$dec[$i]];
            }
        }
    }
    //整数部分
    $str = $mode ? strrev(intval($num)) : strrev($num);
    for ($i = 0, $c = strlen($str); $i < $c; $i++) {
        $out[$i] = $char[$str[$i]];
        if ($mode) {
            $out[$i] .= $str[$i] != '0' ? $unit[$i % 4] : '';
            if ($i > 1 and $str[$i] + $str[$i - 1] == 0) {
                $out[$i] = '';
            }
            if ($i % 4 == 0) {
                $out[$i] .= $unit[4 + floor($i / 4)];
            }
        }
    }
    $retval = join('', array_reverse($out)) . $retval;
    return $retval;
}

//返回正文内容
function lq_format_content($val = '')
{
    if (!$val) return '';
    $val = html_entity_decode($val);
    //$val= str_replace(array("\r\n","\r","\n","\t"),"",$val);
    return str_replace("/uploadfiles/editorfile/", API_DOMAIN . "/uploadfiles/editorfile/", $val);
}

// 正则函数处理 *************************************************************** e  ****************************************************************************************

/**
 * 前端继承U方法
 * URL组装 支持不同URL模式
 * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string|boolean $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $domain 是否显示域名
 * @return string
 */
function PAGE_U($url = '', $vars = '', $suffix = true, $domain = false)
{
    $__APP__ = REL_ROOT . "do";

    // 解析URL
    $info = parse_url($url);
    $url = !empty($info['path']) ? $info['path'] : ACTION_NAME;
    if (isset($info['fragment'])) { // 解析锚点
        $anchor = $info['fragment'];
        if (false !== strpos($anchor, '?')) { // 解析参数
            list($anchor, $info['query']) = explode('?', $anchor, 2);
        }
        if (false !== strpos($anchor, '@')) { // 解析域名
            list($anchor, $host) = explode('@', $anchor, 2);
        }
    } elseif (false !== strpos($url, '@')) { // 解析域名
        list($url, $host) = explode('@', $info['path'], 2);
    }
    // 解析子域名
    if (isset($host)) {
        $domain = $host . (strpos($host, '.') ? '' : strstr($_SERVER['HTTP_HOST'], '.'));
    } elseif ($domain === true) {
        $domain = $_SERVER['HTTP_HOST'];
        if (C('APP_SUB_DOMAIN_DEPLOY')) { // 开启子域名部署
            $domain = $domain == 'localhost' ? 'localhost' : 'www' . strstr($_SERVER['HTTP_HOST'], '.');
            // '子域名'=>array('模块[/控制器]');
            foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule) {
                $rule = is_array($rule) ? $rule[0] : $rule;
                if (false === strpos($key, '*') && 0 === strpos($url, $rule)) {
                    $domain = $key . strstr($domain, '.'); // 生成对应子域名
                    $url = substr_replace($url, '', 0, strlen($rule));
                    break;
                }
            }
        }
    }

    // 解析参数
    if (is_string($vars)) { // aaa=1&bbb=2 转换成数组
        parse_str($vars, $vars);
    } elseif (!is_array($vars)) {
        $vars = array();
    }
    if (isset($info['query'])) { // 解析地址里面参数 合并到vars
        parse_str($info['query'], $params);
        $vars = array_merge($params, $vars);
    }

    // URL组装
    $depr = C('URL_PATHINFO_DEPR');
    $urlCase = C('URL_CASE_INSENSITIVE');
    if ($url) {
        if (0 === strpos($url, '/')) {// 定义路由
            $route = true;
            $url = substr($url, 1);
            if ('/' != $depr) {
                $url = str_replace('/', $depr, $url);
            }
        } else {
            if ('/' != $depr) { // 安全替换
                $url = str_replace('/', $depr, $url);
            }
            // 解析模块、控制器和操作
            $url = trim($url, $depr);
            $path = explode($depr, $url);
            $var = array();
            $varModule = C('VAR_MODULE');
            $varController = C('VAR_CONTROLLER');
            $varAction = C('VAR_ACTION');
            $var[$varAction] = !empty($path) ? array_pop($path) : ACTION_NAME;
            $var[$varController] = !empty($path) ? array_pop($path) : CONTROLLER_NAME;
            if ($maps = C('URL_ACTION_MAP')) {
                if (isset($maps[strtolower($var[$varController])])) {
                    $maps = $maps[strtolower($var[$varController])];
                    if ($action = array_search(strtolower($var[$varAction]), $maps)) {
                        $var[$varAction] = $action;
                    }
                }
            }
            if ($maps = C('URL_CONTROLLER_MAP')) {
                if ($controller = array_search(strtolower($var[$varController]), $maps)) {
                    $var[$varController] = $controller;
                }
            }
            if ($urlCase) {
                $var[$varController] = parse_name($var[$varController]);
            }
            $module = '';

            if (!empty($path)) {
                $var[$varModule] = implode($depr, $path);
            } else {
                if (C('MULTI_MODULE')) {
                    if (MODULE_NAME != C('DEFAULT_MODULE') || !C('MODULE_ALLOW_LIST')) {
                        $var[$varModule] = MODULE_NAME;
                    }
                }
            }
            if ($maps = C('URL_MODULE_MAP')) {
                if ($_module = array_search(strtolower($var[$varModule]), $maps)) {
                    $var[$varModule] = $_module;
                }
            }
            if (isset($var[$varModule])) {
                $module = $var[$varModule];
                unset($var[$varModule]);
            }

        }
    }

    if (C("MODULE_ID") == 2) {
        $url = $__APP__ . '?' . C('VAR_MODULE') . "={$module}&" . http_build_query(array_reverse($var));
    } elseif (C("MODULE_ID") > 2) {
        if (C("systemid")) {
            $url = $__APP__ . '?' . C('VAR_MODULE') . "={$module}&systemid=" . C("systemid") . "&" . http_build_query(array_reverse($var));
        } else {
            $url = $__APP__ . '?' . C('VAR_MODULE') . "={$module}&" . http_build_query(array_reverse($var));
        }
    } else {
        $url = $__APP__ . '?' . C('VAR_MODULE') . "={$module}&" . http_build_query(array_reverse($var));
    }
    if ($urlCase) {
        $url = strtolower($url);
    }
    if (!empty($vars)) {
        $vars = http_build_query($vars);
        $url .= '&' . $vars;
    }
    if (isset($anchor)) {
        $url .= '#' . $anchor;
    }
    if ($domain) {
        $url = (is_ssl() ? 'https://' : 'http://') . $domain . $url;
    }
    return $url;

}

//格式化图册
function lq_thumb_format($album = '', $key = "album")
{
    if (!$album) return 0;
    $path_array = array();
    foreach (explode(",", $album) as $k => $v) {
        $path_array[] = array("key" => $key, "path" => $v);
    }
    return $path_array;
}

/*
缩略图处理
缩略图相关常量定义
    const IMAGE_THUMB_SCALE     =   1 ; //常量，标识缩略图等比例缩放类型
    const IMAGE_THUMB_FILLED    =   2 ; //常量，标识缩略图缩放后填充类型
    const IMAGE_THUMB_CENTER    =   3 ; //常量，标识缩略图居中裁剪类型
    const IMAGE_THUMB_NORTHWEST =   4 ; //常量，标识缩略图左上角裁剪类型
    const IMAGE_THUMB_SOUTHEAST =   5 ; //常量，标识缩略图右下角裁剪类型
    const IMAGE_THUMB_FIXED     =   6 ; //常量，标识缩略图固定尺寸缩放类
*/
function lq_thumb_deal($path_array = array(), $id = 0, $key = 'images')
{
    $thumb_path = array();
    if ($path_array && $id) {
        $thumb_config = C('INT_THUMB_SIZE');//生成缩略图的配置
        $thumb_image = new \Think\Image();
        foreach ($path_array as $k => $v) {
            $openfile = substr($v["path"], 1);
            if (file_exists(WEB_ROOT . $openfile) && $openfile) {
                $width = intval($thumb_config[$v["key"]]["width"]);
                $height = intval($thumb_config[$v["key"]]["height"]);
                $type = intval($thumb_config[$v["key"]]["type"]);
                $pathinfo = pathinfo($openfile);
                $thumb_savepath = REL_ROOT . C("UPLOAD_PATH")["folder"] . C("UPLOAD_PATH")["list"][$key] . "_thumb/" . $id . "_" . $k . "_" . $pathinfo["basename"];
                $thumb_image->open(WEB_ROOT . $openfile)->thumb($width, $height, $type)->save(WEB_ROOT . $thumb_savepath);
                $thumb_path[] = $thumb_savepath;
            }
        }
    }
    return $thumb_path;
}

/**
<<<<<<< HEAD
 * 发送模板短信函数
 * @param mobile 手机号码集合,用英文逗号分开
 * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
 * @param $tempId 模板Id,测试应用和未上线应用使用测试模板请填写1，正式应用上线后填写已申请审核通过的模板ID
 */
function lqSendSms($mobile, $datas, $tempId)
{
    //主帐号,对应开官网发者主账号下的 ACCOUNT SID
    $accountSid = '8a216da856588e5a0156594c3a7200f7';
    //主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
    $accountToken = '328cb6c2884b40cfaf37d518b543e423';
    //应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
    $appId = '8aaf070857dc0e780157e01277e3029f';
    //请求地址
    $serverIP = 'app.cloopen.com';
    //请求端口，生产环境和沙盒环境一致
    $serverPort = '8883';
    //REST版本号，在官网文档REST介绍中获得。
    $softVersion = '2013-12-26';

    $rest = new \LQLibs\Util\CcpSms($serverIP, $serverPort, $softVersion);//容联云通讯接口类
    $rest->setAccount($accountSid, $accountToken);
    $rest->setAppId($appId);
    // 发送模板短信
    $result = $rest->sendTemplateSMS($mobile, $datas, $tempId);
    if ($result == NULL) {
        return array('status' => 0, 'msg' => 'result error!');
    }
    if ($result->statusCode != 0) {
        return array('status' => 0, 'msg' => $result->statusMsg);
    } else {
        return array('status' => 1, 'msg' => $result->dateCreated);
    }
=======
  * 发送模板短信函数
  * @param mobile 手机号码集合,用英文逗号分开
  * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
  * @param $tempId 模板Id,测试应用和未上线应用使用测试模板请填写1，正式应用上线后填写已申请审核通过的模板ID
  */
function lqSendSms($mobile,$datas,$tempId){
	//主帐号,对应开官网发者主账号下的 ACCOUNT SID
	$accountSid= '8a216da856588e5a0156594c3a7200f7';
	//主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
	$accountToken= '328cb6c2884b40cfaf37d518b543e423';
	//应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
	$appId='8aaf070857dc0e780157e01277e3029f';
	//请求地址
	$serverIP='app.cloopen.com';
	//请求端口，生产环境和沙盒环境一致
	$serverPort='8883';
	//REST版本号，在官网文档REST介绍中获得。
	$softVersion='2013-12-26';
     
	 $rest = new \LQLibs\Util\CcpSms($serverIP,$serverPort,$softVersion);//容联云通讯接口类
     $rest->setAccount($accountSid,$accountToken);
     $rest->setAppId($appId);
     // 发送模板短信
     $result = $rest->sendTemplateSMS($mobile,$datas,$tempId);
     if($result == NULL ) {
         return array('status'=>0,'msg'=>'result error!');
     }
     if($result->statusCode!=0) {
		 return array('status'=>0,'msg'=>$result->statusMsg);
     }else{
		 return array('status'=>1,'msg'=>$result->dateCreated);
     }
>>>>>>> b02a4f1343254168e17a02caf460de0d2caa00fc
}

//当前url
function lq_get_url()
{
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
    return strip_tags($sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url);
}

//header 跳转
function lq_header($url, $js = 0, $replace = true, $http_response_code = 200)
{
    if ($js == 1) {
        echo '<script type="text/javascript">window.location.href="' . $url . '";</script>';
        exit();
    }
    if ($replace == 1) {
        @header($url, $replace, $http_response_code);
    } else {
        @header($url, $replace);
    }
}

//获得上次表单的记忆数据 1:清除记忆
function lq_post_memory_data($kill = 0)
{
    if ($kill == 1) {
        setcookie("last_post_cookie", NULL, time() - 3600);
        return 0;
    }
    if ($_COOKIE['last_post_cookie']) {
        $form_array = lq_cookiestr_to_array($_COOKIE['last_post_cookie']);
    } else {
        $form_array = array();
    }
    return $form_array;
}

/*产生房号*/
function create_room_code($member_id, $max_len = 6)
{
    $room_count = M("Room")->where("zn_member_id=" . $member_id)->count();
    $member_len = strlen($member_id . ($room_count + 1));

    $arr = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    shuffle($arr);//打乱元素顺序

    if ($max_len <= $member_len) $max_len = $member_len + 1;
    $sub_len = $max_len - $member_len;
    $rand = array_slice($arr, 0, $sub_len);//取前四个元素
    $result = $member_id . ($room_count + 1) . implode('', $rand);//转成字符串
    return $result;
}


//微信昵称处理
function lq_set_nickname($str = '')
{
    if ($str) {
        $tmpStr = json_encode($str);
        $tmpStr2 = preg_replace("#(\\\ud[0-9a-f]{3})#ie", "", $tmpStr);
        $return = json_decode($tmpStr2);
        if (!$return) {
            return jsonName($return);
        }
    } else {
        $return = '微信用户-' . time();
    }
    return $return;
}


function pr($data)
{
    echo "<prev>";
    print_r($data);
    echo "</prev>";
}

?>