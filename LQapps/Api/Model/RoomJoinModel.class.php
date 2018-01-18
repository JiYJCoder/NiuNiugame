<?php //设计师说 数据处理，数据回调
namespace Api\Model;
use Member\Api\MemberApi as MemberApi;
use LQLibs\Util\Page;
defined('in_lqweb') or exit('Access Invalid!');

class RoomJoinModel extends PublicModel {
    protected $_validate = array(
        array('zn_member_id','require','缺少必要参数!'), //用户id
        array('zn_room_id','require','缺少必要参数！'), //房间id
        array('zn_member_name','require','缺少必要参数！'), //用户nikenamne
        array('zn_points','require','缺少必要参数！'), //玩家分数
        array('zl_visible','require','缺少必要参数！'), //加入状态
    );
    protected $_auto = array (
        array('zn_cdate','time',1,'function'), // 对create_time字段在更新的时候写入当前时间戳
        array('zn_mdate','time',2,'function'), // 对updatetime字段在更新的时候写入当前时间戳
    );
    public function __construct() {
        parent::__construct();
    }

    //房间人
    public function getRoomList($pagesize=15,$roomid){
        $where = array();
        $where['zl_visible'] = 1;
        $where['zn_room_id'] = $roomid;
        $count= $this->where($where)->count();
        $page=new Page($count,$pagesize);
        $firstRow = $page->firstRow;
        $listRows = $page->listRows;
        $list = $this->where($where)->limit("$firstRow , $listRows")->order('zn_cdate desc')->select();
        return $list;
    }

    public function getRoom($id,$roomid,$sql=''){
        $where = array();
        $where['zn_member_id']= $id;
        $where['zn_room_id'] = $roomid;
        if($sql){
            return $this->where($where)->getField($sql);
        }
        return $this->where($where)->find();
    }
    //设置值
    public function setVal($id,$roomid,$sql,$val){
        $where = array();
        $where['zn_member_id'] = $id;
        $where['zn_room_id'] = $roomid;
        return $this->where($where)->setField($sql,$val);
    }

    //加入房间
    public function addRoom($data){
        $memberid = $data['zn_member_id'];
        $roomid = $data['zn_room_id'];
        $v = $this->getRoom($memberid,$roomid);
        //查询是否已加入过
        if(!$v){
            $flag=$this->create($data);
            if(!$flag){
                return $this->getError();
            }
            return $this->add($data);
        }else{
            $where = array();
            $where['zn_member_id'] = $memberid;
            $where['zn_room_id'] = $roomid;
            return $this->where($where)->setField('zl_visible',1);
        }

    }

    //改变分数
    public function chagePoint($id,$roomid,$points,$type){
        $v = $this->getRoom($id,$roomid);
        if(!$v){
            $this->getError();
        }
        $dpoints = $this->getRoom($id,$roomid);
        $dpoints = $dpoints['zn_points'];
        if($type ==1){
            $dpoints += $points;
        }else{
            $dpoints-= $points;
        }
        $where = array();
        $where['zn_member_id']= $id;
        $where['zn_room_id'] = $roomid;
        $this->where($where)>setField('zn_points',$dpoints);
    }

    //退出房间
    public function closeRoom($id,$roomid){
        $point = $this->getRoom($id,$roomid,"zn_points");
        $this->setVal($id,$roomid,'zn_npoints',$point);//记录分数
        $this->setVal($id,$roomid,'zn_points',0);//设置分数
        return $this->setVal($id,$roomid,'zl_visible',0); //设置已推出
    }

    //查询庄家
    public function getMakers($roomid){
        $list =$this->where('roomid='.$roomid)->select();
        foreach ($list as $key => $val){
            if($val['zn_makers'] ==1){
                return $val[$key];
            }
        }
    }


}

?>
