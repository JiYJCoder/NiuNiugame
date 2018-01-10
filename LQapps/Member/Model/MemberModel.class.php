<?php
/*
会员API
*/
namespace Member\Model;
use Think\Model;
/**
 * 会员模型
*/
class MemberModel extends Model{
	protected $follow,$model_log,$model_designer,$model_bank,$model_integration_log,$login_member_info,$model_member_token,$model_favorite,$model_pay_log;
	//protected $connection =MEMBER_DB_DSN;//数据库连接信息
	
	/* 会员模型自动验证 */
	protected $_validate = array(
		/* 验证会员微信的openid */

//		array('zc_openid', 'checkOpenidRule',"微信的OPENID不合法:仅允许英文字母及数字[28个字符]", self::EXISTS_VALIDATE, 'callback'), //会员名规则
//		array('zc_openid', '', 'openid被占用', self::EXISTS_VALIDATE, 'unique'), //openid被占用
//		array('zl_role', 'lqrequire',"请选择会员角色", self::EXISTS_VALIDATE), //会员名规则


		/* 验证会员名 */
//		array('zc_account', '', '会员帐号被占用', self::EXISTS_VALIDATE, 'unique'), //会员名被占用
		/* 验证密码 */
		array('zc_password', 'checkPasswordRule',"会员密码不合法:仅允许英文字母，（@#$!*）及数字[6-30个字符]", self::EXISTS_VALIDATE, 'callback'), //密码规则
		/* 验证邮箱 */
		/*array('zc_email', 'email', '邮箱格式不正确', self::EXISTS_VALIDATE), //邮箱格式不正确
		array('zc_email', '1,32', '邮箱长度不合法', self::EXISTS_VALIDATE, 'length'), //邮箱长度不合法
		array('zc_email', '', "邮箱被占用", self::EXISTS_VALIDATE, 'unique'), //邮箱被占用*/

//		array('zc_email', 'email', '邮箱格式不正确', self::EXISTS_VALIDATE), //邮箱格式不正确
//		array('zc_email', '1,32', '邮箱长度不合法', self::EXISTS_VALIDATE, 'length'), //邮箱长度不合法
//		array('zc_email', '', "邮箱被占用", self::EXISTS_VALIDATE, 'unique'), //邮箱被占用

		/* 验证手机号码 */
//		array('zc_mobile', 'isMobile', '手机格式不正确', self::EXISTS_VALIDATE,'function'), //手机格式不正确
		array('zc_mobile', '', '手机号被占用', self::EXISTS_VALIDATE, 'unique'), //手机号被占用
		//管理员标识
		array('zc_nickname','1,100','昵称在1~100个字符间',self::EXISTS_VALIDATE,'length'),
//		array('zl_sex',array(0,1,2),'性别必填', self::EXISTS_VALIDATE,'in'),//性别
	);

	/* 会员模型自动完成 */
	protected $_auto = array(
		array('zc_easemob_account', 'lqNull', self::MODEL_INSERT,'function'),
		array('zc_easemob_password', 'lqNull', self::MODEL_INSERT,'function'),
		array('zc_openid', 'create_openid', self::MODEL_INSERT, 'callback'),
		array('zc_password', 'lq_ucenter_md5', self::MODEL_INSERT, 'function',SALT),
		array('zc_salt', SALT, self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),
		array('zn_reg_ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
		array('zn_last_login_ip', 'get_client_ip', self::MODEL_INSERT,'function', 1),
	);
	//保护字段（add或edit不能操作）
	protected $_protected_field=array('zn_login_times','zn_trylogin_times','zn_last_login_time','zn_trylogin_lasttime','zl_account_bind','zl_openid_bind','zl_mobile_bind','zl_email_bind','zn_cdate','zn_mdate','zn_pay_integration','zn_rank_integration','zc_easemob_account','zc_easemob_password','zl_visible');
	
    public function __construct() {
		parent::__construct();

//		$this->follow=M("follow");//微信粉丝-模型
		$this->model_log=M("member_log");//会员日志-模型
//		$this->model_designer=M("designer");//设计师-模型
//		$this->model_bank=M("bank");//银行-模型
		$this->model_integration_log=M("member_integration_log");//会员积分日志-模型
		$this->model_member_token=M("member_token");//会员授权-模型
//		$this->model_favorite=M("member_favorite");//我喜欢-模型

		$this->model_pay_log=M("pay_log");//支付日志-模型
		
		$this->login_member_info=session('member_auth');
	}
	

	/**
	 * 检测会员信息
	 * @param  string  $field  会员名
	 * @param  integer $type   会员名类型 1-会员名，2-会员邮箱，3-会员电话
	 * @return integer         错误编号
	 */
	public function checkField($field, $type = 1){
		$data = array();
		switch ($type) {
			case 1:
				$data['zc_account'] = $field;
				break;
			case 2:
				$data['zc_email'] = $field;
				break;
			case 3:
				$data['zc_mobile'] = $field;
				break;
			default:
				return 0; //参数错误
		}
		return $this->create($data) ? 1 : $this->getError();
	}
	
		
	/**
	 * 检测微信的OPENID是不是合法
	 * @param  string $zc_account 会员名
	 * @return boolean          ture - 可用，false - 不可用
	 */
	protected function checkOpenidRule($openid){
		if($openid=='') return true;
		if( preg_match("/^[a-zA-Z0-9-_]{28}$/", $openid) )  return true;
		return false;		
	}
	protected function create_openid($openid){
		if(!$openid) {
			return "LQ".NOW_TIME.lq_random_string(16,4);
		}else{
			return $openid;
		}
	}
	
	/**
	 * 检测会员帐号是不是合法
	 * @param  string $zc_account 会员名
	 * @return boolean          ture - 可用，false - 不可用
	 */
	protected function checkAccountRule($zc_account){return isAccount($zc_account);}
	
	/**
	 * 检测密码是不是合法
	 * @param  string $zc_account 密码
	 * @return boolean          ture - 可用，false - 不可用
	 */
	protected function checkPasswordRule($zc_password){return isPassword($zc_password);}	
	protected function checkIsDesigner($IsDesigner){
		if($IsDesigner){
			$data=$this->getPostData();
			if($data["zl_role"]!=1) return false;
		}
		return true;
	}	

	/**
	 * mid-auth 登录认证
	 * @param  int      $mid  会员ID
	 * @param  string   $auth 会员auth
	 * @return bool  	登录成功  true or false
	 */
	public function midAuth($mid, $auth){
		if(!$mid|!$auth){
			return false;
		}
		$member=$this->where( " zl_visible=1 and id=".intval($mid) )->field('id,zc_account,zn_last_login_time')->find();
		if($member){
			$auth_array = array(
				'id'             => $member['id'],
				'zc_account'        => $member['zc_account'],
				'zl_role'        => $member['zl_role'],
				'zn_last_login_time' => $member['zn_last_login_time'],
			);
			if( lq_data_auth_sign($auth_array) == $auth ){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	

	/**
	 * 会员登录认证
	 * @param  string  $username 会员名
	 * @param  string  $password 会员密码
	 * @param  integer $type     会员名类型 （1-会员名，2-邮箱，3-手机，4-mid）
	 * @param  integer $sha1     sha1加密开启 （1-开，0-关）
	 * @return integer           登录成功-会员ID，登录失败-错误编号
	 */
	public function login($username, $password, $type = 3, $sha1 = 1, $where = ''){
		if(!isAccount($username)&&$type==1) return -1; //帐号不合法
		if(!isEmail($username)&&$type==2) return -1; //邮箱不合法
		if(!isMobile($username)&&$type==3) return -1; //电话号码不合法
		
		$sql = '1';
		switch ($type) {
			case 1:
				$sql.=" and zc_account = '".$username."'";
				break;
			case 2:
				$sql.=" and zc_email = '".$username."'";
				break;
			case 3:
				$sql.=" and zc_mobile = '".$username."'";
				break;
			case 4:
				$sql.=" and id = ".intval($username);
				break;
			default:
				return 0; //参数错误
		}
		if($where!=''){
				$sql.=$where;
		}
		/* 获取会员数据 */
		$member = $this->where($sql)->field("`id`,`zc_account`,`zc_email`,`zc_mobile`,`zc_password`,`zc_salt`,`zn_trylogin_times`,`zn_trylogin_lasttime`,`zl_visible`")->find();
		if(is_array($member) && $member['zl_visible']){
			$lntime_interval=NOW_TIME-$member["zn_trylogin_lasttime"];
			if( ($lntime_interval>C("WEB_SYS_TRYLOGINAFTER")) && ($member["zn_trylogin_times"]>=C("WEB_SYS_TRYLOGINTIMES")) ){
				$this->updateTryLogin($member['id'],0); //更新会员尝试登录信息
				$member["zn_trylogin_times"]=0;
			}
			//系统对登录最大次数处理
			if($member["zn_trylogin_times"]>=C("WEB_SYS_TRYLOGINTIMES"))	return -3;
			/* 验证会员密码 */
			//if(lq_ucenter_md5($password,$member['zc_salt'],$sha1) === $member['zc_password']){
			if(md5($password) === $member['zc_password']){
				$this->updateLogin($member['id']); //更新会员登录信息
				return $this->lqCacheInfo($member["id"]);//缓存当前会员信息
			} else {
				$this->updateTryLogin($member['id']); //更新会员尝试登录信息
				return -2; //密码错误
			}
		} else {
			return -1; //会员不存在或被禁用
		}
	}
	//openid登录
	public function loginByopenid($openid=''){
		if(!$this->checkOpenidRule($openid)) return -1; //openid不合法
		$map = array();
		$map['zc_openid'] = $openid;

		/* 获取会员数据 */
		$member = $this->where($map)->field("`id`,`zc_account`,`zc_email`,`zc_mobile`,`zc_password`,`zc_salt`,`zn_trylogin_times`,`zn_trylogin_lasttime`,`zl_visible`")->find();
		if(is_array($member) && $member['zl_visible']){
			/* 验证会员密码 */
				$this->updateLogin($member['id']); //更新会员登录信息
				return $this->lqCacheInfo($member["id"]);//缓存当前会员信息
		} else {
			return -1; //会员不存在或被禁用
		}
	}	
	//获取会员信息(按ID)
	public function lqGetInfoByID($mid){
			$member=$this->where(" id = ".intval($mid))->field('*')->find();
			if(!$member) return 0;
			return $member;
	}	
	//获取会员信息并缓存(按ID)
	public function lqCacheInfo($mid){
			$member=$this->lqGetInfoByID($mid);
			if(!$member) return 0;
			$member["zl_role_label"]=C("MEMBER_ROLE")[$member["zl_role"]];
			$member["zl_is_designer_label"]=$member["zl_is_designer"]?"是设计师":"不是设计师";
			$member["zl_sex_label"]=C("_SEX")[$member["zl_sex"]];
			//会员等会
			$member_rank_arr=C("MEMBER_RANK");
			if($member["zn_rank_integration"]>=$member_rank_arr[1]["min_points"]&&$member["zn_rank_integration"]<=$member_rank_arr[1]["max_points"]){
				$member["member_rank"]=$member_rank_arr[1];
				$member["member_rank_per"]= round(($member["zn_rank_integration"]/$member_rank_arr[1]["max_points"]),2)*100;
			}elseif($member["zn_rank_integration"]>=$member_rank_arr[2]["min_points"]&&$member["zn_rank_integration"]<=$member_rank_arr[2]["max_points"]){
				$member["member_rank"]=$member_rank_arr[2];
				$member["member_rank_per"]= round(($member["zn_rank_integration"]/$member_rank_arr[2]["max_points"]),2)*100;
			}elseif($member["zn_rank_integration"]>=$member_rank_arr[3]["min_points"]&&$member["zn_rank_integration"]<=$member_rank_arr[3]["max_points"]){
				$member["member_rank"]=$member_rank_arr[3];
				$member["member_rank_per"]= round(($member["zn_rank_integration"]/$member_rank_arr[3]["max_points"]),2)*100;
			}elseif($member["zn_rank_integration"]>=$member_rank_arr[4]["min_points"]&&$member["zn_rank_integration"]<=$member_rank_arr[4]["max_points"]){
				$member["member_rank"]=$member_rank_arr[4];
				$member["member_rank_per"]= round(($member["zn_rank_integration"]/$member_rank_arr[4]["max_points"]),2)*100;
			}else{
				$member["member_rank"]=array("rank_name"=>"非积分会员");
				$member["member_rank_per"]=100;
			}
			
			
			unset($member["zc_password"]);//除去密码缓存
			unset($member["zc_pay_password"]);//除去支付密码缓存
			unset($member["zc_salt"]);//除去随机缓存
			S('member',$member,array('prefix'=>"member_".$mid.C("S_PREFIX"),'temp'=>C("SYSTEM_USER")));
			return $member;
	}
	//获取会员表某个字段（如昵称/头像）
	public function lqGetFieldByID($mid,$field){return $this->where("id=".$mid)->getField($field);}
	public function lqGetField($sql='id=0',$field='zc_mobile'){
		$field_arr=split(',',$field);
		if($field=="*"){
			return $this->where($sql)->field("*")->find();
		}else{
			if(count($field_arr)>1){
			return $this->where($sql)->field($field)->find();
			}else{
			return $this->where($sql)->getField($field);
			}			
		}
	}	
	
	/**
	 * 获取会员信息
	 * @param  string  $mid         会员ID或会员名
	 * @param  boolean $is_username 是否使用会员名查询
	 * @return array                会员信息
	 */
	public function lqGetInfo($mid, $is_username = false){
		if($is_username){ //通过用户名获取
		$mid=intval($this->where("zc_account='".$mid."'")->getField('id'));
		}
		if($mid==0) return -1; //会员不存在或被禁用
		$member = S('member','',array('prefix'=>"member_".$mid.C("S_PREFIX"),'temp'=>C("SYSTEM_USER")));
		if(empty($member)){
				$member=$this->lqCacheInfo($mid,1);
		}
		if(is_array($member)){
			return $member;
		} else {
			return -1; //会员不存在或被禁用
		}
	}

	/**
	 * 尝试登录,更新会员登录信息
	 * @param  integer $mid 会员ID
	 */
	protected function updateTryLogin($mid,$times=array('exp','zn_trylogin_times+1')){
		$data = array(
			'id'              => $mid,
			'zn_mdate'   => NOW_TIME,
			//尝试登陆
			'zn_trylogin_times' => $times,
			'zn_trylogin_lasttime'              => NOW_TIME,
		);
		$this->save($data);
	}

	/**
	 * 更新会员登录信息
	 * @param  integer $mid 会员ID
	 */
	public function updateLogin($mid){
		$data = array(
			'id'              => $mid,
			'zn_login_times' => array('exp','zn_login_times+1'),
			'zn_last_login_ip'   => get_client_ip(1),
			'zn_last_login_time'   => NOW_TIME,
			'zn_mdate'   => NOW_TIME,
			'zn_trylogin_times'              => 0,//尝试登陆
			//'zn_trylogin_lasttime'              => NOW_TIME,
		);
		$this->save($data);
		return $data;
	}

	/**
	 * 更新会员信息
	 * @param int $mid 会员id
	 * @param string $password 密码，用来验证
	 * @param array $data 修改的字段数组
	 * @return true 修改成功，false 修改失败
	 */
	public function updateUserFields($mid, $password, $data){
		if(empty($mid) || empty($password) || empty($data)){
			$this->error = '参数错误！';
			return false;
		}

		//更新前检查会员密码
		if(!$this->verifyUser($mid, $password)){
			$this->error = '验证出错：密码不正确！';
			return false;
		}

		//更新会员信息
		$data = $this->create($data);
		if(data){
			return $this->where(array('id'=>$mid))->save($data);
		}
		return false;
	}

	/**
	 * 验证会员密码
	 * @param int $mid 会员id
	 * @param string $password_in 密码
	 * @return true 验证成功，false 验证失败
	 */
	protected function verifyUser($mid, $password_in){
		$member = $this->where(array('id'=>$mid))->field('id,zc_account,zc_password,zc_salt')->find();
		if(lq_ucenter_md5($password_in,$member['zc_salt']) === $member["zc_password"]){
			return true;
		}
		return false;
	}


	//条件-会员列表总数
	public function lqListCount($sqlwhere_parameter){return $this->where($sqlwhere_parameter)->count();}
	//会员列表
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>'','order'=>'`id` DESC')) {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['account_bind_button'] = $laValue['zl_account_bind'] == 1 ? '帐号已绑定' : '帐号未绑定';
			$list[$lnKey]['openid_bind_button'] = $laValue['zl_openid_bind'] == 1 ? '微信已绑定' : '微信未绑定';
			$list[$lnKey]['mobile_bind_button'] = $laValue['zl_mobile_bind'] == 1 ? '电话已绑定' : '电话未绑定';
			$list[$lnKey]['email_bind_button'] = $laValue['zl_email_bind'] == 1 ? '邮箱已绑定' : '邮箱未绑定';
			$list[$lnKey]['role_label'] = C('MEMBER_ROLE')[$laValue['zl_role']];//.'/'.C('YESNO_STATUS')[$laValue['zl_is_designer']];

			//会员等会
			$member_rank_arr=C("MEMBER_RANK");
			if($laValue["zn_rank_integration"]>=$member_rank_arr[1]["min_points"]&&$laValue["zn_rank_integration"]<=$member_rank_arr[1]["max_points"]){
				$list[$lnKey]["member_rank"]=$member_rank_arr[1]["rank_name"];
			}elseif($laValue["zn_rank_integration"]>=$member_rank_arr[2]["min_points"]&&$laValue["zn_rank_integration"]<=$member_rank_arr[2]["max_points"]){
				$list[$lnKey]["member_rank"]=$member_rank_arr[2]["rank_name"];
			}elseif($laValue["zn_rank_integration"]>=$member_rank_arr[3]["min_points"]&&$laValue["zn_rank_integration"]<=$member_rank_arr[3]["max_points"]){
				$list[$lnKey]["member_rank"]=$member_rank_arr[3]["rank_name"];
			}elseif($laValue["zn_rank_integration"]>=$member_rank_arr[4]["min_points"]&&$laValue["zn_rank_integration"]<=$member_rank_arr[4]["max_points"]){
				$list[$lnKey]["member_rank"]=$member_rank_arr[4]["rank_name"];
			}else{
				$list[$lnKey]["member_rank"]='非积分会员';
			}			
			
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
    public function lqListLimit($where='',$limit=10,$orderby='`id` DESC') {
        $list = $this->field($page_config["field"])->where($where)->order($orderby)->limit('0,'.$limit)->select();
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['no'] = $lnKey+1;
        }
        return $list;
    }
	
	//获取数据表 字段注释 theone 2015-01-10 add
	public function lqGetCacheComment() {
		$db   =  $this->dbName?:C('DB_NAME');
		$cache_comment=F('_theone/'.strtolower($db.'.'.$this->tablePrefix.$this->name),'');
		return $cache_comment["_comment"];
	}
	

	//获取表单数据
	public function getPostData() {
		//表单数据构建
        $data=I("post.LQF");
		if(ACTION_NAME=='add'|ACTION_NAME=='edit'){
			if($this->_protected_field){
				foreach($this->_protected_field as $k =>$v) unset($data[$v]);
			}
		}
		if($data["id"]){
			//记录更新时间
			$data= array_merge($data, array('zn_mdate'=>NOW_TIME) );		
		}else{
			//记录插入时间
			$data= array_merge($data, array('zn_cdate'=>NOW_TIME,'zn_mdate'=>NOW_TIME) );			
		}
	 	return $data;	
	 }	
	
	/**
	 * 注册一个新会员
	 * @return integer          注册成功-会员信息，注册失败-错误编号
	 */
	public function register($data){
		/* 添加会员 */
		$data=$this->create($data);//验证
		if (!$data){
			return $this->getError(); //错误详情见自动验证注释
		} else {
			$mid = $this->add($data);
			return $mid ? $mid : 0; //0-未知错误，大于0-注册成功			
		}
	}
	
	/**
	 * 新增会员信息
	 * @param array $data 字段数组
	 * @return true 修改成功，false 修改失败
	 */
	public function lqInsertMember(){
		$data=$this->getPostData();
		if(empty($data)){
			return '参数错误！';
		}
		//验证新增
		$data=$this->create($data);
		if (!$data){
				return $this->getError();			
		}else{
			$mid = $this->add($data);
			return $mid ? $mid : 0; //0-未知错误，大于0-注册成功
		}
		return '不明错误！';
	}

    /*插入成功后的回调方法
    会员角色:1普通会员（业主），2技工会员，3家装服务商，4社区运营商
	*/
	protected function _after_insert($data,$options) {
		//根据角色初始化不同的会员明细表
		if($data["zl_role"]==1){
			if($data["zl_is_designer"]==1){
				$insert_data=array(
				'zn_member_id'=>$data["id"],
				'zc_member_account'=>$data["zc_account"],
				'zn_city'=>$data["zn_city"],
				'zl_level'=>1,
				'zl_good_index'=>0,
				'zl_is_index'=>0,
				'zc_nickname'=>$data["zc_nickname"],
				'zn_join_year'=>'2016',
				'zc_personality_sign'=>$data["zc_nickname"]."的个性签名",
				'zc_resume'=>$data["zc_nickname"]."的设计理念",
				'zc_style_tag'=>'',
				'zl_visible'=>1,
				'zn_cdate'=>NOW_TIME,
				'zn_mdate'=>NOW_TIME,
				);
				$this->model_designer->add($insert_data);
			}
		}elseif($data["zl_role"]==6){
				$insert_data=array(
				'zn_member_id'=>$data["id"],
				'zc_member_account'=>$data["zc_account"],
				'zn_city'=>$data["zn_city"],
				'zl_good_index'=>0,
				'zl_is_index'=>0,
				'zc_bank_name'=>$data["zc_nickname"],
				'zc_content'=>$data["zc_nickname"]."的银行装修贷内容",
				'zc_contact'=>$data["zc_nickname"],
				'zc_contact_tel'=>$data["zc_mobile"],		
				'zl_visible'=>1,
				'zn_cdate'=>NOW_TIME,
				'zn_mdate'=>NOW_TIME,
				);
				$this->model_bank->add($insert_data);
		}
			if(substr($data["zc_headimg"],0,13)=='/uploadfiles/'){
				$image_data=array();
				if($data["zc_headimg"]) $image_data[]=array("key"=>'avatar',"path"=>$data["zc_headimg"]);				
				$thumb_list=lq_thumb_deal($image_data,$data["id"],'avatar');
				M()->execute("UPDATE __PREFIX__member SET zc_headimg_thumb='".$thumb_list[0]."' WHERE id=".$data["id"]);				
			}			
	}
	
	/**
	 * 更新会员信息
	 * @param array $data 修改的字段数组
	 * @return true 修改成功，false 修改失败
	 */
	public function lqUpdateMember($data){
		if(!$data) $data=$this->getPostData();
		if(empty($data)){
			return '参数错误！';
		}
		$data=$this->create($data);//验证更新
		if (!$data){
				return $this->getError();			
		}else{
			unset($data["__hash__"]);
			$this->save($data);
			$this->lqCacheInfo($data["id"]);
			return $data["id"];
		}
		return '不明错误！';
	}
	public function lqSaveMember($data){
		if(empty($data)){
			return '参数错误！';
		}
		$this->save($data);
		$this->lqCacheInfo($data["id"]);
		return $data["id"];
	}
	/*
	会员积分处理
	$mid:会员ID
	$integration:积分值
	$is_add://增或减 1增0减
	*/
	public function lqUpdateIntegration($mid,$integration=1,$is_add=1){
		if($is_add==1){
		$this->where("id=".$mid)->setInc('zn_pay_integration',$integration);
		$this->where("id=".$mid)->setInc('zn_rank_integration',$integration);
		}else{
		$this->where("id=".$mid)->setDec('zn_pay_integration',$integration);
		$this->where("id=".$mid)->setDec('zn_rank_integration',$integration);
		}
		$this->lqCacheInfo($mid);	
	}	
	
    // 更新成功后的回调方法
    protected function _after_update($data,$options){
		if(ACTION_NAME=='edit'|ACTION_NAME=='member'){
			if($data["zl_role"]==1){
				M()->execute("UPDATE __PREFIX__designer SET zc_nickname='".$data["zc_nickname"]."' WHERE zn_member_id=".$data["id"]);
			}
			if(substr($data["zc_headimg"],0,13)=='/uploadfiles/'){
				$image_data=array();
				if($data["zc_headimg"]) $image_data[]=array("key"=>'avatar',"path"=>$data["zc_headimg"]);				
				$thumb_list=lq_thumb_deal($image_data,$data["id"],'avatar');
				M()->execute("UPDATE __PREFIX__member SET zc_headimg_thumb='".$thumb_list[0]."' WHERE id=".$data["id"]);
			}			
		}
		$this->lqCacheInfo($data["id"]);
	}		
	
	/**
	 * 更新会员密码
	 * @param array $data 修改的字段数组
	 * @return true 修改成功，false 修改失败
	 */
	public function lqEditPass(){
		$data=$this->getPostData();
		if(empty($data)){
			return '参数错误！';
		}
		//验证更新
		$data=$this->create($data);
		if (!$data){
				return $this->getError();			
		}else{
			unset($data["__hash__"]);
			$data["zc_salt"]=SALT;
			$data["zc_password"]=lq_ucenter_md5($data["zc_password"],SALT);
			$this->save($data);
			$this->lqCacheInfo($data["id"]);
			return $data["id"];
		}
		return '不明错误！';
	}

	//单记录删除
    public function lqDelete($mid) {
		$data["id"]=$mid;
		$la_check_data = $this->field("id")->where(" zl_visible=1 and id=" .(int)$data["id"] )->find();
		if($la_check_data){//记录使用状态提示
			return -1;
		}
		return $this->where($data)->delete();
    }
	
	//多记录删除
    public function lqDeleteCheckbox($mids) {
		$data["id"]  = array('in',  $mids );
		$data["zl_visible"] = array('eq',1);
		$la_check_data = $this->field("id")->where($data)->select();
		if($la_check_data){//记录使用状态提示
			return -1;
		}
		unset($data["zl_visible"]);
		return $this->where($data)->delete();
    }
    // 删除成功后的回调方法
	protected function _after_delete($data,$options) {
		$where=array();
		$where["zn_member_id"]=$data["id"];
		$this->model_log->where($where)->delete();//会员日志
		$this->model_designer->where($where)->delete();//设计师
		$this->model_bank->where($where)->delete();//银行
		$this->model_integration_log->where($where)->delete();//会员积	
		$this->model_member_token->where($where)->delete();//会员授权
		$this->model_favorite->where($where)->delete();//我喜欢
	}	
	

	/*会员其他#######################s*/
	//会员频操作过滤
	public function lqIsAllowOs($key,$member,$obj_id=0){
		$times=C("REQUEST_SESSION")[$key];
		if($times>0){
			if($obj_id){
			$count=$this->model_log->where(" zc_action='".$key."' and zn_object_id=".$obj_id." and zn_member_id=" .(int)$member["id"]." and zn_day=".date("Ymd"))->count();
			}else{
			$count=$this->model_log->where(" zc_action='".$key."' and zn_member_id=" .(int)$member["id"]." and zn_day=".date("Ymd"))->count();
			}
			return $count>$times?true:false;
		}elseif($times==0){
			if($obj_id){
			$count=$this->model_log->where(" zc_action='".$key."' and zn_object_id=".$obj_id." and zn_member_id=" .(int)$member["id"])->count();
			}else{
			$count=$this->model_log->where(" zc_action='".$key."' and zn_member_id=" .(int)$member["id"])->count();
			}
			return $count>$times?true:false;			
		}
	}		
	//会员日志
	public function lqMemberLog($key,$member,$obj_id=0){
		if($member){
			$this->login_member_info=$member;
		}else{
			if($key=='login'|$key=='register') $this->login_member_info=session('member_auth');
		}
		if(!$this->login_member_info) die("Please Login In");
		
		if($key) $description=C("LQ_MEMBER_LOG")[$key];
		if(!$description){
			$description=$key;
			$action=ACTION_NAME;
		}else{
			$action=$key;
		}
		$data_memberlog=array();
		$data_memberlog['zn_member_id'] = intval($this->login_member_info["id"]);//操作会员
		$data_memberlog['zc_member_account'] = $this->login_member_info["zc_account"]?$this->login_member_info["zc_account"]:'';//操作会员		
		$data_memberlog['zc_action'] = strtolower($action);//操作名 
		$data_memberlog['zn_object_id'] = intval($obj_id);//操作对象ID
		$data_memberlog['zc_url'] =  lq_get_url();//操作地址
		$data_memberlog['zc_description'] = $description;//操作描述
		$data_memberlog['zn_ip'] = get_client_ip(1);//操作id
		$data_memberlog['zn_day'] = date("Ymd");//操作日期		
		$data_memberlog['zn_cdate'] = NOW_TIME;//操作时间
		
		if($key=='un_subscribe_designer'){
			M()->execute("DELETE FROM __PREFIX__member_log where zn_member_id=".$member["id"]." and zc_action='subscribe_designer' and zn_object_id=".$obj_id);
		}else{
			$this->model_log->add($data_memberlog);
		}
		
		//插入会员积分
		if(C("LQ_MEMBER_INTEGRATION")[$key]){
			$this->lqInsertIntegration($key,$member);
		}
		return 1;
	}
	//判断是否允许积分
	public function lqIsAllowIntegration($key,$member){
		if(C("LQ_MEMBER_INTEGRATION")[$key]&&$member){
			return $this->model_log->where(" zc_action='".$key."' and zn_member_id=" .(int)$member["id"]." and zn_day=".date("Ymd"))->getField('id');
		}		
	}	
	//插入会员积分
	public function lqInsertIntegration($key,$member){
			if(!$this->login_member_info) $this->login_member_info=$member;
			$data_integration=array();
			$data_integration['zn_member_id'] = intval($this->login_member_info["id"]);//操作会员
			$data_integration['zc_member_account'] = $this->login_member_info["zc_account"]?$this->login_member_info["zc_account"]:'';//操作会员		
			$data_integration['zc_type'] = $key;//积分类型  
			$data_integration['zn_integration'] = C("LQ_MEMBER_INTEGRATION")[$key];//积分类型数值
			$data_integration['zn_cdate'] = NOW_TIME;//操作时间		
			$this->model_integration_log->add($data_integration);
			//实时更改会员积分
			$this->lqUpdateIntegration($this->login_member_info["id"],$data_integration['zn_integration'],1);
			return $key;//积分成功
	}
	
	//条件-日志列表总数
	public function lqLogCount($sqlwhere_parameter){return $this->model_log->where($sqlwhere_parameter)->count();}
	//条件-日志列表
    public function lqLogList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>'','order'=>'`id` DESC')) {
        $list = $this->model_log->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['ip'] = long2ip($laValue["zn_ip"]);
			$list[$lnKey]['description'] = lq_cutstr($laValue["zc_description"],60);
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }	
	//日志多记录删除
    public function lqDeleteLog($mids) {
		$data=array();
		if(is_numeric($mids)){
		$data["id"]  = array('eq',  $mids );
		}else{
		$data["id"]  = array('in',  $mids );
		}
		return $this->model_log->where($data)->delete();
    }	

	//条件-积分列表总数
	public function lqIntegrationCount($sqlwhere_parameter){return $this->model_integration_log->where($sqlwhere_parameter)->count();}
	//条件-积分列表
    public function lqIntegrationList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>'','order'=>'`id` DESC')) {
        $list = $this->model_integration_log->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['description'] = C("LQ_MEMBER_LOG")[$laValue["zc_type"]];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }	
	//积分多记录删除
    public function lqDeleteIntegration($mids) {
		$data["id"]  = array('in',  $mids );
		return $this->model_integration_log->where($data)->delete();
    }	
	/*会员其他#######################e*/

	/*粉丝#######################s*/
	public function lqFollowCount($sqlwhere_parameter){return $this->follow->where($sqlwhere_parameter)->count();}
	public function lqInsertFollow($data){$this->follow->add($data);}
	public function lqUpdateFollow($data,$openid){$this->follow->where("zc_openid='$openid'")->save($data);}
	//条件-积分列表
    public function lqFollowList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>'','order'=>'`id` DESC')) {
        $list = $this->follow->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }		
	/*粉丝#######################e*/
		
	
	/*授权#######################s*/
	public function lqInsertToken($member){
		$data=$auth=array();
        $auth = array(
            'id'             => $member['id'],
            'zc_account'        => $member['zc_account'],
			'zl_role'        => $member['zl_role'],
            'zn_last_login_time' => $member['zn_last_login_time'],
        );
		$data["zn_member_id"]=$member["id"];
		$data["zc_member_account"]=$member["zc_account"];
		$data["zc_token"]=lq_data_auth_sign($auth);
		$data["zn_login_time"]=$member["zn_last_login_time"];		
		$data["zc_client_type"]=$member["client_type"];
		$data["zc_openid"]=$member["zc_openid"];		
		$this->model_member_token->add($data);
		return $data["zc_token"];
	}
	public function lqUpdateToken($member){
		$check_token=$this->model_member_token->where(" zc_client_type='".$member['client_type']."' and zn_member_id=" .(int)$member["id"])->getField('id');
		if(!$check_token){
			return $this->lqInsertToken($member);
		}else{
			$data=$auth=array();
			$auth = array(
				'id'             => $member['id'],
				'zc_account'        => $member['zc_account'],
				'zl_role'        => $member['zl_role'],
				'zn_last_login_time' => $member['zn_last_login_time'],
			);
			$data["zc_token"]=lq_data_auth_sign($auth);
			$data["zn_login_time"]=$member["zn_last_login_time"];		
			$this->model_member_token->where("zc_client_type='".$member['client_type']."' and zn_member_id=".intval($member["id"]))->save($data);
			return $data["zc_token"];
		}
	}
	public function lqGetToken($id){
			if(is_weixin()){
						$client_type='WECHAT';
			}else{
				if($_SERVER['HTTP_HOST']=='test.lxjjz.cn'){
						$client_type='WECHAT';
				}else{				
						$client_type='APP';
				}		
			}			

		return $this->model_member_token->field("*")->where("zc_client_type='".$client_type."' and zn_member_id=".intval($id))->find();
	}	
	/*授权#######################e*/
	
	public function lqInsertLove($id=0,$key=1,$member=array()){
		$data=array();
		$data["zn_type"]=$key;
		$data["zn_object_id"]=$id;
		$data["zn_member_id"]=$member["id"];
		$data["zc_member_account"]=$member["zc_account"];
		$data['zn_cdate'] = NOW_TIME;//操作时间		
		return $this->model_favorite->add($data);
	}
	public function lqDeleteLove($id=0,$key=1,$member=array()){
		if(is_numeric($id)){
			$where="zn_type='".intval($key)."' and zn_member_id=" .(int)$member["id"]." and zn_object_id=".intval($id);
		}else{
			$where=array();
			$where["id"]  = array('in',  $id );
		}
		return $this->model_favorite->where($where)->delete();	
	}

	/*支付日志#######################s*/
	public function lqInsertPay($data){return $this->model_pay_log->add($data);}
	public function lqUpdatePay($data,$where){$this->model_pay_log->where($where)->save($data);}
	public function lqGetPay($where,$field="*"){
		$field_arr=split(',',$field);
		if($field=="*"){
			return $this->model_pay_log->where($where)->field("*")->find();
		}else{
			if(count($field_arr)>1){
			return $this->model_pay_log->where($where)->field($field)->find();
			}else{
			return $this->model_pay_log->where($where)->getField($field);
			}			
		}
	}	
	/*支付日志#######################e*/
	
	
}
