<?php
/*
 * 后台公共Model
 * Author：国雾院theone（438675036@qq.com）
 * Date:2013-06-27
 */
namespace Ucenter\Model;
use LQPublic\Model\Base;
use Member\Api\MemberApi as MemberApi;

class PublicModel extends Base {
	protected $autoCheckFields =false; //如果定义的模型没有对应的数据表,最好设置为虚拟模型
	public $pc_os_label,$pc_index_list;
	
    /**
      +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::_initialize();
      +----------------------------------------------------------
     */
    public function __construct() {
		parent::__construct();
		$set_config=F('set_config','',COMMON_ARRAY);
		if($set_config){
			$thumb_config=C('THUMB_CONFIG');;
			if($set_config["THUMB_MAX_WIDTH"]) $thumb_config["THUMB_MAX_WIDTH"]=intval($set_config["THUMB_MAX_WIDTH"]);
			if($set_config["THUMB_MAX_HEIGHT"]) $thumb_config["THUMB_MAX_HEIGHT"]=intval($set_config["THUMB_MAX_HEIGHT"]);
			if($set_config["THUMB_TYPE"]) $thumb_config["THUMB_TYPE"]=$set_config["THUMB_TYPE"];
			if($set_config["THUMB_WATER_OPEN"]) $thumb_config["THUMB_WATER_OPEN"]=$set_config["THUMB_WATER_OPEN"];
			if($set_config["THUMB_WATER_IMAGE"]) $thumb_config["THUMB_WATER_IMAGE"]=$set_config["THUMB_WATER_IMAGE"];
			if($set_config["THUMB_WATER_ALPHA"]) $thumb_config["THUMB_WATER_ALPHA"]=intval($set_config["THUMB_WATER_ALPHA"]);
			if($set_config["THUMB_WATER_TYPE"]) $thumb_config["THUMB_WATER_TYPE"]=$set_config["THUMB_WATER_TYPE"];
			C('THUMB_CONFIG',$thumb_config);
			$this->setIndexUrl();//设置列表页的url
		}
    }

	/*插入会员日志记录
		$key:   会员日志操作 
		$member:  会员数据
		$obj_id:    操作ID
	*/
	public function lqMemberLog($tcid=0){
		$Member = new MemberApi;
		$Member->addMemberLog(strtolower(ACTION_NAME),'',$tcid);
	}

	//设置列表页的 当前 url
	public function setIndexUrl() {
		if(ACTION_NAME=='index') session('index_current_url',__SELF__);
	}

	//***********************数据表常用操作************start**************************************************
	//通用数据提交-保存方法
	public function lqCommonSave($back_url='') {
		//表单数据构建
		$data = $this->lqPostData();
		if(!is_array($data)) return array('status' => 0, 'msg' => C('ALERT_ARRAY')["saveError"].":".$data);
		if(intval($data["id"])>0){
			$saveStatus=$this->save($data);
			$lnid=$data["id"];
		}else{
			$saveStatus=$this->add($data);
			$lnid = $this->getLastInsID();//返回刚插入的记录ID
		}
		if($saveStatus){
			$this->lqMemberLog($lnid);//写入日志
			if($data["id"]){//更新表
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"], 'url' => $back_url!=''?$back_url:U('edit?tnid='.$data["id"]) );
			}else{//插入表
				//写入cookie s
				$cookie_data= $this->getSafeData('LQL','p') ?  $this->getSafeData('LQL','p') : array() ;
				foreach($data as $lcKey =>$lcValue){//去除c key
						if(substr($lcKey,1,1)=='c'){
							if( !array_key_exists($lcKey,C("POST_FILTER_KEY")) )	unset($data[$lcKey]);
						}
				}				
				$cookie_data= array_merge($data,$cookie_data);//续加表单内容
				setcookie('last_post_cookie',lq_array_to_cookiestr($cookie_data),0);
				//写入cookie e				
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"], 'url' =>  $back_url!=''?$back_url:U(CONTROLLER_NAME.'/add') );
			}
		}else{
			return array('status' => 0, 'msg' => C('ALERT_ARRAY')["error"]);
		}
	}
	
    //更改  Label 标题 
    public function lqLabel() {
		$id = I("post.tnid",'0','int');
        $data = array();
        $data[$this->pc_os_label] = I("post.vlaue",'','');
		//验证
		C("TOKEN_ON",false);
		if (!$this->create($data)) return array('status' => 0, 'msg' => $this->getError() );
		if ($this->where(" zn_member_id = ".session('member_auth')["id"]." and id = ".$id)->save($data)) {
			$this->lqMemberLog($id);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"]);
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }
	//单记录删除
    public function lqDelete($isTree=0) {
		$id = I("get.tnid",'0','int');
		if($id==0) return array('status' => 0, 'msg' => C("ALERT_ARRAY")["illegal_operation"] );		
		if ($this->where(" zn_member_id = ".session('member_auth')["id"]." and id = ".$id)->delete()) {
			    $this->lqMemberLog($id);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }
	//多记录删除
    public function lqDeleteCheckbox() {
		$data=array();
		$data["id"]  = array('in',  I("get.tcid",'','lqSafeExplode') );
		$data['zn_member_id'] = session('member_auth')["id"] ;
		if ($this->where($data)->delete()) {
			    $this->lqMemberLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }	
	//***********************数据表常用操作************end**************************************************
	
}
?>