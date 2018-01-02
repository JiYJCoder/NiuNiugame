<?php //短信系统 Article 数据处理，数据回调
namespace Api\Model;
use Think\Model;
defined('in_lqweb') or exit('Access Invalid!');

class SmsLogModel extends PublicModel {
	protected $tableName        =   'sms_log';	
    public function __construct() {
		parent::__construct();
		
	}

	//检测手机号是否允许接收
    public function isAllowReceive($mobile='',$action='login',$allow = 3){
        $ts = time() - 1800 ;
        $count = $this->where("zc_mobile='$mobile' and zc_action='$action' and zn_exp_date > $ts ")->count();
        if($count > $allow)
        {
            return false;
        }
        return true;

		/*$cdate = $this->where("zc_mobile='$mobile' and zc_action='$action'")->order('id DESC')->getField('zn_cdate');
		if(!$cdate) return true;
		if( (NOW_TIME-$cdate)>600 ){//超出10分钟
			return true;
		}else{
			return false;
		}*/
    }
	
	//检测验证码是否有效
    public function isEffective($mobile='',$action='login',$code=''){
		if(!$code) return false;
		$exp_date = $this->where("zl_use=0 and zc_mobile='$mobile' and zc_action='$action' and zc_check_code='$code'")->getField('zn_exp_date');
		if(!$exp_date) return false;
		if( NOW_TIME<$exp_date ){//超出10分钟
			return true;
		}else{
			return false;
		}
    }	
	
	//更新验证码状态
    public function updateUse($mobile='',$action='login',$code=''){
		M()->execute("UPDATE __PREFIX__sms_log SET zl_use=1 WHERE zl_use=0 and zc_mobile='$mobile' and zc_action='$action' and zc_check_code='$code'");
    }		
	
	//验证码入库
    public function addSms($action='',$mobile='',$code='999999') {
		switch ($action){
		case 'login':
			$sms_content="【狸想家】本次注册验证码：".$code."（10分钟内有效），如非本人操作，请忽略本短信";
			break;				
		case 'register':
			$sms_content="【狸想家】本次注册验证码：".$code."（10分钟内有效），如非本人操作，请忽略本短信";
			break;
		case 'hd_application':
			$sms_content="【狸想家】本次注册验证码：".$code."（10分钟内有效），如非本人操作，请忽略本短信";
			break;
        case 'loan_apply':
            $sms_content="【狸想家】本次验证码：".$code."（10分钟内有效），如非本人操作，请忽略本短信";
            break;
		default:
			return false;
		}
		$data=array();
		$data['zc_session_id'] = session_id();//系统session_id
		$data['zc_mobile'] = $mobile;//手机号码  
		$data['zc_check_code'] = $code;//校验码:6位数字
		$data['zc_action'] = $action;//操作
		$data['zc_sms_content'] = $sms_content;//短信内容  
		$data['zl_use'] = 0;//使用状态
		$data['zn_exp_date'] = NOW_TIME+600;//失效期时间（10分钟）		
		$data['zn_cdate'] = NOW_TIME;//记录创建时间

        M()->execute("update `__PREFIX__sms_log` set zl_use = 1 where zc_mobile='".$mobile."' and zc_action='".$action."'");
		return $this->add($data);
    }
	

	
}

?>
