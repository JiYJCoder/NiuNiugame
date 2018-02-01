<?php //设计师说 数据处理，数据回调
namespace Api\Model;
use Think\Model;
use LQLibs\Util\Page;

class ChatModel extends PublicModel {
    protected $_validate = array(
        array('zc_msg_id','require','缺少必要参数2！'), //消息id
        array('zn_timestamp','require','缺少必要参数3！'), //消息发送时间
        array('zc_direction','require','缺少必要参数4！'), //玩家分数
        array('zc_to','require','缺少必要参数5！'), //局数
        array('zc_from','require','缺少必要参数6！'), //是否可见
        array('zc_chat_type','require','缺少必要参数7！'), //zc_chat_type
        array('zc_bodies','require','缺少必要参数8！'), //消息body
        array('zn_way','require','缺少必要参数9！'), //1，接收，2发送
    );
    protected $_auto = array (
        array('zn_cdate','time',1,'function'), // 对create_time字段在更新的时候写入当前时间戳
    );
    public function __construct() {
           parent::__construct();
    }

    public function createChat($data){
        $data=$this->create($data);
        if(!$data){
          return   $this->getError();
        }
        $id=$this->add($data);
        return $id ? $id:0;
    }
    //设置已读
    public function setRead($toid,$formid){
        $where['zc_to'] =$toid;
        $where['zc_from'] =$formid;
        return $flag=$this->where($where)->setField('zl_is_read',1);
    }
    //聊天记录
    public function getChatList($toid='',$formid,$pagesize=15){
        $where['zc_to'] =$toid;
        $where['zc_from'] =$formid;
        $count =$this->where($where)->count();
        $page=new Page($count,$pagesize);
        $firstRow = $page->firstRow;
        $listRows = $page->listRows;
        if($toid==''){
            unset($where['zc_to']);
            $list = $this->field('zc_to,zn_toname')->where($where)->group('zc_to')->order('zn_timestamp')->limit(10)->select();
            return $list;
        }
        $list = $this->where($where)->limit("$firstRow , $listRows")->order('zn_timestamp')->select();
        return $list;
    }

}

?>
