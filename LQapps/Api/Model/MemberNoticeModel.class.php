<?php //设计师说 数据处理，数据回调
namespace Api\Model;
use Think\Model;
use LQLibs\Util\Page;

class MemberNoticeModel extends PublicModel {
    protected $_validate = array(
        array('zn_mid','require','缺少必要参数2！'), //用户id
        array('zc_content','require','缺少必要参数3！'), //内容
    );
    protected $_auto = array (
        array('zn_cdate','time',1,'function'), // 对create_time字段在更新的时候写入当前时间戳
    );
    public function __construct() {
           parent::__construct();
    }

    public function createNotif($data){
        $data=$this->create($data);
        if(!$data){
          return   $this->getError();
        }
        $id=$this->add($data);
        return $id ? $id:0;
    }

    public function delNotif($id,$sql,$val){
        $where['id'] = $id;
        return $flag=$this->where($where)->setField($sql,$val);
    }

    public function getList($id){
        $where['zl_visible'] = 1;
        $where['zn_mid'] = $id;
        $list=$this->where($where)->limit(5)->order('zn_way,zn_cdate')->select();
        foreach ($list as $key=>$val){
            if($val['zn_way']==1){
                $content=M('Article')->where('id='.$val['zn_notifyid'])->getField('zc_title');
                $list[$key]['zc_content'] = $content;
            }
        }
        return $list;
    }
    public function getAnnouncement($id){
        $where['zl_visible'] = 1;
        $where['zn_mid'] = $id;
        $where['zn_way'] = 1;
        $list=$this->where($where)->limit(5)->order('zn_way,zn_cdate')->order('zn_cdate')->select();
        foreach ($list as $key=>$val){
            $content=M('Article')->where('id='.$val['zn_notifyid'])->getField('zc_title');
            $list[$key]['zc_content'] = $content;
        }
        return $list;
    }
}

?>
