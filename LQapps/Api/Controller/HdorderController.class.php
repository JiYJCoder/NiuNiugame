<?php
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:家装订单系统
家装:hd(home-decoration)
*****************************************************************************
(1) 返回内容为json格式，采用UTF-8编码。
(2) 信息内容中含有时间字段的，字段值为urlEncode格式。
(3) 返回内容{"status":0,"msg":"操作提示","data":"回调数据","url":"回调响应地址","note":"备注"};
(4) 用户请求加权文档，统一传入uid,token两值。
*****************************************************************************
*/

namespace Api\Controller;
use Think\Controller;
defined('in_lqweb') or exit('Access Invalid!');
class HdorderController extends PublicController{
	protected  $model_progress;
    /** 初始化*/
    public function __construct() {
		parent::__construct();
        self::apiCheckToken();//用户认证
        $this->D_ORDER=D("Api/Hdorder");//订单
        $this->model_hdorder = D("hd_order");
    }
	//首页
    public function index(){
		$this->ajaxReturn(array('status'=>0,'msg'=>'当前端口暂时关闭','data' =>array(),"url"=>"","note"=>""),$this->JSONP);
    }

	//咨询订单
	public function application(){
		if($this->model_member->apiIsAllowOs('hd_application',$this->login_member_info)){
			$this->ajaxReturn(array('status'=>0,'msg'=>'您的请求次数太频繁，请歇息一下！','data' =>'',"url"=>"","note"=>"收集咨询订单"),$this->JSONP);
		}
		$designer_id=I("get.designer_id",'0','int');//设计师
		$type=I("get.type",'1');//咨询类别:1、一键报价; 2、申请设计; 3、活动
		$name=I("get.name",$this->login_member_info["zc_nickname"]);//申请人
		$mobile=I("get.mobile",'');//申请人手机号码
		$check_code=I("get.check_code",'');//手机验证码
		$name_len=lqAbslength($name);
		
		$acreage=I("get.acreage",'0','int');//房屋面积
		$room=I("get.room",'0','int');//房间个数
		$hall=I("get.hall",'0','int');//客厅个数
		$kitchen=I("get.kitchen",'0','int');//厨房个数
		$toilet=I("get.toilet",'0','int');//卫生间个数
		$balcony=I("get.balcony",'0','int');//阳台个数
		$labour_fee=I("get.labour_fee",'0','float');//人工费
		$material_fee=I("get.material_fee",'0','float');//材料费
		$design_fee=I("get.design_fee",'0','float');//设计费
		$qc_fee=I("get.qc_fee",'0','float');//质检费
        $address = I("get.address");
        $decoration_type = I("get.decoration_type","0","int");

		if(!isMobile($mobile)) $this->ajaxReturn(array('status'=>0,'msg'=>'操作失败,请正确输入手机号码！','data' =>'',"url"=>"","note"=>'收集咨询订单'),$this->JSONP);
		if($designer_id){
			if($designer_id>=1&&$designer_id<=9999999999){
			$this->ajaxReturn(array('status'=>0,'msg'=>'设计师ID不正确','data' =>'',"url"=>"","note"=>'收集咨询订单'),$this->JSONP);
			}
		}
        $data=array();

		if($type==1) {
            $msg = '计算返回';
            if ($this->model_member->apiIsAllowOs('hd_application', $this->login_member_info)) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '编辑次数过于频繁，请歇息一下！', 'data' => '', "url" => "", "note" => "收集咨询订单"), $this->JSONP);
            }
        }elseif($type==2){
            $msg='申请';
            if(!D("SmsLog")->isEffective($mobile,'hd_application',$check_code)){
                $this->ajaxReturn(array('status'=>0,'msg'=>'验证码无效！','data' =>'',"url"=>"","note"=>'收集咨询订单'),$this->JSONP);
            }
        }elseif($type==3){
            //检测是否还有名额
            if($this->model_hdorder->is_discount_finish()){
                $this->ajaxReturn(array('status'=>0,'msg'=>'抱歉，本次活动名额已抢光，请留意下一次活动','data' =>'',"url"=>"","note"=>'收集咨询订单'),$this->JSONP);
            }
            $data["zf_deposit"]= '1000';//1999
            $data["zl_deposit_pay"]= '0';
            $msg = '计算返回';
		}else{
            $this->ajaxReturn(array('status' => 0, 'msg' => '出错！', 'data' => '', "url" => "", "note" => "收集咨询订单"), $this->JSONP);
        }

		//咨询订单处理
		$lnday=date("Ymd");//当天日期
		$model_application=M("hd_application");
		$count_application=$model_application->where("zn_day=".$lnday)->count();

		$data["zc_order_no"]='HD.'.NOW_TIME.rand(10,99).lq_return_zero_start(4,$count_application,'').rand(0,9);
		$data["zn_designer_id"]=$designer_id;
		$data["zn_hd_order_id"]=0;
		$data["zl_type"]=$type;
		$data["zn_member_id"]=$this->login_member_info["id"];
		$data["zc_member_account"]=$this->login_member_info["zc_account"];
		$data["zc_name"]=$name;
		$data["zc_mobile"]=$mobile;
		$data["zn_acreage"]=$acreage;
		$data["zn_room"]=$room;
		$data["zn_hall"]=$hall;
		$data["zn_kitchen"]=$kitchen;
		$data["zn_toilet"]=$toilet;
		$data["zn_balcony"]=$balcony;
        if($address) $data['zc_address'] = $address;
		$data["zl_status"]=0;
		$data['zn_day'] = $lnday;//操作日期		
        $data["zc_status_log"]=$data["zc_follow_contact"]=$data["zc_follow_mobile"]='';
        $data['zn_day'] = date("Ymd");
        $data['zc_client_source'] = get_agent();
        $data['zl_decoration_type'] = $decoration_type;
        $data["zn_cdate"]=NOW_TIME;
		$data["zn_mdate"]=NOW_TIME;
		$return=D("Api/Hdorder")->applicationSubmit($data);
		if(preg_match('/^([1-9]\d*)$/',$return)){
			D("SmsLog")->updateUse($mobile,'hd_application',$check_code);
			$this->model_member->addMemberLog('hd_application',$this->login_member_info);

            //通知运营人员
            if($type==1){//智能一键报价:咨询编号：{1}，咨询类型：{2}，申请人：{3}，手机号：{4}，面积：{5}，户型：{6}
                $capn = C("CAPITAL_NUMBER");
                $house_type =  $capn[$data["zn_room"]] . "房/" . $capn[$data["zn_hall"]] . "客/" . $capn[$data["zn_kitchen"]] . "厨/" . $capn[$data["zn_toilet"]] . "卫/" . $capn[$data["zn_balcony"]] . "阳台";
                lqSendSms('13560444215,13249131367',array($data["zc_order_no"],'一键报价',$data["zc_name"],$mobile,$acreage,$house_type),166346);
                $this->ajaxReturn(array('status'=>1,'msg'=>$msg.'成功','data' =>'',"url"=>"","note"=>'收录报价信息'),$this->JSONP);
            }elseif($type==2){//设计申请:咨询编号：{1}，咨询类型：{2}，申请人：{3}，手机号：{4}
                lqSendSms('13560444215,13249131367',array($data["zc_order_no"],'设计申请',$data["zc_name"],$mobile),166336);
                $this->ajaxReturn(array('status'=>1,'msg'=>$msg.'成功','data' =>'',"url"=>"","note"=>'收录报价信息'),$this->JSONP);
            }elseif($type==3){//活动:咨询编号：{1}，咨询类型：{2}，申请人：{3}，手机号：{4}，面积：{5}，户型：{6}

                //入支付日志
                $pay_data=array();

                if (!is_weixin()) {
                    $pay_data['zc_pay_type'] = 'APP';//支付方式
                }else{
                    $pay_data['zc_pay_type'] = 'WeChat';//支付方式
                }

                $pay_data['zn_order_id'] = $return;//订单ID
                $pay_data['zc_order_no'] = $data["zc_order_no"];//订单编号
                $pay_data['zn_pay_business'] = 1;//支付业务类型
                $pay_data['zn_member_id'] = $this->login_member_info["id"];//会员ID
                $pay_data['zc_member_account'] = $this->login_member_info["zc_account"];//会员帐号
                $pay_data['zf_order_amount'] = $data["zf_deposit"];//支付金额
                $pay_data['zl_is_apy'] = 0;//是否已支付
                $pay_data['zc_transaction_id'] = '';//支付业务单
                $pay_data["zn_cdate"]=NOW_TIME;
                $pay_data["zn_mdate"]=NOW_TIME;
                $pay_id = $this->model_member->apiInsertPay($pay_data);

                if (!is_weixin()) {
                    $app_data = $this->_app_deposit_pay($return,$pay_id);
                }

                $capn = C("CAPITAL_NUMBER");
                $house_type =  $capn[$data["zn_room"]] . "房/" . $capn[$data["zn_hall"]] . "客/" . $capn[$data["zn_kitchen"]] . "厨/" . $capn[$data["zn_toilet"]] . "卫/" . $capn[$data["zn_balcony"]] . "阳台";
                lqSendSms('13560444215,13249131367',array($data["zc_order_no"],'家装活动',$data["zc_name"],$mobile,$acreage,$house_type),166346);

                $token=$this->model_member->apiGetToken($this->login_member_info["id"]);


                $this->ajaxReturn(array('status'=>1,'msg'=>$msg.'成功','data' =>$app_data,"url"=>U('api/hdorder/deposit-pay?tnid='.$return."&uid=".$this->login_member_info["id"]."&token=".$token["zc_token"]),"note"=>'收录报价信息','iosurl'=>U('api/hdorder/ios-pay?'.http_build_query ($app_data)."&uid=".$this->login_member_info["id"]."&token=".$token["zc_token"])),$this->JSONP);
            }

		}else{
			$this->ajaxReturn(array('status'=>0,'msg'=>$msg.'失败:'.$return,'data' =>'',"url"=>"","note"=>'收录报价信息'),$this->JSONP);
		}
	}

	/*
	 * ios支付接口数据
	 */
    public function ios_pay(){
        $data = I("get.paydata","");

        if($data)$this->ajaxReturn(array('status'=>1,'msg'=>'获取参数成功','data' =>$data,"url"=>"","note"=>'获取支付参数'),$this->JSONP);
        else $this->ajaxReturn(array('status'=>0,'msg'=>'获取参数失败','data' =>$data,"url"=>"","note"=>'获取支付参数'),$this->JSONP);

    }
	/*订金支付*/
	public function deposit_pay(){
		$data_application = M("hd_application")->field("zc_order_no,zn_member_id,zf_deposit,zl_deposit_pay,zn_cdate")->where("zl_deposit_pay=0 and id=" .$this->lqgetid)->find();
		if(!$data_application){
			if(!$data_application){$this->error('支付失败');}//支付失败
		}
		$member_token=$this->model_member->apiGetToken($this->login_member_info["id"]);
		
		$total_fee=$data_application["zf_deposit"]*100;
		//接入微信支付类
        import('Vendor.WxPayPubHelper.WxPayPubHelper');
		//使用jsapi接口
		$jsApi = new \JsApi_pub();
		//=========步骤1：网页授权获取用户openid============
		$openid=session('openid');
		if(!$openid){
            setcookie("referer",$_SERVER['REQUEST_URI'], time()+3600);
            lq_return_openid($_SERVER['REQUEST_URI']);
        }

		$order_id=$this->lqgetid;//订单ID
		$pay_id=$this->model_member->apiGetPay("zc_pay_type='WeChat' and zn_pay_business=1 and zn_order_id=".$order_id,"id");//支付ID
		
		//=========步骤2：使用统一支付接口，获取prepay_id============
		//使用统一支付接口
		$unifiedOrder = new \UnifiedOrder_pub();
		
		//设置统一支付接口参数
		$unifiedOrder->setParameter("openid","$openid");//openid
		$unifiedOrder->setParameter("body","订单 [".$data_application["zc_order_no"]."]  微信支付");//商品描述
		//自定义订单号，此处仅作举例
		$timeStamp = time();
		$out_trade_no = \WxPayConf_pub::APPID."$timeStamp";
		//$out_trade_no = $data_application["zc_order_no"];
		$unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号 
		$unifiedOrder->setParameter("total_fee",$total_fee);//总金额
		$unifiedOrder->setParameter("notify_url",'http://wx.lxjjz.cn/sys-index.php/PayNotify/wechat_wx_deposit_update');//通知地址:异步通知url，商户根据实际开发过程设定
		$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
		$unifiedOrder->setParameter("attach",$order_id . '_' . $pay_id);//订单数据

		$prepay_id = $unifiedOrder->getPrepayId();
		//=========步骤3：使用jsapi调起支付============
		$jsApi->setPrepayId($prepay_id);
		$jsApiParameters = $jsApi->getParameters();
		//wxjsbridge
		$js = "<script language=\"javascript\">
					//调用微信JS api 支付
					function jsApiCall()
					{
						WeixinJSBridge.invoke(
							'getBrandWCPayRequest',".$jsApiParameters.",
							function(res){
								//alert(res.err_code+res.err_desc+res.err_msg);
								if(res.err_msg == \"get_brand_wcpay_request:ok\"){
									location.href=\"".U('api/hdorder/respond-deposit-update?status=1&tnid='.$this->lqgetid."&uid=".$this->login_member_info["id"]."&token=".$member_token["zc_token"])."\";
								}else if(res.err_msg == \"get_brand_wcpay_request:cancel\"){
									alert('取消支付');
									location.href=\"http://wx.lxjjz.cn/wx/views/index.html\";
								}else{//支付失败
									location.href=\"do?g=api&m=hdorder&a=respond-deposit-update&status=0&tnid=".$this->lqgetid."&uid=".$this->login_member_info["id"]."&token=".$member_token["zc_token"]."&err=\"+res.err_code+'-'+res.err_desc+'-'+res.err_msg;
								}
							}
						);
					}
					function callpay()
					{
						if (typeof WeixinJSBridge == \"undefined\"){
							if( document.addEventListener ){
								document.addEventListener('WeixinJSBridgeReady', jsApiCall, true);
							}else if (document.attachEvent){
								document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
								document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
							}
						}else{
							jsApiCall();
						}
					}
		</script>";
		$button = '<div style="text-align:center"><button class="c-btn4" type="button" onclick="callpay()">微信安全支付</button></div>'.$js;
		
		exit('<!DOCTYPE html><head><meta charset="utf-8"><title>支付操作</title>
			  <script type="text/javascript" src="/Public/Static/js/lib/jquery-1.11.1.min.js"></script>
			  <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
			  <meta name="viewport" content="initial-scale=1, maximum-scale=1">
			  <meta name="apple-mobile-web-app-capable" content="yes">
			  <meta name="apple-mobile-web-app-status-bar-style" content="black">
			  <style>body,ul,ol,li,p,h1,h2,h3,h4,h5,h6,form,fieldset,table,td,img,div{margin:0;padding:0;border:0;}body{color:#333;font-size:14px;font-family:"Microsoft YaHei";}ul,ol{list-style-type:none;}a{text-decoration:none;font-size:14px;color:#333;}a:hover{color:#666;}*:focus{outline:none;blr:expression(this.onFocus=this.blur());}.info_ul{width:90%;margin:4rem auto 1rem;}.info_ul li{line-height:40px;width:100%;display:block;float:left;}.info_ul li span{width:80px;float:left;font-weight:800;}.c-btn4{-moz-border-radius:15px;-webkit-border-radius:15px;border-radius:15px;width:90%;background-color:#24a52e;color:#FFF;border:0;line-height:40px;font-size:1rem;}.links{color:#999;text-align:center;margin:20px auto 5px;}.links a{color:#999;font-size:1rem;margin:0px 10px;}</style>
			  </head>
			  <body>
			  <ul class="info_ul"><li><span>订单编号：</span>'.$data_application["zc_order_no"].'</li><li><span>支付订金：</span>'.$data_application["zf_deposit"].'</li><li><span>下单时间：</span>'.lq_cdate($data_application["zn_cdate"],1).'</li></ul>
			  <div style="text-align:center">'.$button.'</div>
			  <div class="links"><a href="http://wx.lxjjz.cn/wx/views/index.html">首页</a>&nbsp;|&nbsp;<a href="http://wx.lxjjz.cn/wx/views/my/index.html">会员中心</a></div>
			  </body></html>');
	}

	//订金:响应支付成功
	public function respond_deposit_update(){
        $status =I("get.status",'','int');//订单类型
		if($status!=''){
			$data_application = M("hd_application")->field("zc_order_no,zn_member_id,zf_deposit,zl_deposit_pay,zn_cdate")->where("id=" .$this->lqgetid)->find();
			if($data_application){
				if($status==1){
					exit('<!DOCTYPE html><head><meta charset="utf-8"><title>支付操作</title><script type="text/javascript"src="/Public/Static/js/lib/jquery-1.11.1.min.js"></script><script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script><meta name="viewport"content="initial-scale=1, maximum-scale=1"><meta name="apple-mobile-web-app-capable"content="yes"><meta name="apple-mobile-web-app-status-bar-style"content="black"><style>body,ul,ol,li,p,h1,h2,h3,h4,h5,h6,form,fieldset,table,td,img,div{margin:0;padding:0;border:0}body{color:#333;font-size:14px;font-family:"Microsoft YaHei"}ul,ol{list-style-type:none}a{text-decoration:none;font-size:14px;color:#333}a:hover{color:#666}*:focus{outline:none;blr:expression(this.onFocus=this.blur())}.info_ul{width:80%;margin:4rem auto 1rem}.info_ul li{line-height:40px;width:100%;display:block;float:left}.success{width:80px;height:80px;-moz-border-radius:45px;-webkit-border-radius:45px;border-radius:45px;background-color:#24a52e;line-height:80px;text-align:center;color:#FFF;margin:0 auto;font-size:1.2rem}.info_ul li div{display:block;width:80px;font-weight:800}.info_ul li span{width:100px;font-weight:800}.links{width:80%;color:#999;text-align:center;margin:20px auto 5px}.links a{-moz-border-radius:15px;-webkit-border-radius:15px;border-radius:15px;width:100%;background-color:#333;color:#FFF;border:0;line-height:30px;font-size:0.9rem;display:block;float:left;margin:8px auto 5px}.links a span{margin-left:10px;color:#999}</style></head><body><ul class="info_ul"><li><div class="success">成功</div></li><li><span>订单编号：</span>'.$data_application["zc_order_no"].'</li><li><span>支付订金：</span>'.$data_application["zf_deposit"].'</li></ul><div class="links"><a href="http://wx.lxjjz.cn/wx/views/my/index.html">返回会员中心<span id="wait">0</span></a><a href="http://wx.lxjjz.cn/wx/views/index.html">返回首页</a></div><script language="javascript"type="text/javascript">function countDown(secs){if(--secs>1){$("#wait").html(secs-1);setTimeout("countDown("+secs+")",1000)}else{location.href="http://wx.lxjjz.cn/wx/views/my/index.html"}}window.onload=function(){var wait=10;$("#wait").html(wait);countDown(wait)}</script></body></html>');
				}else{
					exit('<!DOCTYPE html><head><meta charset="utf-8"><title>支付操作</title><script type="text/javascript"src="/Public/Static/js/lib/jquery-1.11.1.min.js"></script><script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script><meta name="viewport"content="initial-scale=1, maximum-scale=1"><meta name="apple-mobile-web-app-capable"content="yes"><meta name="apple-mobile-web-app-status-bar-style"content="black"><style>body,ul,ol,li,p,h1,h2,h3,h4,h5,h6,form,fieldset,table,td,img,div{margin:0;padding:0;border:0}body{color:#333;font-size:14px;font-family:"Microsoft YaHei"}ul,ol{list-style-type:none}a{text-decoration:none;font-size:14px;color:#333}a:hover{color:#666}*:focus{outline:none;blr:expression(this.onFocus=this.blur())}.info_ul{width:80%;margin:4rem auto 1rem}.info_ul li{line-height:40px;width:100%;display:block;float:left}.fail{width:80px;height:80px;-moz-border-radius:45px;-webkit-border-radius:45px;border-radius:45px;background-color:#d9534f;line-height:80px;text-align:center;color:#FFF;margin:0 auto;font-size:1.2rem}.info_ul li div{display:block;width:80px;font-weight:800}.info_ul li span{width:100px;font-weight:800}.links{width:80%;color:#999;text-align:center;margin:20px auto 5px}.links a{-moz-border-radius:15px;-webkit-border-radius:15px;border-radius:15px;width:100%;background-color:#333;color:#FFF;border:0;line-height:30px;font-size:0.9rem;display:block;float:left;margin:8px auto 5px}.links a span{margin-left:10px;color:#999}</style></head><body><ul class="info_ul"><li><div class="fail">失败</div></li><li><span>订单编号：</span>'.$data_application["zc_order_no"].'</li><li><span>支付订金：</span>'.$data_application["zf_deposit"].'</li></ul><div class="links"><a href="http://wx.lxjjz.cn/wx/views/my/index.html">返回会员中心<span id="wait">0</span></a><a href="http://wx.lxjjz.cn/wx/views/index.html">返回首页</a></div><script language="javascript"type="text/javascript">function countDown(secs){if(--secs>1){$("#wait").html(secs-1);setTimeout("countDown("+secs+")",1000)}else{location.href="http://wx.lxjjz.cn/wx/views/my/index.html"}}window.onload=function(){var wait=10;$("#wait").html(wait);countDown(wait)}</script></body></html>');
				}
			}else{
					exit('<!DOCTYPE html><head><meta charset="utf-8"><title>支付操作</title><script type="text/javascript"src="/Public/Static/js/lib/jquery-1.11.1.min.js"></script><script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script><meta name="viewport"content="initial-scale=1, maximum-scale=1"><meta name="apple-mobile-web-app-capable"content="yes"><meta name="apple-mobile-web-app-status-bar-style"content="black"><style>body,ul,ol,li,p,h1,h2,h3,h4,h5,h6,form,fieldset,table,td,img,div{margin:0;padding:0;border:0}body{color:#333;font-size:14px;font-family:"Microsoft YaHei"}ul,ol{list-style-type:none}a{text-decoration:none;font-size:14px;color:#333}a:hover{color:#666}*:focus{outline:none;blr:expression(this.onFocus=this.blur())}.info_ul{width:80%;margin:4rem auto 1rem}.info_ul li{line-height:40px;width:100%;display:block;float:left}.fail{width:80px;height:80px;-moz-border-radius:45px;-webkit-border-radius:45px;border-radius:45px;background-color:#d9534f;line-height:80px;text-align:center;color:#FFF;margin:0 auto;font-size:1.2rem}.info_ul li div{display:block;width:80px;font-weight:800}.info_ul li span{width:100px;font-weight:800}.links{width:80%;color:#999;text-align:center;margin:20px auto 5px}.links a{-moz-border-radius:15px;-webkit-border-radius:15px;border-radius:15px;width:100%;background-color:#333;color:#FFF;border:0;line-height:30px;font-size:0.9rem;display:block;float:left;margin:8px auto 5px}.links a span{margin-left:10px;color:#999}</style></head><body><ul class="info_ul"><li><div class="fail">出错</div></li></ul><div class="links"><a href="http://wx.lxjjz.cn/wx/views/index.html">返回首页</a></div><script language="javascript"type="text/javascript">function countDown(secs){if(--secs>1){$("#wait").html(secs-1);setTimeout("countDown("+secs+")",1000)}else{location.href="http://wx.lxjjz.cn/wx/views/index.html"}}window.onload=function(){var wait=10;$("#wait").html(wait);countDown(wait)}</script></body></html>');
			}
		}
	}		
	

	//获得装修进度
	public function order_detail(){
        $type =I("get.type",'','int');//订单类型

        if(!$this->lqgetid || !$type) $this->ajaxReturn(array('status'=>1,'msg'=>'数据返回失败','data' =>array(),"url"=>"","note"=>'获得订单详情'),$this->JSONP);

        if($type == 1) {
            $data = $this->model_hdorder->getOrderDetailById($this->lqgetid);
        }
        elseif($type == 2) { $data = $this->model_hdorder->getApplicationDetailById($this->lqgetid);}


        if(!$data){
            $this->ajaxReturn(array('status'=>0,'msg'=>'返回失败','data' =>array(),"url"=>"","note"=>'获得订单详情'),$this->JSONP);
        }
		$this->ajaxReturn(array('status'=>1,'msg'=>'数据返回成功','data' =>$data,"url"=>"","note"=>'获得装修进度'),$this->JSONP);
	}

    /*
     * 我的订单
     * */
	public function order_list()
    {
        $where = array(
            "zn_member_id" => $this->login_member_info["id"]
            //"zl_status" => array("NOT IN", array(3,4)),
        );
        $order_field = array("id", "zc_order_no" => "order_no", "zc_name" => "name", "zc_mobile" => "mobile", "zc_area" => "area", "zc_address" => "address", "zn_cdate" => "order_date", "zc_effect_image" => "effect_image", "zn_acreage" => "acreage", "zn_hall" => "hall", "zc_decoration_else" => "decoration_else", "zc_area" => "area", "zc_address" => "address", "zc_decoration_type" => "decoration_type", "zl_status" => "status", "zl_progress" => "progress");
        $order_config = array(
            'field' => $order_field,
            'where' => $where,
            'order' => 'zn_cdate DESC',
        );
        $order = $this->D_ORDER->lqList(0, 20, $order_config);

        $no_use_array = array("room", "short_address", "progress");
        $order = clean_no_use($order, $no_use_array);

        if ($order["odrer_no_arr"]) {
            $where_application = array(
                "zn_member_id" => $this->login_member_info["id"],
                "zl_status" => array("NOT IN", array(6,10)),
                "zc_order_no" => array("NOT IN", $order["odrer_no_arr"])
            );
            unset($order["odrer_no_arr"]);
            $list = $order;
        } else {
            $where_application = array(
                "zn_member_id" => $this->login_member_info["id"],
                "zl_status" => array("NOT IN", array(6,10))
            );
        }
        $application_field = array("id", "zc_order_no" => "order_no", "zc_name" => "name", "zc_mobile" => "mobile", "zn_cdate" => "order_date", "zn_designer_id" => "designer_id", "zn_acreage" => "acreage", "zn_hall" => "hall", "zc_decoration_else" => "decoration_else", "zc_area" => "area", "zc_address" => "address", "zl_decoration_type" => "decoration_type", "zl_status" => "status");
        $application_config = array(
            'field' => $application_field,
            'where' => $where_application,
            'order' => 'zn_cdate DESC',
        );


        $application = $this->D_ORDER->lqListApplication(0, 20, $application_config);

        $no_use_array = array("room", "designer_name", "designer_id");
        $application = clean_no_use($application, $no_use_array);


        if ($list) {
            if ($application) $list = array_merge($order, $application);
        } else {
             $list = $application;
        }
        $list = multi_array_sort($list,"order_date");
        $note = '我的订单';
        if($list) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $list, "url" => "", "note" => $note), $this->JSONP);
        }else{
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        }
    }

    //////用户取消咨询单
    public function cansel_application()
    {
        if(!$this->lqgetid) $this->ajaxReturn(array('status'=>0,'msg'=>'取消失败','data' =>array(),"url"=>"","note"=>'用户取消咨询单'),$this->JSONP);

        $is_pay = M("hd_application")->where("id=" . $this->lqgetid)->getField('zl_deposit_pay');
        if($is_pay) {
            $this->ajaxReturn(array('status'=>0,'msg'=>'已支付成功，无法取消','data' =>array(),"url"=>"","note"=>'用户取消咨询单'),$this->JSONP);
        }

        if($this->model_hdorder->cansel_application($this->lqgetid)) {
            $this->model_member->addMemberLog('cansel_application',$this->login_member_info);
        }
        $this->ajaxReturn(array('status' => 0, 'msg' => '取消咨询订单成功', 'data' => array(), "url" => "", "note" => "用户取消咨询单"), $this->JSONP);

    }

    /////app支付参数
    private function _app_deposit_pay($order_id = 0,$pay_id=0)
    {
        $order_id = intval($order_id);
        $pay_id = intval($pay_id);
        if(!$order_id || !$pay_id) return;
        $data_application = M("hd_application")->field("zc_order_no,zn_member_id,zf_deposit,zl_deposit_pay")->where("zl_deposit_pay=0 and id=".$order_id)->find();
        if (!$data_application) {
            return;
        }

        $total_fee = $data_application["zf_deposit"] * 100;
        //接入微信支付类
        import('Vendor.WxPayPubHelper.WxAppPay');
        //使用apppay接口wechat_native_deposit_update
        $notify_url = 'http://wx.lxjjz.cn/sys-index.php/PayNotify/wechat_native_deposit_update';
        $wechatAppPay = new \wechatAppPay($notify_url);

        //设置统一支付接口参数
        $timeStamp = time();
        $params = array();
        $params['body'] = 'APP-在线支付';      //必填项 商品描述
        $params['out_trade_no'] = \WxPayConf_pub::APP_APPID . "$timeStamp";  //必填项 自定义的订单号
        $params['total_fee'] = $total_fee;       //必填项 订单金额 单位为分所以要*100
        $params['trade_type'] = 'APP';              //必填项 交易类型固定写  APP
        $params['timestamp'] = $timeStamp;              //必填项 交易类型固定写  APP
        $params['attach'] = $order_id . "_" .$pay_id;//非必填项 根据自己情况定的值 这个可有好多个可以参看开发文档中的参数

        $wx_result = $wechatAppPay->unifiedOrder($params);

        $sign_array = array();
        $sign_array['appid'] = $wx_result['appid'];    //注意 $sign_array['appid'] 里的参数名必须是appid
        $sign_array['partnerid'] = $wx_result['mch_id'];   //注意 $sign_array['partnerid'] 里的参数名必须是partnerid
        $sign_array['prepayid'] = $wx_result['prepay_id'];//注意 $sign_array['prepayid'] 里的参数名必须是prepayid
        $sign_array['package'] = 'Sign=WXPay';           //注意 $sign_array['package'] 里的参数名必须是package
        $sign_array['noncestr'] = $wx_result['nonce_str'];//注意 $sign_array['noncestr'] 里的参数名必须是noncestr
        $sign_array['timestamp'] = $timeStamp;//注意 $sign_array['timestamp'] 里的参数名必须是timestamp


        $sign_two = $wechatAppPay->MakeSign($sign_array);//调用wechatAppPay类里的MakeSign()函数生成sign
        $wx_result['sign'] = $sign_two;
        $wx_result['timeStamp'] = $timeStamp;

        return $wx_result;
    }
}