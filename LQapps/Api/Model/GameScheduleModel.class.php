<?php //设计师说 数据处理，数据回调
namespace Api\Model;
use Think\Model;
use LQLibs\Util\Page;

class GameScheduleModel extends PublicModel {
    protected $_validate = array(
        array('zn_room_id','require','缺少必要参数1！'),
        array('zn_status','require','缺少必要参数2！'),
    );
    protected $_auto = array (
        array('zn_cdate','time',1,'function'), // 对create_time字段在更新的时候写入当前时间戳
    );
    public function __construct() {
           parent::__construct();
    }

    public function createGameSchedule($data){
        $data=$this->create($data);
        if(!$data){
          return   $this->getError();
        }
        $id=$this->add($data);
        return $id ? $id:0;
    }
    public function getVal($roomid,$sql=''){
        $where['zn_room_id'] = $roomid;
        $status=$this->where($where)->getField($sql);
        return $status;
    }
    public function setVal($roomid,$sql='',$val){
        $where['zn_room_id'] = $roomid;
        $status=$this->where($where)->setField($sql,$val);
        return $status;
    }
}

?>
