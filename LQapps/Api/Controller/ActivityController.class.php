<?php
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
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

//首页
class ActivityController extends PublicController
{
    protected $D_ACTIVITY;

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->D_ACTIVITY = D("Api/Activity");//活动
        self::apiCheckToken(0);//用户认证
    }

    //微信授权 获得openid
    public function wx_return_openid()
    {
        $referer = 'http://wx.lxjjz.cn/do?g=api&m=activity&a=index&tnid=' . session('activity_tnid');
        cookie("referer", $referer);
        lq_return_openid(U('api/activity/wx-return-openid?'));
    }

    //静态点击跳转
    public function index()
    {
        $data = $this->D_ACTIVITY->getActivityById($this->lqgetid);

        if((time() < $data['zd_start_time']) || (time() > $data['zd_end_time'])) {

            die("<script>location.href='http://wx.lxjjz.cn/wx/views/activity/ranking.html'</script>");

        }

        if (!session('activity_tnid')) session('activity_tnid', $this->lqgetid);
        if (!$data || !$data["url"]) go_error_page();
        if (session('openid')) {
            $member_id = $this->model_member->apiGetField("zc_openid='" . session('openid') . "'", "id");
            lq_header("Location:" . $data["url"] . "?tnid=" . session('activity_tnid') . "&referer=" . $member_id);
        } else {
            lq_return_openid(U('api/activity/wx-return-openid'));
        }

    }

    //活动详情面
    public function detail()
    {
        $new_reg_id = I('get.new_reg_id', '0', 'int');//会员注册成功回调会员ID
        $referer = $old_referer = I('get.referer', '0', 'int');//推荐人ID
        $type = I('get.lqtype', '1', 'int');//分享方式：1朋友圈,2朋友,3APP
        $data = $this->D_ACTIVITY->getActivityById($this->lqgetid);

        if (session('openid')) {
            $openid_mid  = $this->model_member->apiGetField("zc_openid='" . session('openid') . "'", "id");
			//if($openid_mid) $referer = $openid_mid;
        }
		//记录入口数据
        if ($referer) {
			$register_share = array('type' => $type, 'referer' => $referer);
			session("register_share",$register_share);
		}
        $link = $data["url"] . '?tnid=' . $data["id"] . "&lqtype=$type&referer=$referer";
        if ($data) {
				if($openid_mid){//会员
					$data["button_title"] = '我要发起';
					$data["button_url"] = '###';
				}else{//不是会员
					$data["button_title"] = '助他拿奖';
					$data["button_url"] = 'http://wx.lxjjz.cn/do?g=api&m=member&a=wx-return-openid';
				}

            //微信的JSSDK
            if (is_weixin()&&$referer) {
                $wx_share_config = array("url" => cookie("referer"), "title" => $data["title"], "link" => $link, "imgUrl" => $data["image"], "desc" => lq_kill_html($data["content"], 30));
                $data["wx_jssdk"] = lq_get_jssdk(C("WECHAT"), $wx_share_config);
            }
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => '活动详情页'), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => '活动详情页'), $this->JSONP);
        }
    }

    ////记录入口数据设置
    public function set_register_share(){
        $referer = I('get.referer', '0', 'int');//推荐人ID
        $type = I('get.lqtype', '1', 'int');//分享方式：1朋友圈,2朋友,3APP

        $register_share = array('type' => $type, 'referer' => $referer);
        session("register_share",$register_share);

        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => array(), "url" => "", "note" => '记录入口数据设置'), $this->JSONP);
    }
    //活动详情面展示
    public function show()
    {
        $member_id = 0;
        if (session('openid')) {
            $member_id = $this->model_member->apiGetField("zc_openid='" . session('openid') . "'", "id");
        } else {
            cookie("referer", 'http://wx.lxjjz.cn/do?g=api&m=activity&a=show&tnid=' . $this->lqgetid);
            lq_header("Location:" . U('api/member/wx-return-openid'));
        }
        $data = $this->D_ACTIVITY->getActivityById($this->lqgetid);
        if (!$data) go_error_page();
        $type = I('get.lqtype', '1', 'int');//分享方式：1朋友圈,2朋友,3APP
        $referer = I('get.referer', $member_id, 'int');//推荐人ID
        if ($type && $referer) session("register_share", array('type' => $type, 'referer' => $referer));//记录入口数据
        $link = 'http://wx.lxjjz.cn/do?g=api&m=activity&a=show&tnid=' . $data["id"] . "&lqtype=$type&referer=$referer";

        //微信的JSSDK
        if (is_weixin()) {
            $wx_share_config = array("url" => 'http://wx.lxjjz.cn/do?g=api&m=activity&a=show&tnid=' . $data["id"], "title" => $data["title"], "link" => $link, "imgUrl" => $data["image"], "desc" => lq_kill_html($data["content"], 30));
            $data["wx_jssdk"] = lq_get_jssdk(C("WECHAT"), $wx_share_config);
        }
        exit('<!DOCTYPE html><head><meta charset="utf-8"><title>' . $data["title"] . '</title>
						<script type="text/javascript" src="/Public/Static/js/lib/jquery-1.11.1.min.js"></script>
						<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js" type="text/javascript"></script>
						<meta name="viewport" content="initial-scale=1, maximum-scale=1"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black"><style>body,ul,ol,li,p,h1,h2,h3,h4,h5,h6,form,fieldset,table,td,img,div{margin:0;padding:0;border:0;}body{color:#333; font-size:14px;font-family:"Microsoft YaHei";}ul,ol{list-style-type:none;}.msg{ margin:1.2rem auto 0; width:100%; font-size:1.5rem; color:#F00;}</style></head><body>
						<img src="http://wx.lxjjz.cn/uploadfiles/images/20170421/58f9c57c644c1.jpg" alt="' . $data["title"] . '" width="100%">
						</body></html>
						<script type="text/javascript">' . $data["wx_jssdk"] . '</script>');

    }


    /////活动排名接口
    public function ranking_list()
    {
        $list = $this->D_ACTIVITY->getRankingList();

        if ($list) {
            $data['list'] = $list;
            $data['is_login'] = 0;
            $data['my_info'] = array();
            if ($this->login_member_info['id']) {
                $data['is_login'] = 1;
                $member_sort_info =  $this->D_ACTIVITY->getUserSort($this->login_member_info['id']);

                $my_info = array(
                    "headimg" => $this->login_member_info['zc_headimg'],
                    "account" => $this->login_member_info["zc_nickname"],
                    "total" => $member_sort_info['total'],
                    "rank" => $member_sort_info['rank']
                );


                $data['my_info'] = $my_info;
            }
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => '活动排名页'), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => array(), "url" => "", "note" => '活动排名页'), $this->JSONP);
        }
    }

}

?>