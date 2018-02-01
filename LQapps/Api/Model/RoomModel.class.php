<?php //设计师说 数据处理，数据回调
namespace Api\Model;
use Member\Api\MemberApi as MemberApi;
use LQLibs\Util\Page as Page;
defined('in_lqweb') or exit('Access Invalid!');

class RoomModel extends PublicModel {
    protected $_validate = array(
        array('zc_rate','require','缺少必要参数1!'), //倍率
        array('zn_min_score','require','缺少必要参数2！'), //最底上庄分数
        array('zn_bet_between_s','require','缺少必要参数3！'), //下注范围开始
        array('zn_bet_between_e','require','缺少必要参数4！'), //下注范围结束
        array('zn_extract','require','缺少必要参数5！'), //抽庄比例
        array('zn_member_id','require','创建者ID不存在！'), //创建者ID
        array('zn_room_type','require','缺少必要参数6！'), //类型：1公开，2不公开
        array('zn_confirm','require','缺少必要参数7！'), //进房确认：1需要，2不需要
        array('zn_pay_type','require','缺少必要参数8！'), //付费模式：1钟点房，2日费房
        //array('zc_number','uniq','缺少必要参数！'), //编号，自动生成
        array('zc_number', '', "房间号重复", self::MUST_VALIDATE, 'unique'),
        array('zc_title','require','缺少必要参数9！'), //房名,自动生成
        array('zn_play_type','require','缺少必要参数10！'), //玩法 1:五副牌 2:七副牌
        array('zn_bet_time','require','缺少必要参数11！'), //下注时间
        array('zc_title','require','缺少必要参数12！'), //房间名
        array('zn_chatid','require','缺少必要参数13！'), //群聊id
    );
    protected $_auto = array (
        array('zn_cdate','time',1,'function'), // 对create_time字段在更新的时候写入当前时间戳
        array('zn_mdate','time',2,'function'), // 对updatetime字段在更新的时候写入当前时间戳
    );
    public function __construct() {
        parent::__construct();
    }
    //更新信息
    public function updatedRoom($data){
        $id = $data['roomid'];
        unset($data['roomid']);
        return $flag=$this->where('id='.$id)->save($data);
    }
    //创建房间
    public function createRoom($data)
    {
        $flag = $this->create($data);
        $rate =$data['zc_rate'];
        $rateJson = $this->setRate($rate);
        if(!$flag){
            return $this->getError();
        }
        if(!$rateJson){
            return '倍率错误';
        }
        //限制下注时间
        if($data['zn_bet_time']<30){
            return 0;
        }
        //限制最低上分
        if($data['zn_min_score']<0){
            return 0;
        }
        //限制抽庄比例
        if($data['zn_extract']<1|| $data['zn_extract']>15){
            return 0;
        }
        if(!$rateJson){
            return 0;
        }
        $data['zc_rate'] = $rateJson;
        $rid = $this->add($flag);
        return $rid;
    }
    //设置倍率
    public function setRate($rateArray){
        $flag= true;
        for ($i=1;$i<count($rateArray);$i++){
            if($rateArray[$i]<0){
                $flag=false;
                break;
            }
        }
        if(!$flag){
            return 0;
        }
        $rateJson = json_encode($rateArray);
        return $rateJson;
    }
    //房间列表
    public function getData($pagesize=15,$type=1,$id=''){
        $where = array();
        $where['zl_visible'] =1;
        //1 所有房间列表
        //2 自己开的房间
        if($type ==2){
            $where['zn_member_id'] = $id;
            $count= $this->where($where)->count();
        } else{
            $count= $this->count();
        }
        $page=new Page($count,$pagesize);
        $firstRow = $page->firstRow;
        $listRows = $page->listRows;
        $list = $this->where($where)->limit("$firstRow , $listRows")->order('zn_room_type asc,zn_cdate desc')->select();
        return $list;
    }


    public function getRoom($roomid){
        $where = array();
        $where['id'] = $roomid;
        $data=$this->where($where)->find();
        return $data;
    }
    //获取
    public function getRoomNumber($Number){
        $where = array();
        $where['zc_number'] = $Number;
        $data=$this->where($where)->find();
        return $data;
    }

    public function setVal($roomid,$sql,$val){
        $where = array();
        $where['id'] = $roomid;
        return $this->where($where)->setField($sql,$val);
    }

    //解散房间
    public function dissolveRoom($roomid){
        return $this->setVal($roomid,'zl_visible',0);
    }

    //加入过的房间
    public function joinRoomList($roomidArray){

        $list =array();
        $where['zl_visible'] =1;
        foreach ($roomidArray as $key=>$val){
            $where['id'] = $val['zn_room_id'];
            $list[]=$this->where($where)->find();
        }
        return $list;
    }
    //查询是否存在
    public function isRoom($roomid){

        $id =$this->where('id='.$roomid)->getField('zn_member_id');
        return $id;
    }
}

?>
