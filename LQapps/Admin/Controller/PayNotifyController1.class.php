<?php
/*
用户登陆 页面操作 

*/
namespace Admin\Controller;

use LQPublic\Controller\Base;
use Member\Api\MemberApi as MemberApi;

class PayNotifyController extends Base
{
    public $model_member;

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->model_member = new MemberApi;//实例化会员
    }

    //首页
    public function index(){ die("..."); }


    //订金:接收异步通知支付成功
    public function wechat_wx_deposit_update()
    {
        //接入微信支付类
        import('Vendor.WxPayPubHelper.WxPayPubHelper');
        
        //使用通用通知接口
        $notify = new \Notify_pub();
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];

        $notify->saveData($xml);
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if ($notify->checkSign() == FALSE) {
            $notify->setReturnParameter("return_code", "FAIL");//返回状态码
            $notify->setReturnParameter("return_msg", "签名失败");//返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();

        echo $returnXml;
        if ($notify->checkSign() == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {//通信出错

            } elseif ($notify->data["result_code"] == "FAIL") {//业务出错

            } else {//支付成功

                $attach = $notify->data["attach"];
                $transaction_id = $notify->data["transaction_id"];//微信业务单
                $attach_array = explode('_', $attach);
                if ($attach_array) {
                    //入支付日志
                    $pay_data = array();
                    $pay_data['zl_is_apy'] = 1;//是否已支付
                    $pay_data['zc_transaction_id'] = $transaction_id;//支付业务单
                    $pay_data["zn_mdate"] = NOW_TIME;
                    $this->model_member->apiUpdatePay($pay_data, "id=" . intval($attach_array[1]));
                    $notify->replyNotify();
                }

            }
        }

    }

    //订金:接收异步通知支付成功
    public function wechat_native_deposit_update()
    {
        lq_test('dfdfd');

        import('Vendor.WxPayPubHelper.WxAppPay');

        $notify_url = '';
        $wechatAppPay = new \wechatAppPay($notify_url);

        $data = $wechatAppPay->getNotifyData();

        $w_sign = array();
        foreach ($data as $k => $v) {
            $w_sign[$k] = $v;
        }

        $notify = $this->wechatAppPay->MakeSign($w_sign);//生成验
lq_test($notify);
        if ($notify->checkSign() == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {//通信出错

            } elseif ($notify->data["result_code"] == "FAIL") {//业务出错

            } else {//支付成功

                $attach = $notify->data["attach"];
                $transaction_id = $notify->data["transaction_id"];//微信业务单
                $attach_array = explode('_', $attach);
                if ($attach_array) {
                    //入支付日志
                    $pay_data = array();
                    $pay_data['zl_is_apy'] = 1;//是否已支付
                    $pay_data['zc_transaction_id'] = $transaction_id;//支付业务单
                    $pay_data["zn_mdate"] = NOW_TIME;
                    $this->model_member->apiUpdatePay($pay_data, "id=" . intval($attach_array[1]));

                    $wechatAppPay->replyNotify();
                }

            }
        }

    }

}

?>