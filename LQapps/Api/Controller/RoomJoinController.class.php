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

class RoomJoinController extends PublicController
{
    protected $room;
    /** 初始化*/
    public function __construct()
    {

        parent::__construct();
        $this->roomJoin = D('RoomJoin');
        //免死金牌
        $action_no_login_array = array('get-openid', 'wx-return-openid', 'login', 'wx-login', 'openid-login');
        if (in_array(ACTION_NAME, $action_no_login_array)) {

        } else {
            self::apiCheckToken();//用户认证
        }
    }
    //设置分数
    public function chagePoint(){
        $id = I('post.id');
        $roomid = I('post.roomid');
        $points = I('post.points');
        $type = I('post.type');
        $flag=$this->roomJoin->chagePoint($id,$roomid,$points,$type);
        if(!$flag){
            $redata = array('msg'=>'设置失败','status'=>0);
            $this->ajaxReturn($redata);
        }
        $redata = array('msg'=>'设置成功','status'=>1);
        $this->ajaxReturn($redata);
        $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
        $toArray = array();//通知人数
        $total = 0;
        foreach ($joinPerAll as $key=>$val){
            $toArray[] = $val['zn_member_id'];
            $total +=$val['zn_points'];
        }
        $notiftData = array('msg'=>'房主修改分数',"total"=>$total);
        $this->socket->setUser($toArray)->setContent($notiftData)->push();
    }
    //退出房间
    public function closeRoom(){
        $id = I('post.id');
        $roomid = I('post.roomid');
        $flag= $this->room->closeRoom($id,$roomid);
        if(!$flag){
            return $this->ajaxReturn(array('msg'=>'退出失败','status'=>0));
        }
        return $this->ajaxReturn(array('msg'=>'退出成功','status'=>1));
        $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
        $toArray = array();//通知人数
        foreach ($joinPerAll as $key=>$val){
            $toArray[] = $val['zn_member_id'];
            $total +=$val['zn_points'];
        }
        $notiftData = array('msg'=>'退出房间',"nikename"=>$this->login_member_info);
        $this->socket->setUser($toArray)->setContent($notiftData)->push();
    }

    //得到房间人列表
    public function  getJoinRoomList(){
        $roomid = I('post.roomid');
        $pageSize = I('post.pageSize') || 15;
        $list= $this->roomJoin->getRoomList($pageSize,$roomid);
        if($list){
            return $this->ajaxReturn(array('msg'=>'请求失败','status'=>0));
        }
        return $this->ajaxReturn(array('msg'=>'success','status'=>1,'data'=>$list));
    }


}