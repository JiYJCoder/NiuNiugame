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
        if(!$toArray[0]){
            return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
        }
//        $toArray[] = $this->login_member_info['id'];//房主
        if($type==1){
            $this->model_member->addMemberLog('points_add', $this->login_member_info);//插入会员日志
        }else{
            $this->model_member->addMemberLog('points_dec', $this->login_member_info);//插入会员日志
        }
        $notiftData = array('type'=>5,'msg'=>'房主修改分数',"totalPoints"=>$total);
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
        if(!$toArray[0]){
            return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
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
        $type = I('post.type');
        $flag= $this->roomJoin->setMakers($id,$roomid,$type);
        $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
        $toArray = array();//通知人数
        foreach ($joinPerAll as $key=>$val){
            $toArray[] = $val['zn_member_id'];
        }
        if(!$toArray[0]){
            return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
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
        $flag= $this->roomJoin->setVal($id,$roomid,'zn_maker_status',1);
        $time = time();
        $flag1= $this->roomJoin->setVal($id,$roomid,'zn_mdate',$time);
        if(!$flag){
            return $this->ajaxReturn(array('msg'=>'申请失败','status'=>0));
        }
        //通知房主
        $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
        $toArray = array();//通知人数
        foreach ($joinPerAll as $key=>$val){
            $toArray[] = $val['zn_member_id'];
        }
        if(!$toArray[0]){
            return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
        }
        $notiftData =array('type'=>8,'nikename'=>$this->login_member_info['zc_nickname'],'id'=>$this->login_member_info['id'],'roomid'=>$roomid);
        $this->socket->setUser($toArray)->setContent($notiftData)->push();
        return $this->ajaxReturn(array('msg'=>'申请成功','status'=>1));
    }

    //压分
    public function chargePoints(){
        $id = I('post.id');
        $roomid = I('post.roomid');
        $maxmag = I('post.maxmag');//倍数
        $score = I('post.score');
        if($maxmag){
            $maxscore=intval($score) * intval($maxmag);//玩家最大分值
        }
        $maxscoreAll = 0;//下注总分
        $minscore  = $this->room->getRoom($roomid)['zn_min_score'];//最低上庄分数
        $joinper = $this->roomJoin->getJoinPer($roomid,'zn_betting');
        foreach ($joinper as $key =>$val){
            $maxscoreAll += $val['zn_betting'];
        }
        $maxscoreAll += $maxscore;
        if($maxscoreAll>$minscore){
            return $this->ajaxReturn(array('msg'=>"压分失败，庄家分数不够",'status'=>0));
        }
        $few  = I('post.few');
        $flag = $this->roomJoin->chargePoints($id,$roomid,$score,$maxmag);
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
            if(!$toArray[0]){
                return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
            }
            $notiftData = array('msg'=>'','type'=>9,'id'=>$id,'nickname'=>$this->login_member_info,'zn_maker_points'=>$flag,'few'=>$few,'score'=>$score,'maxmag'=>$maxmag);
            $this->socket->setUser($toArray)->setContent($notiftData)->push();
            return $this->ajaxReturn(array('msg'=>"上分成功",'status'=>1,'data'=>array('score'=>$score,'maxmag'=>$maxmag)));
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
        if(!$toArray[0]){
            return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
        }
        $notiftData = array('msg'=>'房主发布了一条公告','type'=>10,'nickname'=>$this->login_member_info['zc_nickname'],'content'=>$content);
        $this->socket->setUser($toArray)->setContent($notiftData)->push();
        $this->model_member->addMemberLog('send_notice', $this->login_member_info);//插入会员日志
        return $this->ajaxReturn(array('msg'=>"发布成功",'status'=>1));
    }

    //申请上庄列表

    public function getMakerList(){
        $roomid = I('post.roomid');
        $list=$this->roomJoin->getMakerList($roomid);
        $this->ajaxReturn(array('msg'=>"申请成功",'status'=>1,'data'=>$list));
    }
}