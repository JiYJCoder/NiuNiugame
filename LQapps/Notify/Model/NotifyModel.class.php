<?php
/*
会员API
*/
namespace Notify\Model;
use Think\Model;
/**
 * 消息通知类
*/
class NotifyModel extends Model{
    protected $tableName = 'article';
	protected $follow;


	protected $_auto = array(
	);

    public function __construct() {
		parent::__construct();
		$this->sysNotice=M("Article");
	}


	public function getSysInfo(){

		return $this->sysNotice->where("zn_sort,id DESC")->limit(5)->select();
	}

	public function getJoinPer($roomid){
	    $where = array();
        $where['zn_room_id'] =$roomid;
        $where['zl_visible'] = 1;
	    return M('RoomJoin')->where($where)->select();
    }
	

	
}
