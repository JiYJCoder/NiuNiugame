<?php //设计师说 数据处理，数据回调
namespace Api\Model;
use Think\Model;

class GameLogModel extends PublicModel {
    protected $_validate = array(
        array('zn_member_id','require','缺少必要参数!'), //用户id
        array('zn_room_id','require','缺少必要参数！'), //房间id
        array('zc_is_boss','require','缺少必要参数！'), //是否庄家
        array('zn_points_total','require','缺少必要参数！'), //玩家分数
        array('zn_number','require','缺少必要参数！'), //局数
        array('zn_visible','require','缺少必要参数！'), //是否可见
    );
    protected $_auto = array (
        array('zn_cdate','time',1,'function'), // 对create_time字段在更新的时候写入当前时间戳
        array('zn_mdate','time',2,'function'), // 对updatetime字段在更新的时候写入当前时间戳
    );
    public function __construct() {
           parent::__construct();
    }
    //查询数据
    public function getData($roomid,$id='',$number=''){
        $where = array();
        $where['zn_room_id'] = $roomid;
        if($id){
            $where['zn_member_id'] =$id;
            return $this->where($where)->order('zn_cdate asc')->select();
        }
        if($number){
            if(!$id){
                $where['zn_number'] =$number;
                return $this->where($where)->select();
            }
            $where['zn_member_id'] =$id;
            $where['zn_number'] =$number;
            return $this->where($where)->find();
        }
        $list =$this->field('zn_number')->where($where)->order('zn_number asc')->group('zn_number')->select();

        foreach ($list as $key=>$val){
            $where['zn_number'] =$val['zn_number'];
            $DRs = $this->where($where)->select();
            $list[$key]['DRs'] = $DRs;
        }
        return $list;
    }


}

?>
