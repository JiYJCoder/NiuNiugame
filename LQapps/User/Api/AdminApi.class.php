<?php
/*
用户API
*/
namespace User\Api;
use User\Api\Api;
use User\Model\AdminModel;

class AdminApi extends Api{
	/**
	 * 构造方法，实例化操作模型
	 */
	protected function _init(){
		$this->model = new AdminModel();
	}

	/**
	 * 检测访问权限(节点)
	 * @param int $user 用户信息
	 * @return integer         错误编号
	 */
	public function apiAccessControl($user){
		return $this->model->lqAccessControl($user);
	}
	
	/**
	 * 检测用户名
	 * @param  string  $field  用户名
	 * @return integer         错误编号
	 */
	public function checkUsername($username){
		return $this->model->checkField($username, 1);
	}
	
	/**
	 * 检测邮箱
	 * @param  string  $email  邮箱
	 * @return integer         错误编号
	 */
	public function checkEmail($email){
		return $this->model->checkField($email, 2);
	}

	/**
	 * 检测手机
	 * @param  string  $mobile  手机
	 * @return integer         错误编号
	 */
	public function checkMobile($mobile){
		return $this->model->checkField($mobile, 3);
	}


	/**
	 * 注册一个新用户
	 * @param  string $username 用户名
	 * @param  string $password 用户密码
	 * @param  string $email    用户邮箱
	 * @param  string $mobile   用户手机号码
	 * @return integer          注册成功-用户信息，注册失败-错误编号
	 */
	public function apiRegister($username, $password, $email, $mobile = ''){
		return $this->model->register($username, $password, $email, $mobile);
	}

	/**
	 * uid-auth 登录认证
	 * @param  int      $uid  用户ID
	 * @param  string   $auth 用户auth
	 * @return bool  	登录成功  true or false
	 */
	public function apiUidAuth($uid, $auth){
		return $this->model->uidAuth($uid, $auth);
	}

	/**
	 * 用户登录认证
	 * @param  string  $username 用户名
	 * @param  string  $password 用户密码
	 * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
	 * @param  integer $sha1     sha1加密开启 （1-开，0-关）
	 * @return integer           登录成功-用户ID，登录失败-错误编号
	 */
	public function apiLogin($username, $password, $type = 1, $sha1 = 1){
		return $this->model->login($username, $password, $type,$sha1);
	}
	
	/**
	 * 用户登录信息写入session
	 */
	public function apiLoginSession($uid){
		$user=$this->model->lqGetInfoByID($uid);
        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'id'             => $user['id'],
            'zc_account'        => $user['zc_account'],
            'zn_last_login_time' => $user['zn_last_login_time'],
        );
        session('admin_auth', $auth);
        session('admin_auth_sign', lq_data_auth_sign($auth));
	}
	
    /**
     * 注销当前用户
     * @return void
     */
    public function apiLoginOut(){
        session('admin_auth', null);
        session('admin_auth_sign', null);
		return array('status' => 1, 'msg' => C('lqAdminLog')["loginOut"].C('ALERT_ARRAY')["success"], 'url' => U('Login/login'));
    }	
	
	/**
	 * 获取用户信息
	 * @param  string  $uid         用户ID或用户名
	 * @param  boolean $is_username 是否使用用户名查询
	 * @return array                用户信息
	 */
	public function apiGetInfo($uid, $is_username = false){
		return $this->model->lqGetInfo($uid, $is_username);
	}
	public function apiGetInfoByID($uid){
		return $this->model->lqGetInfoByID($uid);
	}	
	/**
	 * 获取用户信息并缓存
	 * @param  string  $uid         用户ID或用户名
	 * @param  boolean $is_username 是否使用用户名查询
	 * @return array                用户信息
	 */
	public function apiCacheInfo($uid, $is_username = false){
		return $this->model->lqCacheInfo($uid, $is_username);
	}

	/**
	 * 更新用户信息
	 * @param int $uid 用户id
	 * @param string $password 密码，用来验证
	 * @param array $data 修改的字段数组
	 * @return true 修改成功，false 修改失败
	 */
	public function apiUpdateInfo($uid, $password, $data){
		if($this->model->lqUpdateInfo($uid, $password, $data) !== false){
			$return['status'] = true;
		}else{
			$return['status'] = false;
			$return['msg'] = $this->model->getError();
		}
		return $return;
	}

	/**
	 * 用户菜单
	 * @param  string  $uid         用户
	 * @return array                菜单数组
	 */
	public function apiGetMenus($uid){
		return intval($uid)>0 ? $this->model->getMenus($uid) : array();
	}

	/**
	 * 条件-用户列表总数
	 */
	public function apiListCount($sqlwhere_parameter){
		return $this->model->lqListCount($sqlwhere_parameter);
	}

	/**
	 * 条件-用户列表
	 * @param  firstRow int               用户ID
	 * @param  listRows int               菜单数组
	 * @param  page_config  array         菜单数组
	 */
	public function apiListAdmin($page_firstRow, $page_listRows,$page_config){
		session('index_current_url',__SELF__);
		return $this->model->lqList($page_firstRow, $page_listRows,$page_config);
	}
	

	//获取数据表 字段注释 theone 2015-01-10 add
	public function apiGetCacheComment() {
		return $this->model->lqGetCacheComment();
	}


	/**
	 * 注册一个新用户
	 */
	public function apiInsertAdmin(){
		return $this->model->lqInsertAdmin();
	}

	//更新用户表
	public function apiUpdateAdmin($uid=0) {return $this->model->lqUpdateAdmin($uid);}
	//更新密码
	public function apiEditPass($uid=0) {return $this->model->lqEditPass($uid);}	
	//设置用户权限
	public function apiUpdatePopedom($uid=0) {return $this->model->lqUpdateAdmin($uid);}
	//密码打印
	public function ucenter_md5_print($pass) {echo lq_ucenter_md5($pass);}	
	//实时获取 权限id集
	public function apiGetPopedom($uid){return $this->model->getPopedom($uid);}

    //更改属性
    public function apiProperty() {
		$lcop=I("get.tcop",'is_lock','');
		$data=array();
        $data["id"] = I("get.tnid",'0','int');	
		if($data["id"]==1) return array('status' => 0, 'msg' => "原始管理员不能操作！");
		if($lcop=='login_clear'){
		$data['zn_trylogin_times'] = 0;
		$data['zn_trylogin_lasttime'] = 0;
		$data['zn_mdate'] =NOW_TIME ;
		$op_data= array("status" => 1, "txt" => '成功' ) ;
		}
		if ($this->model->lqSaveAdmin($data)) {
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' =>$op_data );
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }	

    //更改  zl_visible 
    public function apiVisible() {
		$uid=I("get.tnid",'0','int');
		$data=array();
        $data["id"] =$uid;
        $data['zl_visible'] = I("get.status",'0','int') == 1 ? 0 : 1;
		if($uid==1) return array('status' => 0, 'msg' => "原始管理员不能操作！");
		if(session('admin_auth')["id"]==$uid) return array('status' => 0, 'msg' => "自己不能审核自己！" );
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->model->lqSaveAdmin($data)) {
				$log_data=array(
					'id'=>$data["id"],
					'action'=>ACTION_NAME,
					'table'=>"admin",
					'url'=>$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],
					'operator'=>session('admin_auth')["id"],
				);				
				$this->addAdminLog($log_data);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"],'url' =>'', 'data' => array("status" => $data['zl_visible'], "txt" => $data['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0] ));
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }	

	//单记录删除 
    public function apiDelete() {
		$uid=I("get.tnid",'0','int');
		if($uid==1) return array('status' => 0, 'msg' => "原始管理员不能操作！");
		
		if(session('admin_auth')["id"]==$uid) return array('status' => 0, 'msg' => "自己不能删除自己！" );
		$status=$this->model->lqDelete($uid);
		if($status==-1){
			 return array('status' => 0, 'msg' => C("ALERT_ARRAY")["recordVisible"] );
		}else{
			if($status){
				$log_data=array(
						'id'=>$uid,
						'action'=>ACTION_NAME,
						'table'=>"admin",
						'url'=>$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],
						'operator'=>session('admin_auth')["id"],
				);					
				$this->addAdminLog($log_data);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"] , 'url' => U("Admin/index") );
			}else{
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
			}
		}
    }	
	
	//多记录删除
    public function apiDeleteCheckbox() {
		$uids  = I("get.tcid",'','');
		if($uids=='') return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
		$kill_ids=array(1,session('admin_auth')["id"]);
		$uids_array = explode(",",$uids);
		$id = array_diff($uids_array,$kill_ids);
		if(!$id) return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
		$id=implode(",",$id);
		$status=$this->model->lqDeleteCheckbox($id);
		if($status==-1){
			return array('status' => 0, 'msg' => C("ALERT_ARRAY")["recordVisible"] );
		}else{
			if($status) {
					$log_data=array(
							'id'=>array('in', $id ),
							'action'=>ACTION_NAME,
							'table'=>"admin",
							'url'=>$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],
							'operator'=>session('admin_auth')["id"],
					);				
					$this->addAdminLog($log_data);//写入日志
					return array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"], 'url' => U("Admin/index") );
				} else {
					return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
			}	
		}
    }		

	//新增日志
	/**
	 * 用户日志
	 * @param  string  $uid         用户ID
	 * @return array                菜单数组
	 */
	public function addAdminLog($log_data){
		$login_admin_info=session('admin_auth');
		if(!$login_admin_info) die("Please log in");
		$la_admin_action=F('admin_action','',COMMON_ARRAY);//系统操作行为
		$la_sys_table=F('sys_table','',COMMON_ARRAY);//系统数据表
		
		if(is_array($log_data["id"])){
		    	$lcalmoduleid=$log_data["id"][0]." (".$log_data["id"][1].") ";
		}else{
			if(is_numeric($log_data["id"])){
				$lcalmoduleid=$log_data["id"];
			}else{
				$lcalmoduleid=0;
			}
		}
		$lctable=CONTROLLER_TO_TABLE($log_data["table"]);
		//数据表模型
		if(array_key_exists($lctable, $la_sys_table)){
			$lclabel= $login_admin_info["zc_account"]."对数据表".$lctable."进行".$la_admin_action[$log_data["action"]]."操作。";
			$lntype=1;
		}else{//虚拟模型
			$lcaction=$la_admin_action[$log_data["action"]];
			if(!$lcaction) $lcaction=$log_data["action"];
			$lclabel= $login_admin_info["zc_account"]."对".$log_data["table"]."模型,进行".$lcaction."操作。";
			$lntype=0;
		}
		$data_admlog=array();
		$data_admlog['zn_type'] = $lntype;//模型类型
		$data_admlog['zc_action'] = $log_data["action"];//操作名  
		$data_admlog['zc_table'] = $lctable;//操作的表或文件
		$data_admlog['zc_table_id'] = $lcalmoduleid;//操作传参id
		if($log_data["description"]){
		$data_admlog['zc_description'] = str_replace("<","&lt;",$log_data["description"]);//操作描述 htmlspecialchars()
		}else{
		$data_admlog['zc_description'] =$lclabel."url请求:".$log_data["url"];//操作描述
		}
		$data_admlog['zn_ip'] = get_client_ip(1);//操作id
		$data_admlog['zn_cdate'] = NOW_TIME;//操作时间
		if($login_admin_info["id"]){
			$data_admlog['zn_operator'] = intval($login_admin_info["id"]);//操作用户
			$data_admlog['zc_account'] = $login_admin_info["zc_account"];//操作用户
		}else{
			$data_admlog['zn_operator'] = $log_data["operator"];//操作用户
			$data_admlog['zc_account'] =  'test_theone0750';//操作用户
		}
		$this->model->lqAdminLog($data_admlog);
	}	
	//日志总数
	public function apiLogCount($sqlwhere_parameter){
		return $this->model->lqLogCount($sqlwhere_parameter);
	}
	//列出日志
	public function apiLogList($page_firstRow, $page_listRows,$page_config){
		session('index_current_url',__SELF__);
		return $this->model->lqLogList($page_firstRow, $page_listRows,$page_config);		
	}
	//多日志记录删除
    public function apiDeleteLog() {
		$mids=I("get.tcid",'','lqSafeExplode');
		if($mids=='') return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
		$status=$this->model->lqDeleteLog($mids);
		if($status==-1){
			return array('status' => 0, 'msg' => C("ALERT_ARRAY")["recordVisible"] );
		}else{
			if($status) {
					return array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"], 'url' => U("AdminLog/index"),'ids'=>$mids);
				} else {
					return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
			}	
		}
    }
		
}
