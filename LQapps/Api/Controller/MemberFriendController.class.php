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
        $_POST['username'] = $this->login_member_info['zc_nickname'];
        $friendid['zc_to'] = $_POST['zn_friend_id'];
        $isFriend=$this->friend->isfrend($friendid,$_POST['zn_mid']);
        if($isFriend[0]==false){
            return $this->ajaxReturn(array('msg'=>"添加失败，你们已经是好友了",'status'=>2));
        }
        $flag= $this->friend->createFrient($_POST);
        if(intval($flag)){
            $this->model_member->addMemberLog('make_friends', $this->login_member_info);//插入会员日志
            return $this->ajaxReturn(array('msg'=>"添加成功",'status'=>1));
        }
        return $this->ajaxReturn(array('msg'=>"添加失败",'status'=>0));
    }
    //删除好友
    function delFriend(){
        $id = I('post.id');
        $friend = I('post.friend');
        $flag=$this->friend->delFrient($id,$friend);
        if($flag !==false){
            $this->model_member->addMemberLog('delete_friends', $this->login_member_info);
            return $this->ajaxReturn(array('msg'=>"删除成功",'status'=>1));
        }
        return $this->ajaxReturn(array('msg'=>"删除失败",'status'=>0));
    }

    //修改备注
    function modifyMark(){
        $id = I('post.id');
        $friendid = I('post.friendid');
        $name = I('post.name');
        $flag=$this->friend->modifyMark($id,$friendid,$name);
        if($flag!==false){
            $this->model_member->addMemberLog('modify_friend_name', $this->login_member_info);//插入会员日志
            return $this->ajaxReturn(array('msg'=>"修改成功",'status'=>1));
        }
        return $this->ajaxReturn(array('msg'=>"修改失败",'status'=>0));
    }

    //获取好友列表
    function getFrientList(){
        $id = I('post.id');
        $pagesize = I('post.pagesize');
        $list=$this->friend->getFrientList($id,$pagesize);
        return $this->ajaxReturn(array('msg'=>"请求成功",'status'=>1,'data'=>$list));
    }
}