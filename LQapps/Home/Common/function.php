<?php //前端公共函数

/*将CONTROLLER_NAME转化为数据表*/
function CONTROLLER_TO_TABLE($str)
{
    $accessControl = F('accessControl', '', COMMON_ARRAY);
    return strtolower($accessControl["controller_to_table"][$str]);
}

/**
 * 设置主题
 */
function lq_set_theme($theme = '')
{
    //判断是否存在设置的模板主题
    if (empty($theme)) {
        $theme_name = C('DEFAULT_THEME');
    } else {
        if (is_dir(HOME_VIEW_PATH . $theme)) {
            $theme_name = $theme;
        } else {
            $theme_name = C('DEFAULT_THEME');
        }
    }
    //替换COMMON模块中设置的模板值
    C('DEFAULT_THEME', $theme_name);
    C('CACHE_PATH', RUNTIME_PATH . "Cache/" . MODULE_NAME . "/" . $theme_name . "/");
}

//前端继承S方法
function PAGE_S($name, $value = '', $options = null)
{
    if (!$options) $options = array('prefix' => 'page_', 'expire' => (3600 * 24 * 30));
    return S($name, $value, $options);
}


//判断手机访问
function is_mobile_check_substrs($substrs, $text)
{
    foreach ($substrs as $substr)
        if (false !== strpos($text, $substr)) {
            return true;
        }
    return false;
}

/*
 * 抓取远程图片
 */
function curl_get_pic($file_url, $type = "avatar")
{
    if (!$file_url) return;
    $save_to_path = './' . C("UPLOAD_PATH")["folder"] . C("UPLOAD_PATH")["list"][$type] . date("Ymd") . "/";
    if (!is_dir($save_to_path)) mkdir($save_to_path);
    $save_name = time() . lq_random_string(6, 1) . "." . pathinfo($file_url)['extension'];
    $save_to = $save_to_path . $save_name;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_URL, $file_url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $file_content = curl_exec($ch);

    curl_close($ch);
    $downloaded_file = fopen($save_to, 'w');
    fwrite($downloaded_file, $file_content);
    fclose($downloaded_file);
    ////返回完整图片路径，入图片库用
    return substr($save_to, 1);
}

/**
 * +----------------------------------------------------------
 * 功能：系统邮件发送函数
 * +----------------------------------------------------------
 * @param string $to 接收邮件者邮箱
 * @param string $name 接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * +----------------------------------------------------------
 * @return boolean
+----------------------------------------------------------
 */
function lq_send_mail($to, $name, $subject = '', $body = '', $attachment = null, $config = '')
{
    $config = is_array($config) ? $config : F('set_config', '', COMMON_ARRAY);;

    import('PHPMailer.phpmailer', VENDOR_PATH);         //从PHPMailer目录导class.phpmailer.php类文件
    $mail = new \phpmailer();//PHPMailer对象
    $mail->CharSet = 'UTF-8';                         //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();
    $mail->IsHTML(true);
    $mail->SMTPDebug = 1;                             // 关闭SMTP调试功能 1 = errors and messages2 = messages only
    $mail->SMTPAuth = true;                           // 启用 SMTP 验证功能
    if ($config['SMTP_PORT'] == 465)
        $mail->SMTPSecure = 'ssl';                    // 使用安全协议
    $mail->Host = $config['SMTP_SERVER'];                // SMTP 服务器
    $mail->Port = $config['SMTP_PORT'];                // SMTP服务器的端口号
    $mail->Username = $config['SMTP_USER'];           // SMTP服务器用户名
    $mail->Password = $config['SMTP_PASS'];           // SMTP服务器密码
    $mail->SetFrom($config['SMTP_USER'], $config['SMTP_USER']);

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


/**
 * +----------------------------------------------------------
 * 功能：直播按钮判断生成
 * +----------------------------------------------------------
 * @param int $statue 审核状态
 * 审核状态：(1=>"审核中", 2=>"审核通过", 3=>"审核未通过", 4=>"上线状态",5=>"管理员下架",6=>"完结")
 * @param int $id 直播课程id
 * +----------------------------------------------------------
 * @return string  拼接好的字符串 (a标签)
 * +----------------------------------------------------------
 */

function lq_button($status = 1, $id = 0, $reason = '')
{
    // 判断id是否存在
    if ($id == 0) {
        return '请传入id';
    }

    // 6种状态 6种情况

    $str1 = '<a class="hover_btn" href="' . U('teacher/courselist', array(id => $id, type => 1)) . '">查看名单</a>';

    //////////
    $str2 = '<a class="hover_btn" href="' . U('teacher/revisecourse', array(id => $id)) . '" >修改课程资料</a>';
    $str2 .= '<a class="hover_btn" href="' . U('teacher/courselist', array(id => $id, type => 1)) . '">查看名单</a>';
    $str2 .= '<a class="hover_btn gx_look_reason" reason_value="' . $reason . '">查看下架原因</a>';
    $str2 .= '<a class="hover_btn no_pass gx_vod_del" del_id=' . $id . '>删除</a>';

    //////////
    $str3 = '<a class="hover_btn"  href="' . U('teacher/revisecourse', array(id => $id)) . '">修改课程资料</a>';
    $str3 .= '<a class="hover_btn" href="' . U('teacher/courselist', array(id => $id, type => 1)) . '">查看名单</a>';
    $str3 .= '<a class="hover_btn gx_look_reason" reason_value="' . $reason . '">查看未通过原因</a>';
    $str3 .= '<a class="hover_btn no_pass finish-class gx_del_mylive" del_id=' . $id . '>删除</a>';

    //////////
    $str4 = '<a class="hover_btn" href="' . U('teacher/revisecourse', array(id => $id)) . '" >修改课程资料</a>';
    $str4 .= '<a class="hover_btn" href="' . U('teacher/courselist', array(id => $id, type => 1)) . '">查看名单</a>';
    $str4 .= '<a class="hover_btn no_pass finish-class gx_del_mylive" del_id=' . $id . '>删除</a>';

    ////////////
    $str5 = '<a class="hover_btn" href="' . U('teacher/completecourse', array(id => $id)) . '">完善课程资料</a>';
    $str5 .= '<a class="hover_btn" href="' . U('teacher/courselist', array(id => $id, type => 1)) . '">查看名单</a>';
    $str5 .= '<a class="hover_btn no_pass finish-class gx_del_mylive" del_id=' . $id . '>删除</a>';

    ////////////
    $str6 = '<a class="hover_btn" href="' . U('teacher/completecourse', array(id => $id)) . '">完善课程资料</a>';
    $str6 .= '<a class="hover_btn" href="' . U('teacher/courselist', array(id => $id, type => 1)) . '">查看名单</a>';
    $str6 .= '<a class="hover_btn no_pass finish-class gx_finish_class" lesson_id=' . $id . '>结束课程</a>';


    // 设置数组
    $list = array(
        1 => $str1,
        2 => $str2,
        3 => $str3,
        4 => $str4,
        5 => $str5,
        6 => $str6,
    );

    // 返回处理好的a标签
    return $list[$status];
}

/*
 * 下载
 */
function download($file)
{
    $filename = basename($file);
    $file_extension = strtolower(substr(strrchr($filename, "."), 1));
    switch ($file_extension) {
        case "exe" :
            $ctype = "application/octet-stream";
            break;
        case "zip" :
            $ctype = "application/x-zip-compressed";
            break;
        case "rar" :
            $ctype = "application/x-rar";
            break;
        default :
            $ctype = "application/force-download";
    }
    header("Cache-Control:");
    header("Cache-Control: public");
    header("Content-Type: {$ctype}");
    if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
        $iefilename = preg_replace("/\\./", "%2e", $filename, substr_count($filename, ".") - 1);
        header("Content-Disposition: attachment; filename=\"{$iefilename}\"");
    } else {
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
    }
    header("Accept-Ranges: bytes");
    $fp = fopen("{$file}", "rb");
    while (!feof($fp)) {
        set_time_limit(0);
        print fread($fp, 8192);
    }
    fclose($fp);
    exit();
}


/**
 * +----------------------------------------------------------
 * 功能：直播图标判断生成
 * +----------------------------------------------------------
 * @param int $statue 审核状态
 * 审核状态：(1=>"审核中", 2=>"审核通过", 3=>"审核未通过", 4=>"上线状态",5=>"管理员下架",6=>"完结")
 * +----------------------------------------------------------
 * @return string  选择好的状态和对话
 * +----------------------------------------------------------
 */
function lq_picture($status = 1)
{
    $list = array(
        4 => array(
            'status' => 'live_status4',
            'message' => '正在审核中,请耐心等待'
        ),
        5 => array(
            'status' => 'live_status5',
            'message' => '审核通过,请完善资料'
        ),
        3 => array(
            'status' => 'live_status3',
            'message' => '审核未通过,请检查原因'
        ),
        2 => array(
            'status' => 'live_status2',
            'message' => '检查视频内容是否违规'
        ),
        1 => array(
            'status' => 'live_status1',
            'message' => '直播已经结束'
        )
    );
    return $list[$status];
}


/**
 * +----------------------------------------------------------
 * 功能：录播图标判断生成
 * +----------------------------------------------------------
 * @param int $statue 审核状态
 * 审核状态：(1=>"审核中", 2=>"审核通过", 3=>"审核未通过", 4=>"上线状态",5=>"管理员下架",6=>"完结")
 * +----------------------------------------------------------
 * @return string  选择好的状态和对话
 * +----------------------------------------------------------
 */
function vod_picture($status = 1)
{
    $list = array(
        6 => array(
            'status' => 'live_status5',
            'message' => '更新录播中'
        ),
        4 => array(
            'status' => 'live_status4',
            'message' => '正在审核中,请耐心等待'
        ),
        5 => array(
            'status' => 'live_status5',
            'message' => '录播审核通过'
        ),
        3 => array(
            'status' => 'live_status3',
            'message' => '审核未通过,请检查原因'
        ),
        2 => array(
            'status' => 'live_status2',
            'message' => '请检查视频是否违规'
        ),
        1 => array(
            'status' => 'live_status1',
            'message' => '录播已经完结'
        )
    );
    return $list[$status];
}


/**
 * +----------------------------------------------------------
 * 功能：录播按钮判断生成
 * +----------------------------------------------------------
 * @param int $statue 审核状态
 * 审核状态：(1=>"审核中", 2=>"审核通过", 3=>"审核未通过", 4=>"上线状态",5=>"管理员下架",6=>"完结")
 * @param int $id 直播课程id
 * +----------------------------------------------------------
 * @return string  拼接好的字符串 (a标签)
 * +----------------------------------------------------------
 */

function vod_button($status = 1, $id = 0, $reason = '')
{
    // 判断id是否存在
    if ($id == 0) {
        return '请传入id';
    }

    // 6种状态 6种情况

    $str1 = '<a class="hover_btn" href="' . U('teacher/courselist', array(id => $id, type => 2)) . '">查看名单</a>';
    $str1 .= '<a class="hover_btn no_pass gx_vod_del" del_id=' . $id . '>删除</a>';

    //////////
    $str2 = '<a class="hover_btn" href="' . U('teacher/sqbroadchange', array(id => $id)) . '" >修改录播资料</a>';
    $str2 .= '<a class="hover_btn" href="' . U('teacher/courselist', array(id => $id, type => 2)) . '">查看名单</a>';
    $str2 .= '<a class="hover_btn gx_look_reason" reason_value="' . $reason . '">查看下架原因</a>';
    $str2 .= '<a class="hover_btn no_pass gx_vod_del" del_id=' . $id . '>删除</a>';

    //////////
    $str3 = '<a class="hover_btn"  href="' . U('teacher/sqbroadchange', array(id => $id)) . '">修改录播资料</a>';
    $str3 .= '<a class="hover_btn" href="' . U('teacher/courselist', array(id => $id, type => 2)) . '">查看名单</a>';
    $str3 .= '<a class="hover_btn no_pass gx_vod_del" del_id=' . $id . '>删除</a>';

    //////////
    $str4 = '<a class="hover_btn" href="' . U('teacher/sqbroadchange', array(id => $id)) . '" >修改录播资料</a>';
    $str4 .= '<a class="hover_btn" href="' . U('teacher/courselist', array(id => $id, type => 2)) . '">查看名单</a>';
    $str4 .= '<a class="hover_btn no_pass gx_vod_del" del_id=' . $id . '>删除</a>';

    ////////////

    $str5 = '<a class="hover_btn" href="' . U('teacher/courselist', array(id => $id, type => 2)) . '">查看名单</a>';
    $str5 .= '<a class="hover_btn  finish-class" href="' . U('teacher/myrecorded_complete', array(id => $id)) . '">更新课程</a>';
    $str5 .= '<a class="hover_btn no_pass gx_vod_del" del_id=' . $id . '>删除</a>';

    ////////////
    $str6 = '<a class="hover_btn" href="' . U('teacher/courselist', array(id => $id, type => 2)) . '">查看名单</a>';
    $str6 .= '<a class="hover_btn  finish-class" href="' . U('teacher/myrecorded_complete', array(id => $id)) . '">更新课程</a>';
    $str6 .= '<a class="hover_btn no_pass finish-class gx_del_myrecorded" del_id=' . $id . '>停止更新</a>';


    // 设置数组
    $list = array(
        1 => $str1,
        2 => $str2,
        3 => $str3,
        4 => $str4,
        5 => $str5,
        6 => $str6,
    );

    // 返回处理好的a标签
    return $list[$status];
}


/**
 * +----------------------------------------------------------
 * 功能：课程排名
 * +----------------------------------------------------------
 * @param int $lesson_id 课程id
 * @param int $type 课程类型  1=>直播 2=>录播
 * +----------------------------------------------------------
 * @return int  课程排名  100内
 * +----------------------------------------------------------
 */
function get_ranking($lesson_id, $type = 1)
{
    $table = $type = 1 ? "Live" : "Vod";
    /*    $model = M();

        $result = $model->query("select tt.zn_object_id as zn_object_id,tt.total,count(ft.zn_object_id) ct,(select count(1)+1 from (select zn_object_id,count(zn_object_id) total from
    `lq_member_enroll` group by zn_object_id) a where a.total>tt.total ) as rank from
    (select zn_object_id,count(zn_object_id) total from
    `lq_member_favorite` where zn_type='" . $type . "' group by zn_object_id) tt join `lq_member_favorite` ft on tt.zn_object_id=ft.zn_object_id
    group by tt.zn_object_id
    order by rank asc,ct desc limit 100");*/
    $where = array(
        "zl_status" => array("NOT IN", '2,4'),
    );
    $result = M($table)->where($where)->order("zn_enroll_num DESC,zn_fav_num DESC,zn_cdate ASC")->limit(100)->select();

//echo M($table)->getLastSql();
    foreach ($result as $k => $v) {
        $result[$v['id']] = $k + 1;
    }
    if (array_key_exists($lesson_id, $result)) return $result[$lesson_id];
    return '100+';
}

// 输入分类id 返回分类名称
function return_cat($id)
{
    $cat = F('lesson_cat', '', COMMON_ARRAY);
    foreach ($cat as $key => $value) {// 获取分类相应的数据
        $lq_cat[$value['id']] = $value['zc_caption'];
    }
    return $lq_cat[$id];
}

/////格式化时间显示
function formattime($sec)
{
    $size = sprintf("%u", $sec);
    if ($size == 0) {
        return ("0 sec");
    }

    if ($size < 60) {
        return $size . " sec";
    } else if ($size < 3600) {
        $time = $size / 60;
        $min = floor($time);
        return $min . " min";
    } else {
        $time = $size / 3600;
        $hour = round($time, 1);
        return $hour . " hour";
    }
}

/////格式化时间显示
function formattime_live($sec)
{
    $size = sprintf("%u", $sec);
    if ($size == 0) {
        return ("0 sec");
    }

    $array = array(0, 0, 0);

    if ($size < 60) {
        return $array = array(00, 00, 00);
    } else if ($size < 3600) {
        $time = $size / 60;
        $min = floor($time);
        $s = ($sec - $min * 60);
        if ($min < 10) $min = "0" . $min;
        if ($s < 10) $s = "0" . $s;

        return $array = array(00, 00, $min);
    } else if ($size < 3600 * 24) {
        $time = $size / 3600;
        $hour = round($time);
        $min = ($sec - $hour * 3600) / 60;
        $min = floor($min);

        if ($min < 10) $min = "0" . $min;
        if ($hour < 10) $hour = "0" . $hour;
        return $array = array(00, $hour, $min);
    } else {
        $time = $size / (3600 * 24);
        $day = round($time);

        $hour = ($sec - $day * 3600 * 24) / 3600;
        $hour = floor($hour);

        $min = ($sec - ($day * 3600 * 24 + $hour * 3600)) / 60;
        $min = floor($min);

        if ($day < 10) $day = "0" . $day;
        if ($min < 10) $min = "0" . $min;
        if ($hour < 10) $hour = "0" . $hour;
        return $array = array($day, $hour, $min);
    }
}

////查找类型，返回key
function search_from_array($needle, $haystack)
{
    foreach ($haystack as $key1 => $value1) {
        foreach ($value1 as $key2 => $value2) {
            if ($needle == $value2) return $key1;
        }
    }
    return 'default';
}

/*
 * 验证验证码
 */
function check_code($identify, $check_code, $type)
{
    $list = array(
        1 => 'register',
        2 => 'login'
    );
    if (!(D("SmsLog")->isEffective($identify, $list[$type], $check_code)) || !$check_code) {
        return false;
    } else {
        return true;
    }
}

?>
