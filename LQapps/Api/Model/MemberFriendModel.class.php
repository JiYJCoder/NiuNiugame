<?php //设计师说 数据处理，数据回调
namespace Api\Model;
use Think\Model;
use LQLibs\Util\Page;

class MemberFriendModel extends PublicModel {
    protected $_validate = array(
        array('zn_way','require','缺少必要参数2！'), //添加方式1他加我，2我加他
        array('zn_mid','require','缺少必要参数3！'), //用户id
        array('zn_friend_id','require','缺少必要参数4！'), //zn_friend_id申请id
        array('zc_remark','require','缺少必要参数5！'), //备注
    );
    protected $_auto = array (
        array('zn_cdate','time',1,'function'), // 对create_time字段在更新的时候写入当前时间戳
    );
    public function __construct() {
        parent::__construct();
    }

    public function setVal($id,$friendid,$sql,$val){
        $where['zn_mid'] = $id;
        $where['zn_friend_id'] = $friendid;
        return $this->where($where)->setField($sql,$val);
    }

    public function createFrient($data){
        if($data['zn_way']==1){
            $data1['zn_way'] =2;
        }else{
            $data1['zn_way']=1;
        }
        $data1['zn_mid'] = $data['zn_friend_id'];
        $data1['zn_friend_id'] = $data['zn_mid'];
        $data1['zc_remark'] = $data['username'];
        unset($data['username']);
        $data=$this->create($data);
        $data1=$this->create($data1);
        if(!$data){
            return   $this->getError();
        }
        $this->add($data1);
        $id=$this->add($data);
        return $id?$id:0;
    }
    //删除好友
    public function delFrient($id,$friendid){
        return $this->setVal($id,$friendid,'zl_visible',0);
    }
    //好友列表
    public function getFrientList($id,$pagesize=15){
        $where['zl_visible'] = 1;
        $where['zn_mid'] = $id;
        $count =$this->where($where)->count();
        $page=new Page($count,$pagesize);
        $firstRow = $page->firstRow;
        $listRows = $page->listRows;
        return $list = $this->where($where)->limit("$firstRow , $listRows")->order('zn_cdate')->select();
    }
    //修改备注
    public function modifyMark($id,$friendid,$val){
        return $this->setVal($id,$friendid,'zc_remark',$val);
    }
    //判断是否好友
    public function isfrend($toidArray,$id){
        $flagArray = array();
        foreach ($toidArray as $key=>$val){
            $where['zn_friend_id']=$val['zc_to'];
            $where['zn_mid']=$id;
            $flag = $this->where($where)->find();
            $flag=$flag ? 1:0;
            $flagArray[$key]= $flag;
        }
        return $flagArray;
    }
    //单个查询
    public function isfrendSingle($toid,$id){
        $where['zn_friend_id']=$toid;
        $where['zn_mid']=$id;
        $flag = $this->where($where)->find();
        return $flag;
    }



}

?>
