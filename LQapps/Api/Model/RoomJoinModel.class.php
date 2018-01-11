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
//        parent::__construct();
    }
    public function getRoom($id,$roomid){
        $where = array();
        $where['zn_member_id']= $id;
        $where['zn_room_id'] = $roomid;
        return $this->where($where)->find();
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
}

?>
