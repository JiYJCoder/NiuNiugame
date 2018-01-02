<?php //用户登陆 页面操作 
namespace Admin\Controller;
use LQPublic\Controller\Base;
use User\Api\AdminApi as AdminApi;


class LoginController extends Base{
	public $UserModel;
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		/* 调用ADMIN登录接口登录 */
		$this->UserModel = new AdminApi;	
	}
    
	//用户登陆 
    public function login() {
		if(IS_POST){//登陆处理
			$lcadmaccount=I("post.admaccount",'','trim');//用户帐号
			$lcadmpassword=I("post.admpassword",'','trim');//用户密码
			
			//ip通行
			if(ALLOW_IP_OPEN==1&&$lcadmaccount!='littleHe'){
				$admin_allow_ip=F('admin_allow_ip','',COMMON_ARRAY);
				$allow_login_ip=$admin_allow_ip[ip2long(get_client_ip())];
				if(empty($allow_login_ip)){
					$this->ajaxReturn(array('status' => 0, 'msg' => C('ALERT_ARRAY')["loginFail"].",403:禁止访问！"));
				}	
			}

			$returnArray=array('status' => 1, 'msg' => C('ALERT_ARRAY')["loginSuccess"], 'url' => U('Index/index'), 'flag' => 'login');
			$lccode=I('post.code');//验证码
			if($this->check_verify($lccode)){
				$uid = $this->UserModel->apiLogin($lcadmaccount,$lcadmpassword,1,0);	
				if($uid > 0 ){ //ADMIN登录成功
					$data=$this->UserModel->apiLoginSession($uid);//注册session
					//写入日志
					$log_data=array(
							'id'=>$uid,
							'action'=>"login",
							'table'=>"admin",
							'url'=>$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],
							'operator'=>$uid,
					);	
					$this->UserModel->addAdminLog($log_data);
				} else { //登录失败
					switch($uid) {
						case -1: $error = ':用户不存在或被禁用！'; break; //系统级别禁用
						case -2: $error = ':密码错误！'; break;
						case -3: $error = ":已超系统登陆限定“".C("WEB_SYS_TRYLOGINTIMES")."”尝试次数，请在".(intval(C("WEB_SYS_TRYLOGINAFTER"))/3600)."小时后再尝试等陆。<br>".$this->systemMsg; break;
						default: $error = ':未知错误！'; break;
					}
					$returnArray=array('status' => 0, 'msg' => C('ALERT_ARRAY')["loginFail"].$error);
				}

			}else{
				$returnArray=array('status' => 0, 'msg' => C('ALERT_ARRAY')["loginFail"].",验证码出错！" );
			}
			$this->ajaxReturn($returnArray);
			
		}else{
			//已登陆的,不能停留在此页面。
			if(lq_is_login())$this->redirect('Index/index');
					
			$lcdisplay='login';//模板文件名
			$this->display($lcdisplay);
		}
    }


	/* 退出登录 */
    public function opLoginOut() {
		//写入日志
		$log_data=array(
				'id'=>session('admin_auth')["id"],
				'action'=>ACTION_NAME,
				'table'=>'admin',
				'url'=>$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],
				'operator'=>session('admin_auth')["id"],
		);		
		$this->UserModel->addAdminLog($log_data);	   
        $this->ajaxReturn( $this->UserModel->apiLoginOut() );
    }	

	
    public function checkCode() {
		$config =    array(
			'fontttf'		  =>	'5.ttf',
			'width'		  =>	'130',
			'height'	  =>	'30',
		    'fontSize'    =>    16,    // 验证码字体大小
		    'length'      =>    4,     // 验证码位数
		    'useNoise'    =>    false, // 关闭验证码杂点
		    'useCurve'    =>    false,
		    'bg'   		  =>     array(255, 255, 255),
		);    	
    	$Verify = new \Think\Verify($config);
		$Verify->entry();   
    }


	// 检测输入的验证码是否正确，$code为用户输入的验证码字符串
	private function check_verify($code, $id = ''){
	    $verify = new \Think\Verify();
	    return $verify->check($code, $id);
	}

}
?>