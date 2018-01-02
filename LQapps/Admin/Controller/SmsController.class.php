<?php
/*短信发送器
****
*/
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use Member\Api\MemberApi as MemberApi;

class SmsController extends PublicController{
    public function __construct() {
		parent::__construct();
		$search_content_array=array(
            'time_start'=>I('get.time_start',lq_cdate(0,0,(-2592000))),
            'time_end'=>I('get.time_end',lq_cdate(0,0)),
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值	
		C("TOKEN_ON",false);	
	}

	//常规汇总
    public function index(){
		
		
//装修优惠券通知       //lqSendSms($hd_application["zc_follow_mobile"].",13560444215,13249131367",array($visit_time,$hd_application["zc_order_no"],'',$hd_application["zc_name"],$hd_application["zc_mobile"],$hd_application["zc_area"].$hd_application["zc_address"]),168881);

        if(IS_POST) {

            $activity=I("post.activity");
            $money=I("post.money");
            $validity=I("post.validity");
            $coupon=I("post.coupon");
            $phone=I("post.phone");

            if(!isMobile($phone))
            {
                $this->ajaxReturn(array('status' => 0, 'msg' => "请输入有效的手机号码" ));
            }
            if(!$activity)
            {
                $this->ajaxReturn(array('status' => 0, 'msg' => "请填写活动名称" ));
            }
            if(!$money)
            {
                $this->ajaxReturn(array('status' => 0, 'msg' => "请填写优惠金额" ));
            }
            if(!$validity)
            {
                $this->ajaxReturn(array('status' => 0, 'msg' => "请填写有效期" ));
            }
            if(!$coupon)
            {
                $this->ajaxReturn(array('status' => 0, 'msg' => "请填写优惠卷码" ));
            }


            $data=array();
            $data['zc_session_id'] = session_id();//系统session_id
            $data['zc_mobile'] = $phone;//手机号码
            $data['zc_check_code'] = $coupon;//校验码:6位数字
            $data['zc_action'] = 'robot_sms';//操作
            $data['zc_sms_content'] = '尊敬的客户，您参与《'.$activity.'》活动，获得装修优惠券'.$money.'元，有效期到'.$validity.'。优惠码:'.$coupon.'。如有疑问，请随时联系专属热线400-900-5521，谢谢。';//短信内容
            $data['zl_use'] = 0;//使用状态
            $data['zn_exp_date'] = NOW_TIME+600;//失效期时间（10分钟）
            $data['zn_cdate'] = NOW_TIME;//记录创建时间
            M("sms_log")->add($data);
            lqSendSms($phone,array($activity,$money,$validity,$coupon),168881);
            $this->ajaxReturn(array('status' => 1, 'msg' =>'短信通知发送成功', 'url' => U('index'), 'flag' => 'index'));
        }




		
		$this->assign("sys_current",'<ol class="breadcrumb" style="padding:10px 0px;margin:5px 0px;"><span><a><i class="fa fa-location-arrow"></i> 当前位置：</a></span><li><a href="/sys-index.php/Index/index" title="">系统桌面</a></li><li><a href="javascript:;"> 狸想家平台</a></li><li><a href="javascript:;">其它</a></li><li><a href="javascript:;">辅助工具
</a></li><li class="active">短信发送器</li></ol>');//搜索表单赋值
        $this->assign("sys_heading",'短信发送器');
		
        $this->display('index');
    }
	
}
?>