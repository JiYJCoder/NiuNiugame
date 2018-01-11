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
    public function addRoom($data){
        $flag=$this->create($data);
        if(!$flag){
            return $this->getError();
        }
        return $this->add($data);
    }
}

?>
