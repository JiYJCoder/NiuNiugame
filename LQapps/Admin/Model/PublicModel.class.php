<?php
/*
 * 后台公共Model
 * Author：国雾院theone（438675036@qq.com）
 * Date:2013-06-27
 * 验证类型支持 in between equal length regex expire ip_allow ip_deny，默认为regex 
 */
namespace Admin\Model;
use LQPublic\Model\Base;
use User\Api\AdminApi as AdminApi;

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
			$thumb_config=C('THUMB_CONFIG');
			if($set_config["INT_THUMB_MAX_WIDTH"]) $thumb_config["INT_THUMB_MAX_WIDTH"]=intval($set_config["INT_THUMB_MAX_WIDTH"]);
			if($set_config["INT_THUMB_MAX_HEIGHT"]) $thumb_config["INT_THUMB_MAX_HEIGHT"]=intval($set_config["INT_THUMB_MAX_HEIGHT"]);
			if($set_config["THUMB_TYPE"]) $thumb_config["THUMB_TYPE"]=$set_config["THUMB_TYPE"];
			if($set_config["THUMB_WATER_OPEN"]) $thumb_config["THUMB_WATER_OPEN"]=$set_config["THUMB_WATER_OPEN"];
			if($set_config["THUMB_WATER_IMAGE"]) $thumb_config["THUMB_WATER_IMAGE"]=$set_config["THUMB_WATER_IMAGE"];
			if($set_config["INT_THUMB_WATER_ALPHA"]) $thumb_config["INT_THUMB_WATER_ALPHA"]=intval($set_config["INT_THUMB_WATER_ALPHA"]);
			if($set_config["THUMB_WATER_TYPE"]) $thumb_config["THUMB_WATER_TYPE"]=$set_config["THUMB_WATER_TYPE"];
			C('THUMB_CONFIG',$thumb_config);
		}
		$this->setIndexUrl();//设置列表页的url
    }

	/* lqAdminLog 管理日志记录
		$lcfilepre:   操作模块 - 
		$lcAction:    增删改 等等
	*/
	public function lqAdminLog($tcid=0){
		$log_data=array(
				'id'=>$tcid,
				'action'=>ACTION_NAME,
				'table'=>CONTROLLER_NAME,
				'url'=>$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],
				'operator'=>session('admin_auth')["id"],
		);		
		$User = new AdminApi;
		$User->addAdminLog($log_data);
	}

	//设置列表页的 当前 url
	public function setIndexUrl() {
		if(ACTION_NAME=='index') session('index_current_url',__SELF__);
	}	
	
	//********************************检查记录操作：单项删除，不定项删除，不定项回收***************************************
	//单项删除检查
	protected function lqDeletCheck($data,$isTree) {
		$lcreturn='';
		if($data["id"]==0) $lcreturn=C("ALERT_ARRAY")["fail"];
		if($isTree==1){
			//记录使用状态提示
		    $tree = new \LQLibs\Util\Category(CONTROLLER_TO_TABLE(CONTROLLER_NAME), array('id', 'zn_fid', 'zc_caption'));
			$child_ids = $tree->get_child($data["id"],10,'');
			$where["id"] = array('exp',' IN ('.$child_ids.') ');
			$where["zl_visible"] = array('eq',1);
			$la_check_data = $this->field("id")->where($where)->select();
			if($la_check_data) $lcreturn=C("ALERT_ARRAY")["recordVisible"];
		}else{
			$la_check_data = $this->field("zl_visible")->where("id=" .(int)$data["id"] )->find();
			if($la_check_data){//记录使用状态提示
				if($la_check_data["zl_visible"]==1) $lcreturn=C("ALERT_ARRAY")["recordVisible"];
			}else{
				$lcreturn=C("ALERT_ARRAY")["recordNull"];//无记录
			}
		}		
		return $lcreturn;
	}
	
	//不定项删除检查
	public function lqDelectCheckboxCheck($data) {
		$lcreturn='';
		if($data["id"]=='') $lcreturn=C("ALERT_ARRAY")["fail"];
		
		//记录使用状态提示
		$data["zl_visible"] = array('eq',1);
		$la_check_data = $this->field("id")->where($data)->select();
		if($la_check_data) $lcreturn=C("ALERT_ARRAY")["recordVisible"];		
		
		return $lcreturn;
	}
	//不定项回收检查
	public function lqRecycleCheckboxCheck($data) {
		$lcreturn='';
		if($data["id"]=='') $lcreturn=C("ALERT_ARRAY")["fail"];
		return $lcreturn;
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
			$this->lqAdminLog($lnid);//写入日志
			if($data["id"]){//更新表
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"], 'url' =>  $back_url!=''?$back_url:U(CONTROLLER_NAME.'/edit/tnid/'.$data["id"]));
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
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"], 'url' => $back_url!=''?$back_url:U(CONTROLLER_NAME.'/add'));
			}
		}else{
			return array('status' => 0, 'msg' => C('ALERT_ARRAY')["error"]);
		}
	}
	
    //单记录审批
    public function lqVisible($isTree=0) {
		$data=array();
        $data["id"] = I("get.tnid",'0','int');
		if($isTree){
			//记录使用状态提示
		    $tree = new \LQLibs\Util\Category(CONTROLLER_TO_TABLE(CONTROLLER_NAME), array('id', 'zn_fid', 'zc_caption'));
			$child_ids = $tree->get_child($data["id"],10,'');
			$data["id"] = array('exp',' IN ('.$child_ids.') ');
			$url=U($this->pc_index_list);
		}else{
			$url='';
		}
        $data['zl_visible'] = I("get.status",'0','int') == 1 ? 0 : 1;
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"],'url' =>$url, 'data' => array("status" => $data['zl_visible'], "visible_label" => $data['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0] ));
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }	
	
    //单记录更改Sort
    public function lqSort() {
		$data=array();
        $data["id"] = I("post.tnid",'0','int');
        $data["zn_sort"] = I("post.vlaue",'0','int');
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"]);
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }
	
    //更改  Sort 列表
    public function lqSortList() {
        $lcsortid = addslashes(I("post.tcsortid",'',''));
		$lnresetzero = I("post.resetzero",'0','int');
		$lnok=0;
		$laSort=explode (",",$lcsortid);
		if($laSort&&$lcsortid){
			 foreach($laSort as $lnKey=>$lcValue){
				unset($data);
				$lnok=1;
				$data["id"] = $lcValue;
				if($lnresetzero==0){
					$data["zn_sort"] = $lnKey+1;
				}else{
					$data["zn_sort"] = C("COM_SORT_NUM");
				}
				$data["zn_mdate"] = NOW_TIME;
				$data= array_merge($data,array('zn_mdate'=>NOW_TIME));
				$this->save($data);
			 }
		}
        if ($lnok==1) {
			$this->lqAdminLog(array('in',$lcsortid));//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'url' => U($this->pc_index_list));
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }	
	

    //单记录更改Label
    public function lqLabel() {
        $data["id"] = I("post.tnid",'0','int');
        $data[$this->pc_os_label] = I("post.vlaue",'','');
		if($this->where("id='".$data["id"]."'")->getField('zl_visible')==1){
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"].",请反审记录再编辑。");
		}
		if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"]);
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }

    //单记录更改字段值
    public function lqProperty() {
    	return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
    	//以下为模板
		$lcop=I("post.tcos",'','string');
		$lcvlaue=I("post.vlaue",'','string');
		$data=array();
        $data["id"] = I("post.tnid",'0','int');
        if($this->where("id=".$data["id"])->getField('zl_visible')){
		   return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"].",请反审记录再编辑。");
        }
		if($lcop=='name'){
			$data['zc_name'] = $lcvlaue;
		}else if($lcop=='code'){
			$data['zc_code'] = $lcvlaue;
		}else{
			return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
		}
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"]);
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }    
	
	//单记录删除
    public function lqDelete($isTree=0) {
        $data["id"] = I("get.tnid",'0','int');
		$lc_check=$this->lqDeletCheck($data,$isTree);//检查
		if($lc_check) return array('status' => 0, 'msg' => $lc_check );
		if($isTree==1){
		    $tree = new \LQLibs\Util\Category(CONTROLLER_TO_TABLE(CONTROLLER_NAME), array('id', 'zn_fid', 'zc_caption'));
			$child_ids = $tree->get_child($data["id"],10,'');
			$data["id"] = array('exp',' IN ('.$child_ids.') ');
			setcookie("last_post_cookie",NULL, time()-3600);//清除录入操作的记忆
		}
		if ($this->where($data)->delete()) {
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }
	
	//多记录删除
    public function lqDeleteCheckbox() {
		$data["id"]  = array('in',  I("get.tcid",'','lqSafeExplode') );
		$lc_check=$this->lqDelectCheckboxCheck($data);//检查
		if($lc_check) return array('status' => 0, 'msg' => $lc_check );
		
		if ($this->where($data)->delete()) {
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }

	//多记录审批
    public function lqVisibleCheckbox() {
		$data['zl_visible'] = I("get.status",'0','int') == 1 ? 1 : 0;
		$data['zn_mdate'] = NOW_TIME ;
		$data["id"]  = array('in', I("get.tcid",'','lqSafeExplode') );
		if( $this->save($data)) {
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"], 'url' => U($this->pc_index_list));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
		}  	
    }
	
	//通用缓存数据
	public function lqCacheData($return=0){
		$la_cache_data = $this->field("`id`,`".$this->pc_os_label."`")->order('zn_sort','id desc')->where("zl_visible=1")->select();
		$array=lq_return_array_one($la_cache_data,"id",$this->pc_os_label);
		F(CONTROLLER_TO_TABLE($this->getModelName()),$array,COMMON_ARRAY);
		if($return) return $array;
	}
	//***********************数据表常用操作************end**************************************************
	


	
	
}
?>