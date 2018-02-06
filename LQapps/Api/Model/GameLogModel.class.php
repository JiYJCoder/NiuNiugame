<?php //设计师说 数据处理，数据回调
namespace Api\Model;
use Think\Model;

class GameLogModel extends PublicModel {
    protected $_validate = array(
        array('zn_member_id','require','缺少必要参数1!'), //用户id
        array('zn_room_id','require','缺少必要参数2！'), //房间id
        array('zc_is_boss','require','缺少必要参数3！'), //是否庄家 0不是，1是
//        array('zn_points_total','require','缺少必要参数4！'), //玩家总分数
        array('zn_number','require','缺少必要参数5！'), //局数
        array('zn_points_give','require','缺少必要参数7！'), //抽水分数
        array('zn_points_left','require','缺少必要参数8！'), //结余
        array('zc_result','require','缺少必要参数9！'), //压牌结果
        array('zn_few','require','缺少必要参数10！'), //第几副牌
        array('zc_name','require','缺少必要参数11！'), //用户昵称
        array('zc_name','require','缺少必要参数12！'), //流水分数
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
        $where['zn_visible'] =1;
        //查询个人
        if($id){
            $where['zn_member_id'] =$id;
            $list = $this->where($where)->order('zn_number')->select();
            return $list;
        }
        //查询局数
        if($number){
            if(!$id){
                $where['zn_number'] =$number;
                $list = $this->where($where)->select();
                $total = 0;
                foreach ($list as $key=>$val){
                    $total+= intval($val['zn_points_left']);
                }
                foreach ($list as $key1=>$val1){
                    $list[$key1]['zn_points_total'] = $total;
                }
                return $list;
            }
            $where['zn_member_id'] =$id;
            $where['zn_number'] =$number;
            return $this->where($where)->find();
        }
        //分组
        $list =$this->field('zn_number')->where($where)->order('zn_number')->group('zn_number')->select();
        foreach ($list as $key=>$val){
            $where['zn_number'] =$val['zn_number'];
            $DRs = $this->where($where)->select();
            $list[$key]['DRs'] = $DRs;
        }
        $total = 0;
        foreach ($list as $key=>$val){
            foreach ($val['DRs'] as $val1){
                $total+= $val1['zn_points_left'];
            }
        }
        foreach ($list as $key=>$val){
            foreach ($val['DRs'] as $key1=> $val1){
                $list[$key]['DRs'][$key1]['zn_points_total']=$total;
            }
        }
        return $list;
    }

    public function createGameLog($data){
        $data = $this->create($data);
        if(!$data){
            $this->getError();
        }
        $flag =$this->add($data);
        return $flag;
    }
    //设置
    public function setAll($roomid,$sql,$val){
        $where['zn_room_id'] = $roomid;
        $this->where($where)->setField($sql,$val);
    }
}

?>
