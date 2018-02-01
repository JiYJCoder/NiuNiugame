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

class ChatController extends PublicController
{
    protected $chat ,$friend;
    /** 初始化*/
    public function __construct()
    {

        parent::__construct();
        $this->chat = D('Chat');
        $this->friend = D('MemberFriend');

        //免死金牌
        $action_no_login_array = array('get-openid', 'wx-return-openid', 'login', 'wx-login', 'openid-login');
        if (in_array(ACTION_NAME, $action_no_login_array)) {

        } else {
            self::apiCheckToken();//用户认证
        }
    }
//    保存聊天
    public function createChat(){
        $flag=$this->chat->createChat($_POST);
        if($flag){
            return $this->ajaxReturn(array('msg'=>'保存成功','status'=>1));
        }
        return $this->ajaxReturn(array('msg'=>$flag,'status'=>0));
    }
    //设置已读
    public function setRead(){
        $formid = I('post.formid');
        $toid = I('post.toid');
        $flag= $this->chat->setRead($toid,$formid);
        if($flag){
            return $this->ajaxReturn(array('msg'=>'设置成功','status'=>1));
        }
        return $this->ajaxReturn(array('msg'=>'设置失败,没有新消息','status'=>0));
    }
    //获取聊天记录
    public function getChatList(){
        $formid = I('post.formid');
        $toid = I('post.toid');
        $pagesize = I('post.pagesize');
        $list=$this->chat->getChatList($toid,$formid,$pagesize);
        if($list){
            if(!$toid){
                $isfrend=$this->friend->isfrend($list,$formid);
                foreach ($isfrend  as $key=>$val){
                    $list['isfrend']  = $val;
                }
                return $this->ajaxReturn(array('msg'=>'获取成功','status'=>1,'data'=>$list));
            }
            return $this->ajaxReturn(array('msg'=>'获取成功','status'=>1,'data'=>$list));
        }
        return $this->ajaxReturn(array('msg'=>'获取失败','status'=>0));
    }
}