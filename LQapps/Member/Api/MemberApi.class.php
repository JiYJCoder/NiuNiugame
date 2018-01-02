<?php
/*
会员API
*/
namespace Member\Api;
use Member\Api\Api;
use Member\Model\MemberModel;

class MemberApi extends Api{
	/**
	 * 构造方法，实例化操作模型
	 */
	protected function _init(){
		$this->model = new MemberModel();
	}

	//检测会员名
	public function checkUsername($username){return $this->model->checkField($username, 1);}

	//检测邮箱
	public function checkEmail($email){return $this->model->checkField($email,2);}

	//检测手机
	public function checkMobile($mobile){return $this->model->checkField($mobile, 3);}
	
	//获取数据表 字段注释 theone 2015-01-10 add
	public function apiGetCacheComment() {
		return $this->model->lqGetCacheComment();
	}	
	
	//获取数据表 字段注释 theone 2015-01-10 add
	public function apiM() {
		return $this->model;
	}	
	
    /**
     * 检测会员是否登录
     * @return integer 0-未登录，大于0-当前登录会员ID
     */
    public static function apiIsLogin(){
        $member = session('member_auth');
        if (empty($member)) {
            return 0;
        } else {
            return session('member_auth_sign') == $this->dataAuthSign($member) ? $member['mid'] : 0;
        }
    }	
	
	/**
	 * 数据签名认证
	 * @param  array  $data 被认证的数据
	 * @return string       签名
	 */
	protected function dataAuthSign($data) {
		//数据类型检测
		if(!is_array($data)){
			$data = (array)$data;
		}
		ksort($data); //排序
		$code = http_build_query($data); //url编码并生成query字符串
		$sign = sha1($code); //生成签名
		return $sign;
	}

	/**
	 * 注册一个新会员
	 * @return integer          注册成功-会员信息，注册失败-错误编号
	 */
	public function apiRegister($data){return $this->model->register($data);}

	/**
	 * mid-auth 授权认证
	 * @param  int      $mid  会员ID
	 * @param  string   $auth 会员auth
	 * @return bool  	登录成功  true or false
	 */
	public function apiMidAuth($mid, $auth){return $this->model->midAuth($mid, $auth);}

	/**
	 * 会员登录认证
	 * @param  string  $username 会员名
	 * @param  string  $password 会员密码
	 * @param  integer $type     会员名类型 （1-会员名，2-邮箱，3-手机，4-mid）
	 * @param  integer $sha1     sha1加密开启 （1-开，0-关）
	 * @return integer           登录成功-会员ID，登录失败-错误编号
	 */
	public function apiLogin($username, $password, $type = 1, $sha1 = 1, $where = ''){return $this->model->login($username,$password,$type,$sha1,$where);}

	/**
	 * 会员登录信息写入session
	 */
	public function apiLoginSession($mid,$app=false){
		if(preg_match("/^[1-9]\d*$/",$mid)){
			$member=$this->model->lqGetInfoByID($mid);
		}elseif(is_array($mid)){
			$member=$mid;
		}else{
			return 0;
		}
        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'id'             => $member['id'],
            'zc_account'        => $member['zc_account'],
			'zl_role'        => $member['zl_role'],
            'zn_last_login_time' => $member['zn_last_login_time'],
        );
		if($app==false){
			session('member_auth', $auth);
			session('member_auth_sign', $this->dataAuthSign($auth));			
		}else{
			return $this->dataAuthSign($auth);
		}
	}
	//openid登录
	public function loginByopenid($openid){
		return $this->model->loginByopenid($openid);
	}	
    /**
     * 注销当前会员
     * @return void
     */
    public function apiLoginOut(){
        session('member_auth', null);
        session('member_auth_sign', null);
		return array('status' => 1, 'msg' => C('LQ_MEMBER_LOG')["login_out"]."成功", 'url' =>U('ucenter/login/index'));
    }	
	
	/*获取会员信息*************start***********************/
	//获取会员信息(按字段)
	public function apiGetInfo($mid, $is_username = false){
		return $this->model->lqGetInfo($mid,$is_username);
	}
	//获取会员信息(按ID)
	public function apiGetInfoByID($mid){
		return $this->model->lqGetInfoByID($mid);
	}
	//获取会员信息并缓存(按ID)
	public function apiCacheInfo($mid, $is_username = false){
		return $this->model->lqCacheInfo($mid, $is_username);
	}	
	//获取会员表某个字段（如昵称/头像）
	public function apiGetFieldByID($mid,$field = 'zc_nickname'){
		return $this->model->lqGetFieldByID($mid,$field);
	}
	//获取会员表某个字段（如昵称/头像）
	public function apiGetField($sql,$field = 'zc_nickname'){
		return $this->model->lqGetField($sql,$field);
	}
	//更新登录信息
	public function apiUpdateLogin($mid){
		return $this->model->updateLogin($mid);
	}		
	/*获取会员信息*************end***********************/

	
	/**
	 * 更新会员信息
	 * @param int $mid 会员id
	 * @param string $password 密码，用来验证
	 * @param array $data 修改的字段数组
	 * @return true 修改成功，false 修改失败
	 */
	public function apiUpdateInfo($mid, $password, $data){
		if($this->model->updateUserFields($mid, $password, $data) !== false){
			$return['status'] = true;
			$return['msg'] = 'ok';
		}else{
			$return['status'] = false;
			$return['msg'] = $this->model->getError();
		}
		return $return;
	}

	/**
	 * 条件-会员列表总数
	 */
	public function apiListCount($sqlwhere_parameter){
		return $this->model->lqListCount($sqlwhere_parameter);
	}

	/**
	 * 条件-会员列表
	 * @param  firstRow int               会员ID
	 * @param  listRows int               菜单数组
	 * @param  page_config  array         菜单数组
	 */
	public function apiListMember($page_firstRow, $page_listRows,$page_config){
		session('index_current_url',__SELF__);
		return $this->model->lqList($page_firstRow, $page_listRows,$page_config);
	}
	public function apiListLimit($where='',$limit=10,$orderby='`id` DESC'){
		return $this->model->lqListLimit($where, $limit,$orderby);
	}
	//注册一个新会员(后台新增)
	public function apiInsertMember(){return $this->model->lqInsertMember();}
	//更新会员表
	public function apiUpdateMember($data){return $this->model->lqUpdateMember($data);}
	public function apiSaveMember($data){return $this->model->lqSaveMember($data);}
	//会员积分更新
	public function apiUpdateIntegration($mid,$integration=1,$is_add=1){return $this->model->lqUpdateIntegration($mid,$integration,$is_add);}	
	//更新密码
	public function apiEditPass(){return $this->model->lqEditPass();}	
	//插入积分
	public function apiInsertIntegration($key,$member){return $this->model->lqInsertIntegration($key,$member);}		
	//判断是否允许积分
	public function apiIsAllowIntegration($key,$member){return $this->model->lqIsAllowIntegration($key,$member);}	

	//喜欢管理
	public function apiTestLove($id,$key,$member){return $this->model->lqTestLove($id,$key,$member);}	
	public function apiInsertLove($id,$key,$member){return $this->model->lqInsertLove($id,$key,$member);}	
	public function apiDeleteLove($id,$key,$member){return $this->model->lqDeleteLove($id,$key,$member);}	
	public function apiGetFavoriteIds($type,$member){return $this->model->laGetFavoriteIds($type,$member);}

    //更改属性
    public function apiProperty() {
		$lcop=I("get.tcop",'login_clear');
		$data=array();
        $data["id"] = I("get.tnid",'0','int');		
		if($lcop=='login_clear'){
			$data['zn_trylogin_times'] = 0;
			$data['zn_trylogin_lasttime'] = 0;
			$data['zn_mdate'] =NOW_TIME ;
			$op_data= array("status" => 1, "txt" => '成功' ) ;
		}else if($lcop=='account_bind'){
			$data['zl_account_bind'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
			$op_data= array("status" => $data['zl_account_bind'], "txt" => $data['zl_account_bind'] == 1 ? "已绑定" : "未绑定" ) ;			
		}else if($lcop=='openid_bind'){
			$data['zl_openid_bind'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
			$op_data= array("status" => $data['zl_openid_bind'], "txt" => $data['zl_openid_bind'] == 1 ? "已绑定" : "未绑定" ) ;
		}else if($lcop=='mobile_bind'){
			$data['zl_mobile_bind'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
			$op_data= array("status" => $data['zl_mobile_bind'], "txt" => $data['zl_mobile_bind'] == 1 ? "已绑定" : "未绑定" ) ;
		}else if($lcop=='email_bind'){
			$data['zl_email_bind'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
			$op_data= array("status" => $data['zl_email_bind'], "txt" => $data['zl_email_bind'] == 1 ? "已绑定" : "未绑定" ) ;
		}else{
			return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
		}
		if ($this->model->lqSaveMember($data)) {
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' =>$op_data,'id'=>$data["id"]);
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }	

    //更改  zl_visible 
    public function apiVisible($mid=0) {
		$data=array();
        $data["id"] = I("get.tnid",'0','int');
        $data['zl_visible'] = I("get.status",'0','int') == 1 ? 0 : 1;
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->model->lqSaveMember($data)) {
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"],'url' =>'', 'data' => array("status" => $data['zl_visible'], "txt" => $data['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0]),'id'=>$data["id"]);
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }	

	//多记录审批
    public function apiVisibleCheckbox() {
		$ids=I("get.tcid",'','lqSafeExplode');
		$data['zl_visible'] = I("get.status",'0','int') == 1 ? 1 : 0;
		$data['zn_mdate'] = NOW_TIME ;
		$data["id"]  = array('in',$ids);
		if ($this->model->lqSaveMember($data)){
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"], 'url' => U("Member/index"), 'ids' =>$ids);
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
		} 
		
    }		

	//单记录删除 
    public function apiDelete() {
		$mid = I("get.tnid",'0','int');
		$status=$this->model->lqDelete($mid);
		if($status==-1){
			 return array('status' => 0, 'msg' => C("ALERT_ARRAY")["recordVisible"] );
		}else{
			if($status){
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"] , 'url' => U("Member/index"),'id'=>$mid);
			}else{
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
			}
		}
    }	
	
	//多记录删除
    public function apiDeleteCheckbox() {
		$mids=I("get.tcid",'','lqSafeExplode');
		if($mids=='') return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
		$status=$this->model->lqDeleteCheckbox($mids);
		if($status==-1){
			return array('status' => 0, 'msg' => C("ALERT_ARRAY")["recordVisible"] );
		}else{
			if($status) {
					return array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"], 'url' => U("Member/index"),'ids'=>$mids);
				} else {
					return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
			}	
		}
    }		


	/*会员其他#######################s*/
	//会员频操作过滤
	public function apiIsAllowOs($key='operation',$member='',$obj_id=0){
		return $this->model->lqIsAllowOs($key,$member,$obj_id);
	}	
	//会员日志
	public function addMemberLog($key='operation',$member='',$obj_id=0){
		$this->model->lqMemberLog($key,$member,$obj_id);
	}
	//会员日志总数
	public function apiLogCount($sqlwhere_parameter){
		return $this->model->lqLogCount($sqlwhere_parameter);
	}
	//列出会员日志
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
					return array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"], 'url' => U("MemberLog/index"),'ids'=>$mids);
				} else {
					return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
			}	
		}
    }

	//会员积分总数
	public function apiIntegrationCount($sqlwhere_parameter){return $this->model->lqIntegrationCount($sqlwhere_parameter);}
	//列出积分日志
	public function apiIntegrationList($page_firstRow, $page_listRows,$page_config){
		session('index_current_url',__SELF__);
		return $this->model->lqIntegrationList($page_firstRow, $page_listRows,$page_config);		
	}	
	//多积分删除
    public function apiDeleteIntegration() {
		$mids=I("get.tcid",'','lqSafeExplode');
		if($mids=='') return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
		$status=$this->model->lqDeleteIntegration($mids);
		if($status==-1){
			return array('status' => 0, 'msg' => C("ALERT_ARRAY")["recordVisible"] );
		}else{
			if($status) {
					return array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"], 'url' => U("MemberIntegrationLog/index"),'ids'=>$mids);
				} else {
					return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
			}	
		}
    }

	//粉丝总数
	public function apiFollowCount($sqlwhere_parameter){
		return $this->model->lqFollowCount($sqlwhere_parameter);
	}
	//列出粉丝
	public function apiFollowList($page_firstRow, $page_listRows,$page_config){
		session('index_current_url',__SELF__);
		return $this->model->lqFollowList($page_firstRow, $page_listRows,$page_config);		
	}	
	//插新粉丝
	public function apiInsertFollow($data){
		return $this->model->lqInsertFollow($data);
	}	
	//更新粉丝
	public function apiUpdateFollow($data,$openid=''){
		return $this->model->lqUpdateFollow($data,$openid);
	}	
		
	//插新授权
	public function apiInsertToken($member){
		if(!$member) return 0;
		return $this->model->lqInsertToken($member);
	}	
	//更新授权
	public function apiUpdateToken($member){
		return $this->model->lqUpdateToken($member);
	}
	//获得授权
	public function apiGetToken($id=''){
		if(!$id) return 0;
		return $this->model->lqGetToken($id);
	}
	
	
	//插新支付日志
	public function apiInsertPay($data){
		if(!$data) return 0;
		return $this->model->lqInsertPay($data);
	}	
	//更新支付日志
	public function apiUpdatePay($data,$where='1'){
		return $this->model->lqUpdatePay($data,$where);
	}
	//获得支付日志
	public function apiGetPay($where='',$field="*"){
		if(!$where) return 0;
		return $this->model->lqGetPay($where,$field);
	}	
	
		
	
	/*会员其他#######################e*/
	
	

	
}
