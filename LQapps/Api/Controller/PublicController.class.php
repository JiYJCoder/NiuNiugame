<?php
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
家装:hd(home-decoration)
家居:ha(home-appliance)
HTTP请求例子：http://www.jianyu020.net/do?g=api&m=hd&a=index&lqtest=1(为跨域访问测试)&uid=1&token=xxxx(30位)
*/
namespace Api\Controller;

use Think\Controller;
use Member\Api\MemberApi as MemberApi;
use Attachment\Api\AttachmentApi as AttachmentApi;

defined('in_lqweb') or exit('Access Invalid!');

class PublicController extends Controller
{
    public $lqgetid, $lqpostid, $set_config, $returnData, $model_member, $login_member_info, $JSONP;

    public function __construct()
    {

        parent::__construct();
        //AJAX返回数据格式
        header("Access-Control-Allow-Origin:*");
        $this->JSON = 'json';
        $this->model_member = new MemberApi;//实例化会员
        $this->lqgetid = isset($_GET["tnid"]) ? intval($_GET["tnid"]) : 0;
        $this->lqpostid = isset($_POST["fromid"]) ? intval($_POST["fromid"]) : 0;
        $this->set_config = F('set_config', '', COMMON_ARRAY);
        $this->returnData = array('status' => 0, 'msg' => '当前系统繁忙，请稍后重试！', 'data' => array(), "url" => "", "note" => "");//初始回调数据
    }

    //api用户认证**************************************************
    protected function apiCheckToken($mustReturn = 1)
    {
        $uid = I("get.uid", '');//用户ID
        $token = I("get.token", '');//授权加密码
        $token_data = $this->model_member->apiGetToken($uid);
        if (!$token_data) {
            if ($mustReturn == 1) $this->ajaxReturn(array('status' => 2, 'msg' => '用户认证失败,请重新登录', 'data' => '', "url" => "", "note" => "请离开"), $this->JSONP);
        } else {
            if ($token === $token_data["zc_token"]) {
                //会员信息
                $this->login_member_info = $this->model_member->apiGetInfo($token_data["zn_member_id"]);

                if ($this->login_member_info == -1) $this->ajaxReturn(array('status' => 2, 'msg' => '用户认证失败,请重新登录', 'data' => '', "url" => "", "note" => "请离开"), $this->JSONP);
                if ($this->login_member_info["zc_headimg"]) {
                    if (substr($this->login_member_info["zc_headimg"], 0, 4) == 'http') {
                        $this->login_member_info["zc_headimg"] = $this->login_member_info["zc_headimg"];
                    } else {
                        $this->login_member_info["zc_headimg"] = API_DOMAIN . $this->login_member_info["zc_headimg"];
                    }
                } else {
                    $this->login_member_info["zc_headimg"] = NO_HEADIMG;
                }
            } else {
                if ($mustReturn == 1) $this->ajaxReturn(array('status' => 2, 'msg' => '用户认证失败,请重新登录', 'data' => '', "url" => "", "note" => "请离开"), $this->JSONP);
            }
        }

    }

    //web用户认证**************************************************
    protected function webCheckLogin()
    {
        if (!lq_is_login('member')) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '用户认证失败,请重新登录', 'data' => array(), "url" => "", "note" => ""), $this->JSONP);
        } else {
            //会员信息
            $this->login_member_info = $this->model_member->apiGetInfo(session('member_auth')["id"]);
            if ($this->login_member_info["zc_headimg"]) {
                $this->login_member_info["m_headimg"] = $this->login_member_info["zc_headimg"];
            } else {
                $this->login_member_info["m_headimg"] = NO_HEADIMG;
            }
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

    // 停止执行脚本，并打印字符串
    public function dieMsg($str = '程序中止执行...')
    {
        header('Content-Type:text/html; charset=utf-8');
        die($str);
    }

    //获得seo数据
    protected function getSeoData($data = array())
    {
        $seo_data = array();
        $seo_data["title"] = $data["seo_title"] == '' ? $this->set_config["SEO_TITLE"] : $data["seo_title"];
        $seo_data["keywords"] = $data["seo_keywords"] == '' ? $this->set_config["SEO_KEYWORDS"] : $data["seo_keywords"];
        $seo_data["description"] = $data["seo_description"] == '' ? $this->set_config["SEO_DESCRIPTION"] : $data["seo_description"];
        return $seo_data;
    }

    //上传操作 - 单文件
    protected function opUpload($key = 'image', $type = 'images')
    {
        ob_end_clean();
        //file表单控件名
        $file_widget = $_FILES[$key];
        if ($file_widget['size'] == 0) {
            return array('status' => 0, 'msg' => '提交失败:上传的文件不存在或为空', "url" => "");
        }
        $upload = new \Think\Upload();// 实例化上传类
        $upload->rootPath = './' . C("UPLOAD_PATH")["folder"];//文件上传保存的根路径
        $upload->maxSize = C("UPLOAD_MAX_SIZE")[$type];// 设置附件上传大小
        $upload->exts = C("UPLOAD_EXT")[$type];// 设置附件上传类型
        $upload->savePath = C("UPLOAD_PATH")["list"][$type];//上传目录
        $upload->subName = array('date', 'Ymd');//上传目录
        if ($upfile_info = $upload->uploadOne($file_widget)) {// 上传错误提示错误信息
            $Attachment = new AttachmentApi;
            $lc_table = "attachment";
            $lc_folder_path = $upload->rootPath . $upfile_info["savepath"];
            $lc_folder_path = substr($lc_folder_path, 1);
            $upfile_data = array(
                'zn_uid' => intval($this->login_member_info["id"]),
                'zc_account' => $this->login_member_info["zc_account"],
                'zc_table' => $lc_table,
                'zc_controller' => CONTROLLER_NAME,
                'zn_type' => in_array($upfile_info["ext"], array('jpg', 'gif', 'png', 'jpeg')) ? 0 : 1,
                'zn_user_type' => 2,
                'zc_original_name' => str_replace("." . $upfile_info["ext"], "", $upfile_info["name"]),
                'zc_sys_name' => $upfile_info["savename"],
                'zc_folder_path' => $lc_folder_path,
                'zc_file_path' => $lc_folder_path . $upfile_info["savename"],
                'zc_suffix' => strtolower($upfile_info['ext']),
                'zn_size' => $upfile_info["size"],
                'zc_folder' => $type,
                'zn_day' => strtotime(C("LQ_TIME_DAY")),
                'zn_cdate' => NOW_TIME
            );
            $lnLastInsID = $Attachment->insertAttachment($lc_table, $upfile_data);
            return array('status' => 1, 'msg' => '上传成功', "url" => $upfile_data["zc_file_path"]);

        } else {
            return array('status' => 0, 'msg' => '上传失败:' . $upload->getError(), "url" => "");
        }
    }

    //上传操作 - 多文件
    protected function opUploadMulti($type = 'images')
    {
        ob_end_clean();
        $upload = new \Think\Upload();// 实例化上传类
        $upload->rootPath = './' . C("UPLOAD_PATH")["folder"];//文件上传保存的根路径
        $upload->maxSize = C("UPLOAD_MAX_SIZE")[$type];// 设置附件上传大小
        $upload->exts = C("UPLOAD_EXT")[$type];// 设置附件上传类型
        $upload->savePath = C("UPLOAD_PATH")["list"][$type];//上传目录
        $upload->subName = array('date', 'Ymd');//上传目录
        if ($upfile_list = $upload->upload($_FILES)) {// 上传错误提示错误信息
            $file_list = array();
            $Attachment = new AttachmentApi;
            $lc_table = "attachment";
            foreach ($upfile_list as $lcKey => $lcValue) {
                $lc_folder_path = $upload->rootPath . $lcValue["savepath"];
                $lc_folder_path = substr($lc_folder_path, 1);
                $upfile_data = array(
                    'zn_uid' => intval($this->login_member_info["id"]),
                    'zc_account' => $this->login_member_info["zc_account"],
                    'zc_table' => $lc_table,
                    'zc_controller' => CONTROLLER_NAME,
                    'zn_type' => in_array($lcValue["ext"], array('jpg', 'gif', 'png', 'jpeg')) ? 0 : 1,
                    'zn_user_type' => 2,
                    'zc_original_name' => str_replace("." . $lcValue["ext"], "", $lcValue["name"]),
                    'zc_sys_name' => $lcValue["savename"],
                    'zc_folder_path' => $lc_folder_path,
                    'zc_file_path' => $lc_folder_path . $lcValue["savename"],
                    'zc_suffix' => strtolower($lcValue['ext']),
                    'zn_size' => $lcValue["size"],
                    'zc_folder' => $type,
                    'zn_day' => strtotime(C("LQ_TIME_DAY")),
                    'zn_cdate' => NOW_TIME
                );
                $Attachment->insertAttachment($lc_table, $upfile_data);
                $file_list[] = $upfile_data["zc_file_path"];
            }
            return array('status' => 1, 'msg' => '上传成功', "url" => $file_list);
        } else {
            return array('status' => 0, 'msg' => '上传失败:' . $upload->getError(), "url" => "");
        }
    }


}

?>