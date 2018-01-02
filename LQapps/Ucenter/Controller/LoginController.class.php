<?php
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:会员登陆
*/
namespace Ucenter\Controller;
use LQPublic\Controller\Base;
use Member\Api\MemberApi as MemberApi;

class LoginController extends Base{
	public $model_member;
    /**
      +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::_initialize();
      +----------------------------------------------------------
     */
    public function __construct() {
		parent::__construct();
	    /* 调用UC登录接口登录 */
		$this->model_member = new MemberApi;			
	}
    
	//用户登陆 
    public function index() {
		if(IS_POST){//登陆处理
			
			$lcAccount=I("post.account");//用户帐号
			$lcPassword=I("post.password");//用户密码	
			$returnArray=array('status' => 1, 'msg' => C('ALERT_ARRAY')["loginSuccess"], 'url' => U('ucenter/index/index'), 'flag' => 'login');
			$lccode=I('post.code');//验证码
			
			if($this->check_verify($lccode)){
				$mid = $this->model_member->apiLogin($lcAccount,$lcPassword,1,0,' and (zl_is_designer=1 or zl_role=6)');
				if($mid > 0 ){ //MEMBER登录成功
					$data=$this->model_member->apiLoginSession($mid);//注册session
					$this->model_member->addMemberLog("login");//写入日志
				} else { //登录失败
					switch($mid) {
						case -1: $error = ':用户不存在或被禁用！'; break; //系统级别禁用
						case -2: $error = ':密码错误！'; break;
						case -3: $error = ":已超系统登陆限定“".C("WEB_SYS_TRYLOGINTIMES")."”尝试次数，请在".(intval(C("WEB_SYS_TRYLOGINAFTER"))/3600)."小时后再尝试等陆。<br>".$this->systemMsg; break;
						default: $error = ':未知错误！'; break; // 0-接口参数错误（调试阶段使用）
					}
					$returnArray=array('status' => 0, 'msg' => C('ALERT_ARRAY')["loginFail"].$error);
				}
			}else{
				$returnArray=array('status' => 0, 'msg' => C('ALERT_ARRAY')["loginFail"].",验证码出错！" );
			}
			$this->ajaxReturn($returnArray);			
		}else{
			//已登陆的,不能停留在此页面。
			if(lq_is_login('member')){
					$this->redirect('ucenter/index/index');
			}
			$lcdisplay='login';//模板文件名
			$this->display($lcdisplay);
		}
    }


	/* 退出登录 */
    public function login_out() {
		//写入日志
		$this->model_member->addMemberLog('login_out');	   
        $this->ajaxReturn($this->model_member->apiLoginOut());
    }	

	
    public function check_code() {
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