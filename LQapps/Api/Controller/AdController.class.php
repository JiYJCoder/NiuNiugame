<?php
namespace Api\Controller;

use Think\Controller;

defined('in_lqweb') or exit('Access Invalid!');

class AdController extends PublicController
{

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
    }

//获取BANNER
    public function index()
    {
        $data=D("Api/AdPosition")->getAdPositionById($this->lqgetid)["list"];
        if(!$data) $data = array();
		
		//微信
		if(!is_weixin()){
			foreach($data as $lnKey=>$laValue){
				if($laValue["client"]==2) unset($data[$lnKey]);
			}
		}
        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => '获取BANNER'), $this->JSONP);
    }

}