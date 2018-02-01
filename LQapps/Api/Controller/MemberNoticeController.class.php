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

class MemberNoticeController extends PublicController
{
    protected $membernotce;
    /** 初始化*/
    public function __construct()
    {

        parent::__construct();
        $this->membernotce = D('MemberNotice');
        //免死金牌
        $action_no_login_array = array('get-openid', 'wx-return-openid', 'login', 'wx-login', 'openid-login');
        if (in_array(ACTION_NAME, $action_no_login_array)) {

        } else {
            self::apiCheckToken();//用户认证
        }
    }
    //申请好友
    public function applyFriend(){
        $_POST['zn_way']=2;
        $id=$this->membernotce->createNotif($_POST);
        if(intval($id)){
            $this->ajaxReturn(array('msg'=>'申请成功，请等待对方确认','status'=>1));
        }
        $this->ajaxReturn(array('msg'=>'申请失败','status'=>0));
    }
    //获取系统消息
    public function getNotify(){
        $id = I('post.id');
        $data=$this->membernotce->getList($id);
        $this->ajaxReturn(array('msg'=>'请求成功','status'=>1,'data'=>$data));
    }
    public function getAnnouncement(){
        $id = I('post.id');
        $data=$this->membernotce->getAnnouncement($id);
        $this->ajaxReturn(array('msg'=>'请求成功','status'=>1,'data'=>$data));
    }
    //删除消息
    public function delNotify(){
        $id = $_POST['id'];
        $flag=$this->membernotce->delNotif($id,'zl_visible',0);
        if($flag !==false){
            $this->ajaxReturn(array('msg'=>'删除成功','status'=>1));
        }
        $this->ajaxReturn(array('msg'=>'删除失败','status'=>0));
    }
    //设置已读
    public function setRead(){
        $id = $_POST['id'];
        $flag=$this->membernotce->delNotif($id,'zn_read',2);
        if($flag !==false){
            $this->ajaxReturn(array('msg'=>'设置成功','status'=>1));
        }
        $this->ajaxReturn(array('msg'=>'删除失败','status'=>0));
    }

}