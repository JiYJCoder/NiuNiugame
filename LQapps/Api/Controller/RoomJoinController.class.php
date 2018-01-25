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
    protected $room ,$roomJoin ,$gameSchedule;
    /** 初始化*/
    public function __construct()
    {

        parent::__construct();
        $this->roomJoin = D('RoomJoin');
        $this->room = D('Room');
        $this->gameSchedule = D('GameSchedule');
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
        $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
        $toArray = array();//通知人数
        $total = 0;
        foreach ($joinPerAll as $key=>$val){
            $toArray[] = $val['zn_member_id'];
            $total +=$val['zn_points'];
        }
        if($type==1){
            $this->model_member->addMemberLog('points_add', $this->login_member_info);//插入会员日志
        }else{
            $this->model_member->addMemberLog('points_dec', $this->login_member_info);//插入会员日志
        }
        $notiftData = array('type'=>5,'msg'=>'房主修改分数',"total"=>$total);
        $this->socket->setUser($toArray)->setContent($notiftData)->push();
        $this->ajaxReturn($redata);
    }
    //退出房间
    public function closeRoom(){
        $id = I('post.id');
        $roomid = I('post.roomid');
        $flag= $this->roomJoin->closeRoom($id,$roomid);
        if(!$flag){
            return $this->ajaxReturn(array('msg'=>'退出失败','status'=>0));
        }
        $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
        $toArray = array();//通知人数
        $total= 0;
        foreach ($joinPerAll as $key=>$val){
            $toArray[] = $val['zn_member_id'];
            $total +=$val['zn_points'];
        }
        $notiftData = array('msg'=>'退出房间','total'=>$total,'type'=>6);
        $this->socket->setUser($toArray)->setContent($notiftData)->push();
        $this->model_member->addMemberLog('kick_member', $this->login_member_info);//插入会员日志
        return $this->ajaxReturn(array('msg'=>'退出成功','status'=>1));

    }

    //得到房间人列表
    public function  getJoinRoomList(){
        $roomid = I('post.roomid');
        $pageSize = I('post.pagesize');
        $list= $this->roomJoin->getRoomList($pageSize,$roomid);
        if(!$list){
            return $this->ajaxReturn(array('msg'=>'请求失败','status'=>0));
        }
        return $this->ajaxReturn(array('msg'=>'success','status'=>1,'data'=>$list));
    }

    //设置庄家
    public function setMakers(){
        $roomid = I('post.roomid');
        $id = I('post.id');
        $flag= $this->roomJoin->setMakers($id,$roomid);
        $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
        $toArray = array();//通知人数
        foreach ($joinPerAll as $key=>$val){
            $toArray[] = $val['zn_member_id'];
        }
        $notiftData = array('msg'=>'成为庄家','type'=>7,'id'=>$id);
        $this->socket->setUser($toArray)->setContent($notiftData)->push();
        if(!$flag){
            return $this->ajaxReturn(array('msg'=>'设置失败','status'=>0));
        }
        return $this->ajaxReturn(array('msg'=>'设置成功','status'=>1));
    }

    //申请上庄
    public  function  applyfor(){
        $roomid = I('post.roomid');
        $id = I('post.id');
        $score = $this->roomJoin->getRoom($id,$roomid,'zn_points');
        $roomData = $this->room->getRoom($roomid);
        if($score<$roomData['zn_min_score']){
            return $this->ajaxReturn(array('msg'=>'申请失败，玩家分数小于最低上庄分数','status'=>0));
        }
        if($id == $this->login_member_info['id']){

            //通知房主
            $to =  array($roomData['zn_member_id']);
            $notiftData =array('type'=>8,'nikename'=>$this->login_member_info['zc_nickname'],'id'=>$this->login_member_info['id'],'roomid'=>$roomid);
            $this->socket->setUser($to)->setContent($notiftData)->push();
            return $this->ajaxReturn(array('msg'=>'申请成功，请等待房主确认','status'=>1));
        }
        return $this->ajaxReturn(array('msg'=>'申请失败','status'=>0));
    }

    //压分
    public function chargePoints(){
        $score = I('post.score');
        $id = I('post.id');
        $roomid = I('post.roomid');
        $flag = $this->roomJoin->chargePoints($id,$roomid,$score);
        $status = $this->gameSchedule->getVal($roomid,'zn_status');
        if($status ==3){
            return $this->ajaxReturn(array('msg'=>"压分失败，系统已停止下注",'status'=>0));
        }
        if(intval($flag)){
            $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
            $toArray = array();//通知人数
            foreach ($joinPerAll as $key=>$val){
                $toArray[] = $val['zn_member_id'];
            }
            $notiftData = array('msg'=>'','type'=>9,'id'=>$id,'nickname'=>$this->login_member_info,'zn_maker_points'=>$flag);
            $this->socket->setUser($toArray)->setContent($notiftData)->push();
            return $this->ajaxReturn(array('msg'=>"上分成功",'status'=>1,'data'=>array('score'=>$score)));
        }
        return $this->ajaxReturn(array('msg'=>$flag,'status'=>0));
    }

    //公告
    public function placard(){
        $content = I('post.content');
        $roomid = I('post.roomid');
        $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
        $toArray = array();//通知人数
        foreach ($joinPerAll as $key=>$val){
            $toArray[] = $val['zn_member_id'];
        }
        $notiftData = array('msg'=>'房主发布了一条公告','type'=>10,'nickname'=>$this->login_member_info,'content'=>$content);
        $this->socket->setUser($toArray)->setContent($notiftData)->push();
        $this->model_member->addMemberLog('send_notice', $this->login_member_info);//插入会员日志
        return $this->ajaxReturn(array('msg'=>"发布成功",'status'=>1));
    }

}