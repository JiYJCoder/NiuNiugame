<?php
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:会员中心
家装:hd(home-decoration)
*****************************************************************************
(1) 返回内容为json格式，采用UTF-8编码。
(2) 信息内容中含有时间字段的，字段值为urlEncode格式。
(3) 返回内容{"status":0,"msg":"操作提示","data":"回调数据","url":"回调响应地址","note":"备注"};
(4) 用户请求加权文档，统一传入uid,token两值。
*****************************************************************************
*/

namespace Api\Controller;

use Think\Controller;

defined('in_lqweb') or exit('Access Invalid!');

class MemberFriendController extends PublicController
{
    protected $friend ;
    /** 初始化*/
    public function __construct()
    {

        parent::__construct();
        $this->friend = D('MemberFriend');

        //免死金牌
        $action_no_login_array = array('get-openid', 'wx-return-openid', 'login', 'wx-login', 'openid-login');
        if (in_array(ACTION_NAME, $action_no_login_array)) {

        } else {
            self::apiCheckToken();//用户认证
        }
    }
   //添加好友
    function addFriend(){
        $flag= $this->friend->createFrient($_POST);
        if(intval($flag)){
            return $this->ajaxReturn(array('msg'=>"添加成功",'status'=>1));
        }
        return $this->ajaxReturn(array('msg'=>"添加失败",'status'=>0));
    }
}