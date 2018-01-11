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

class RoomController extends PublicController
{
    protected $room ,$roomJoin;
    /** 初始化*/
    public function __construct()
    {

        parent::__construct();
        $this->room = D('Room');
        $this->roomJoin = D('RoomJoin');

        //免死金牌
        $action_no_login_array = array('get-openid', 'wx-return-openid', 'login', 'wx-login', 'openid-login');
        if (in_array(ACTION_NAME, $action_no_login_array)) {

        } else {
            self::apiCheckToken();//用户认证
        }
    }
    public function createRoom()
    {
        //nikename作为房间名
        $_POST['zc_title'] = $this->login_member_info['zc_nickname'];
        //生成房间编号
        $_POST['zc_number'] = $_SESSION['zc_number'];
        $flag=  $this->room->createRoom($_POST);
        if($flag){
            unset($_SESSION['zc_number']);
            $redata = array('msg'=>'创建成功','status'=>1,'data'=>$_POST);
            $this->ajaxReturn($redata);
        }else{
            $redata = array('msg'=>'创建失败,缺少参数','status'=>0);
            $this->ajaxReturn($redata);
        }
    }
    //获取房间号
    public function getRoorNumber(){
        $id = $this->login_member_info['id'];
        $zc_number = create_room_code($id);
        $_SESSION['zc_number'] = $zc_number;
        if($zc_number){
            $this->ajaxReturn(array('msg'=>'请求成功','status'=>1,'data'=>$zc_number));
        }else{
            $this->ajaxReturn(array('msg'=>'请求失败','status'=>0));
        }
    }

    //房间列表
    public  function getRoomList(){
        $list=$this->room->getData($_POST);
        if(!$list){
            $this->ajaxReturn(array('msg'=>'数据为空','status'=>0));
        }else{
            $this->ajaxReturn(array('msg'=>'请求成功','status'=>1,'data'=>$list));
        }
    }

    //加入房间
    public function joinroom(){
        $roomData=$this->room->getData($_POST['roomid']);
        if($roomData){
            if($roomData['zn_room_type']==1 ){
                if($roomData['zn_confirm'] ==2){
                    $_POST['zn_member_id'] = $this->login_member_info['id'];
                    $_POST['zc_nickname'] = $this->login_member_info['zc_nickname'];
                    $flag= $this->roomJoin->addRoom($_POST);
                    if(!$flag){
                        $this->ajaxReturn(array('msg'=>'加入失败,参数错误','status'=>0));
                    }
                    $this->ajaxReturn(array('msg'=>'加入成功','status'=>1,'data'=>$roomData));
                    $joinPerAll = $this->notift->apiGetNumPer($_POST['roomid']); //获取要通知的人
                    $toArray = array();//通知人数
                    foreach ($joinPerAll as $key=>$val){
                        $toArray = $val['zn_member_id'];
                    }
                    $notiftData = array();//通知数据
                    $notiftData['total'] = count($joinPerAll);
                    $notiftData['nikename'] = $this->login_member_info['zc_nickname'];
                    $notiftData['zn_member_id'] = $this->login_member_info['id'];
                    //socket推送
                    $this->socket->setUser($toArray)->setContent($notiftData)->push();
                }else{
                    //通知房主
                    $to =  $roomData['zn_member_id'];
                    $notiftData =array('nikename'=>$this->login_member_info['zc_nickname'],'id'=>$this->login_member_info['id'],'roomid'=>$_POST['roomid']);
                    $this->socket->setUser($to)->setContent($notiftData)->push();
                }
            }else{
                $this->ajaxReturn(array('msg'=>'房间不公开','status'=>0));
            }
        }else{
            $this->ajaxReturn(array('msg'=>'找不到房间','status'=>0));
        }
    }
    public function getFlag(){
        $type = I('post.type');
        $notiftPerid= I('post.id');
        $nikename= I('post.nikename');
        $roomid = I('post.roomid');
        $data = array();
        $data['zn_member_id'] =$notiftPerid;
        $data['zn_room_id'] = $roomid;
        if($type==1){
            //允许加入
            $flag= $this->roomJoin->addRoom($data);
            if(!$flag){
                $this->ajaxReturn(array('msg'=>'加入失败,参数错误','status'=>0));
            }
            $this->ajaxReturn(array('msg'=>'加入成功','status'=>1));
            $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
            $toArray = array();//通知人数
            foreach ($joinPerAll as $key=>$val){
                $toArray = $val['zn_member_id'];
            }
            $notiftData = array();//通知数据
            $notiftData['total'] = count($joinPerAll);
            $notiftData['nikename'] = $nikename;
            $notiftData['zn_member_id'] = $notiftPerid;
            //socket推送
            $this->socket->setUser($toArray)->setContent($notiftData)->push();
        }else{
            //通知他不能加入
//            TODO
            $to = $notiftPerid;
            $notiftData =array('msg'=>'抱歉!房主拒绝你加入','status'=>0);
            $this->socket->setUser($to)->setContent($notiftData)->push();
        }
    }
}