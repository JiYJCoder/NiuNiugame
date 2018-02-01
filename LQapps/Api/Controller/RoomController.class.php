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
    protected $room ,$roomJoin ,$gameSchedule, $gameLog ;
    /** 初始化*/
    public function __construct()
    {

        parent::__construct();
        $this->room = D('Room');
        $this->roomJoin = D('RoomJoin');
        $this->gameSchedule = D('GameSchedule');
        $this->gameLog = D('GameLog');

        //免死金牌
        $action_no_login_array = array('get-openid', 'wx-return-openid', 'login', 'wx-login', 'openid-login');
        if (in_array(ACTION_NAME, $action_no_login_array)) {

        } else {
            self::apiCheckToken();//用户认证
        }
    }
    public function setGameStatus(){

    }
    public function createRoom()
    {
        $_POST['zl_visible'] = 1;
        $_POST['zn_member_id'] = $this->login_member_info['id'];
        $flag=  $this->room->createRoom($_POST);
        if(intval($flag)){
            $_POST['id'] = $flag;
            $this->model_member->addMemberLog('create_room', $this->login_member_info);//插入会员日志
            $redata = array('msg'=>'创建成功','status'=>1,'data'=>$_POST);
            $this->ajaxReturn($redata);
        }else{
            $redata = array('msg'=>$flag,'status'=>0);
            $this->ajaxReturn($redata);
        }
    }
    //获取房间号
    public function getRoomNumber(){
        $id = $this->login_member_info['id'];
        $zc_number = create_room_code($id);
        if($zc_number){
            $this->ajaxReturn(array('msg'=>'请求成功','status'=>1,'data'=>$zc_number));
        }else{
            $this->ajaxReturn(array('msg'=>'请求失败','status'=>0));
        }
    }

    //房间列表
    public  function getRoomList(){
        $pageSize = I('post.pagesize') ? I('post.pagesize'):15;
        $type = I('post.type');
        $list=$this->room->getData($pageSize,$type,$this->login_member_info['id']);
        foreach ($list as $key=>$val){
            $list[$key]['pernumber']=$this->roomJoin->getRoomList(15,$val['id'])['count'];
        }
        if(!$list){
            $this->ajaxReturn(array('msg'=>'数据为空','status'=>0));
        }else{
            $this->ajaxReturn(array('msg'=>'请求成功','status'=>1,'data'=>$list));
        }
    }
    //加入过房间
    public function joinRoomList(){
        $userid = I('post.id');
        $list=$this->roomJoin->getRoomArray($userid);
        if(!$list){
            $this->ajaxReturn(array('msg'=>'没有加入过房间','status'=>3));
        }
        $data=$this->room->joinRoomList($list);
        if(!$data){
            $this->ajaxReturn(array('msg'=>'加入的房间已解散','status'=>4));
        }
        foreach ($data as $key=>$val){
            $data[$key]['pernumber']=$this->roomJoin->getRoomList(15,$val['id'])['count'];
        }
        $this->ajaxReturn(array('msg'=>'请求成功','status'=>1,'data'=>$data));

    }

    //加入房间
    public function joinRoom(){
        $roomData=$this->room->getRoom($_POST['zn_room_id']);
        $roomstatus = $this->gameSchedule->getVal($_POST['zn_room_id'],'zn_status');
        $this->roomJoin->setRoomStatus($this->login_member_info['id']);//设置用户其他房间状态为退出
        $isRoom = $this->room->isRoom($_POST['zn_room_id']);
        if($isRoom==$this->login_member_info['id']){
            return $this->ajaxReturn(array('msg'=>'你是房主','status'=>3));
        }
        if($roomData){
            //是否公开
            if($roomData['zn_room_type']==1 ){
                //是否允许进入
                if($roomData['zn_confirm'] ==2){
                    $_POST['zn_member_id'] = $this->login_member_info['id'];
                    $_POST['zn_member_name'] = $this->login_member_info['zc_nickname'];
                    $perData = $this->roomJoin->getRoom($_POST['zn_member_id'],$_POST['zn_room_id']);
                    //是否加入过房间
                    if($perData['zl_visible']==1){
                        $this->ajaxReturn(array('msg'=>'你已在房间里','status'=>3));
                    }
                    if($perData['zl_visible']===0){
                        $flag= $this->roomJoin->addRoom($_POST);
                        if(!$flag){
                            $this->ajaxReturn(array('msg'=>'加入失败,参数错误','status'=>0));
                        }
                        $this->roomJoin->setValN($_POST['zn_member_id'],$_POST['zn_room_id'],'zl_visible',1);
                        $joinPerAll = $this->notift->apiGetNumPer($_POST['zn_room_id']); //获取要通知的人
                        $toArray = array();//通知人数
                        $zn_points = 0;
                        foreach ($joinPerAll as $key=>$val){
                            $toArray[] = $val['zn_member_id'];
                            $zn_points+= $val['zn_points'];
                        }
                        if(!$toArray[0]){
                            return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
                        }
                        $toArray[] = $roomData['zn_member_id'];
                        $notiftData = array('type'=>1);//通知数据
                        $notiftData['total'] = count($joinPerAll);
                        $notiftData['nikename'] = $this->login_member_info['zc_nickname'];
                        $notiftData['totalPoints'] = $zn_points;
                        $notiftData['zn_member_id'] = $this->login_member_info['id'];
                        //socket推送房间内的人
                        $this->socket->setUser($toArray)->setContent($notiftData)->push();
                        $this->model_member->addMemberLog('join_room', $this->login_member_info);//插入会员日志
                        $this->ajaxReturn(array('msg'=>'加入成功','status'=>1,'data'=>$roomData,'perData'=>$perData,'roomstatus'=>$roomstatus));
                    }
                    //创建
                    $flag= $this->roomJoin->addRoom($_POST);
                    if(!$flag){
                        $this->ajaxReturn(array('msg'=>'加入失败,参数错误','status'=>0));
                    }
                    $joinPerAll = $this->notift->apiGetNumPer($_POST['zn_room_id']); //获取要通知的人
                    $toArray = array();//通知人数
                    $zn_points = 0;
                    foreach ($joinPerAll as $key=>$val){
                        $toArray[] = $val['zn_member_id'];
                        $zn_points+= $val['zn_points'];
                    }
                    if(!$toArray[0]){
                        return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
                    }
                    $notiftData = array('type'=>1);//通知数据
                    $notiftData['total'] = count($joinPerAll);
                    $notiftData['nikename'] = $this->login_member_info['zc_nickname'];
                    $notiftData['totalPoints'] = $zn_points;
                    $notiftData['zn_member_id'] = $this->login_member_info['id'];
                    //socket推送房间内的人
                    $this->socket->setUser($toArray)->setContent($notiftData)->push();
                    $this->model_member->addMemberLog('join_room', $this->login_member_info);//插入会员日志
                    $this->ajaxReturn(array('msg'=>'加入成功','status'=>1,'data'=>$roomData,'perData'=>$perData,'roomstatus'=>$roomstatus));
                }else{
                    //通知房主
                    $to =  array($roomData['zn_member_id']);
                    $notiftData =array('type'=>2,'nikename'=>$this->login_member_info['zc_nickname'],'id'=>$this->login_member_info['id'],'roomid'=>$_POST['roomid']);
                    $this->socket->setUser($to)->setContent($notiftData)->push();
                }
            }else{
                $this->ajaxReturn(array('msg'=>'房间不公开','status'=>0));
            }
        }else{
            $this->ajaxReturn(array('msg'=>'找不到房间','status'=>0));
        }
    }

    //是否允许加入房间
    public function isJoin(){
        $type = I('post.type');
        $notiftPerid= I('post.id');
        $nikename= I('post.nikename');
        $roomid = I('post.roomid');
        $data = array();
        $data['zn_member_id'] =$notiftPerid;
        $data['zn_room_id'] = $roomid;
        $roomstatus = $this->gameSchedule->getVal($_POST['zn_room_id'],'zn_status');
        if($type==1){
            //允许加入
            $flag= $this->roomJoin->addRoom($data);
            if(!$flag){
                $this->ajaxReturn(array('msg'=>'加入失败,参数错误','status'=>0));
            }
            $perData = $this->roomJoin->getRoom($_POST['zn_member_id'],$_POST['zn_room_id']);
            if($perData['zl_visible']==1){
                $this->ajaxReturn(array('msg'=>'你已在房间里','status'=>3));
            }

            if($perData['zl_visible']==0){
                $this->roomJoin->setValN($_POST['zn_member_id'],$_POST['zn_room_id'],'zl_visible',1);
                $joinPerAll = $this->notift->apiGetNumPer($_POST['zn_room_id']); //获取要通知的人
                $toArray = array();//通知人数
                foreach ($joinPerAll as $key=>$val){
                    $toArray[] = $val['zn_member_id'];
                }
                if(!$toArray[0]){
                    return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
                }
                $notiftData = array('type'=>1);//通知数据
                $notiftData['total'] = count($joinPerAll);
                $notiftData['nikename'] = $this->login_member_info['zc_nickname'];
                $notiftData['zn_member_id'] = $this->login_member_info['id'];
                //socket推送房间内的人
                $this->socket->setUser($toArray)->setContent($notiftData)->push();
                $this->model_member->addMemberLog('join_room', $this->login_member_info);//插入会员日志
                $this->ajaxReturn(array('msg'=>'加入成功','status'=>1,'perData'=>$perData,'roomstatus'=>$roomstatus));
            }
            $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
            $toArray = array();//通知人数
            foreach ($joinPerAll as $key=>$val){
                $toArray[] = $val['zn_member_id'];
            }
            $notiftData = array();//通知数据
            $notiftData['type'] = 1; //
            $notiftData['total'] = count($joinPerAll);
            $notiftData['nikename'] = $nikename;
            $notiftData['zn_member_id'] = $notiftPerid;
            if(!$toArray[0]){
                return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
            }
            //socket推送房间内的人
            $this->socket->setUser($toArray)->setContent($notiftData)->push();
            $this->model_member->addMemberLog('join_room', $this->login_member_info);//插入会员日志
            $this->ajaxReturn(array('msg'=>'加入成功','status'=>1,'perData'=>$perData,'roomstatus'=>$roomstatus));
        }else{
            //通知他不能加入
            $to = $notiftPerid;
            $notiftData =array('type'=>3,'msg'=>'抱歉!房主拒绝你加入','status'=>0);
            $this->socket->setUser($to)->setContent($notiftData)->push();
        }
    }

    //解散房间
    public function dissolveRoom(){
        $roomid = I('post.roomid');
        $roomdata = $this->room->getRoom($roomid);
        $flag = $this->room->dissolveRoom($roomid);
        if($this->login_member_info['id']==$roomdata['zn_member_id']){
            if(!$flag){
                return $this->ajaxReturn(array('msg'=>'解散失败','status'=>0));
            }
            $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
            $toArray = array();//通知人数
            foreach ($joinPerAll as $key=>$val){
                $toArray[] = $val['zn_member_id'];
                $this->roomJoin->closeRoom($val['zn_member_id'],$roomid);//设置每个人的进入状态
            }
            $notiftData = array();//通知数据
            $notiftData['msg'] = "房间已解散";
            $notiftData['type'] = 4;
            if(!$toArray[0]){
                return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
            }
            //socket推送
            $this->socket->setUser($toArray)->setContent($notiftData)->push();
            $this->model_member->addMemberLog('create_room', $this->login_member_info);//插入会员日志
            return $this->ajaxReturn(array('msg'=>'解散成功','status'=>1));
        }else{
            return $this->ajaxReturn(array('msg'=>'解散失败1','status'=>0));
        }
    }

    //获取房间信息
    public function getRoom(){
        $roomid = $_POST['roomid'];
        $data=$this->room->getRoom($roomid);
        if($data){
            return $this->ajaxReturn(array('msg'=>'获取成功','status'=>1,'data'=>$data));
        }
        return $this->ajaxReturn(array('msg'=>'获取失败','status'=>0));
    }
    //获取房间信息，用房间编号
    public function getRooms(){
        $roomid = $_POST['number'];
        $data=$this->room->getRoomNumber($roomid);
        if($data){
            return $this->ajaxReturn(array('msg'=>'获取成功','status'=>1,'data'=>$data));
        }
        return $this->ajaxReturn(array('msg'=>'获取失败','status'=>0));
    }

    //设置房间状态
    function setRoomStatus(){
        $roomid = I('post.zn_room_id');
        $status = I('post.zn_status');
        $flag = $this->gameSchedule->getVal($roomid,'zn_status');
        if(!$flag){
            $id=$this->gameSchedule->createGameSchedule($_POST);
            if(!$id){
                return $this->ajaxReturn(array('msg'=>'设置失败，参数错误','status'=>0));
            }
//
        }
        $flag1=$this->gameSchedule->setVal($roomid,'zn_status',$status);
        if(!$flag1){
            return $this->ajaxReturn(array('msg'=>'设置失败，状态已设置','status'=>3));
        }
        switch (intval($status)){
            case 1:
                $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
                $toArray = array();//通知人数
                foreach ($joinPerAll as $key=>$val){
                    $toArray[] = $val['zn_member_id'];
                }
                if(!$toArray[0]){
                    return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
                }
                $notiftData = array();//通知数据
                $notiftData['msg'] = "房主暂停游戏";
                $notiftData['type'] = 14;
                //socket推送
                $this->socket->setUser($toArray)->setContent($notiftData)->push();
                break;
            case 2:
                $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
                $toArray = array();//通知人数
                foreach ($joinPerAll as $key=>$val){
                    $toArray[] = $val['zn_member_id'];
                }
                if(!$toArray[0]){
                    return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
                }
                $notiftData = array();//通知数据
                $notiftData['msg'] = "房主开始游戏";
                $notiftData['type'] = 13;
                //socket推送
                $this->socket->setUser($toArray)->setContent($notiftData)->push();
                break;
            case 3:
                break;
            case 4:
                //发牌
                $json = I('post.zn_text');
                $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
                $toArray = array();//通知人数
                foreach ($joinPerAll as $key=>$val){
                    $toArray[] = $val['zn_member_id'];
                }
                if(!$toArray[0]){
                    return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
                }
                $notiftData = array();//通知数据
                $notiftData['msg'] = "发牌中";
                $notiftData['type'] = 12;
                $notiftData['data'] = htmlspecialchars_decode ($json);//发牌数据
                //socket推送
//                lq_test($notiftData);
                $this->socket->setUser($toArray)->setContent($notiftData)->push();

                break;
            case 5:
                break;
        }
        return $this->ajaxReturn(array('msg'=>'设置成功','status'=>1,'id'=>$json));
    }
    //更新房间设置
    public function updatedRoom(){
        unset($_POST['zc_number']);
        unset($_POST['zn_member_id']);
        $flag=$this->room->updatedRoom($_POST);
        if($flag !== false){
            $joinPerAll = $this->notift->apiGetNumPer($_POST['roomid']); //获取要通知的人
            $toArray = array();//通知人数
            foreach ($joinPerAll as $key=>$val){
                $toArray[] = $val['zn_member_id'];
            }
            if(!$toArray[0]){
                return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
            }
            $notiftData = array();//通知数据
            $notiftData['msg'] = "更新房间信息成功";
            $notiftData['type'] = 16;
            //socket推送
            $this->socket->setUser($toArray)->setContent($notiftData)->push();
            return $this->ajaxReturn(array('msg'=>'更新成功','status'=>1));
    }
        return $this->ajaxReturn(array('msg'=>'更新失败','status'=>0));
    }
    public function getRoomStatus(){
        $roomid = I('post.roomid');
        $data = $this->gameSchedule->getlist($roomid);
        if($data){
            return $this->ajaxReturn(array('msg'=>'请求成功','status'=>1,'data'=>$data));
        }
        return $this->ajaxReturn(array('msg'=>'请求失败','status'=>0));
    }
    //重新开局
    public function  reGame(){
        $roomid = I('post.roomid');
        $flg=$this->roomJoin->setAll($roomid,'zn_points',0);
        $flg1= $this->gameLog->setAll($roomid,'zn_visible',0);
        if($flg!==false&&$flg1!==false){
            $joinPerAll = $this->notift->apiGetNumPer($roomid); //获取要通知的人
            $toArray = array();//通知人数
            foreach ($joinPerAll as $key=>$val){
                $toArray[] = $val['zn_member_id'];
            }
            if(!$toArray[0]){
                return $this->ajaxReturn(array('msg'=>"房间没人",'status'=>3));
            }
            $notiftData = array();//通知数据
            $notiftData['msg'] = "房主重新开局";
            $notiftData['type'] = 11;
            //socket推送
            $this->socket->setUser($toArray)->setContent($notiftData)->push();
            return $this->ajaxReturn(array('msg'=>'已重新开局','status'=>1));
        }
        return $this->ajaxReturn(array('msg'=>'重新开局失败，未知原因，请联系史上最强Little哥','status'=>0));
    }
    //庄家设置
    public function setRoomMakers(){
        $roomid = I('post.roomid');
        $maker = I('post.maker');
        $flag=$this->room->setVal($roomid,'zn_maker',$maker);
        if($maker==2){
            $number = I('makernumber');
            if(!$number){
                return $this->ajaxReturn(array('msg'=>'设置失败，参数错误','status'=>0));
            }
            $this->room->setVal($roomid,'zn_maker_number',$number);
        }
        if(intval($flag)===0|| intval($flag)===1){
            return $this->ajaxReturn(array('msg'=>'设置成功','status'=>1));
        }
        if(!$flag){
            return $this->ajaxReturn(array('msg'=>'设置失败，参数错误','status'=>0));
        }
    }
    //废弃接口
    public function getSocket(){
        $id = I('post.id');
        $notiftData = array();//通知数据
        $notiftData['msg'] = "查询socket";
        $notiftData['type'] = 17;
        $this->socket->setUser(array($id))->setContent($notiftData)->push();
        return $this->ajaxReturn(array('msg'=>'废弃接口，请勿请求','status'=>1));
    }
}