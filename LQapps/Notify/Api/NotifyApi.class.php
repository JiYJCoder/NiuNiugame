<?php
/*
会员API
*/
namespace Notify\Api;
use Notify\Api\Api;
use Notify\Model\NotifyModel;

class NotifyApi extends Api{
	/**
	 * 构造方法，实例化操作模型
	 */
	protected function _init(){
		$this->model = new NotifyModel();
	}

	//获得支付日志
	public function apiGetSysInfo(){
		return $this->model->getsysInfo();
	}

	//获取在线人数
    public function apiGetNumPer($roomid){
        $this->model->getJoinPer($roomid);
    }

	

	
}
