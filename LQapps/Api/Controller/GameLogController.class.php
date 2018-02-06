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

class GameLogController extends PublicController
{
    protected $room ,$roomJoin ,$game;
    /** 初始化*/
    public function __construct()
    {

        parent::__construct();
        $this->game = D('GameLog');
        $this->roomJoin = D('RoomJoin');
        //免死金牌
        $action_no_login_array = array('get-openid', 'wx-return-openid', 'login', 'wx-login', 'openid-login','test');
        if (in_array(ACTION_NAME, $action_no_login_array)) {

        } else {
            self::apiCheckToken();//用户认证
        }
    }
    //获取游戏结果
   public function getData(){
        $roomid = I("post.roomid");
        $id = I('post.id');
        $number = I('post.number');//局数
        $data=$this->game->getData($roomid,$id,$number);
        $redata = array('msg'=>'查询成功','status'=>1,'data'=>$data);
        $this->ajaxReturn($redata);
   }

   //存储游戏结果
    public function createGameLog()
    {
        $flag=$this->game->createGameLog($_POST);
        if(intval($flag)){
            $poins=$_POST['zn_points_left'];
            $this->roomJoin->setVal($_POST['zn_member_id'],$_POST['zn_room_id'],'zn_points',$poins);//设置分数
            if($_POST['zc_is_boss'] == 1){
                $poins = $_POST['zn_points_left']- $_POST['zn_points_give'];
            }
            $this->roomJoin->setVal($_POST['zn_member_id'],$_POST['zn_room_id'],'zn_maker_points',$poins);//设置庄家分数
            $redata = array('msg'=>'创建成功','status'=>1);
            $this->ajaxReturn($redata);
        }
        $redata = array('msg'=>$flag,'status'=>0);
        $this->ajaxReturn($redata);
    }
}