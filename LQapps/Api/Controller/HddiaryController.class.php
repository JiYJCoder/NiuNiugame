<?php
namespace Api\Controller;

use Think\Controller;

defined('in_lqweb') or exit('Access Invalid!');

class HddiaryController extends PublicController
{

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->D_HDDIARY = D("Api/Hddiary");

    }

//日记列表
    public function index()
    {
        self::apiCheckToken(0);//用户认证

        $pageno = I("get.p", '1', 'int');//页码

        $sqlwhere_parameter = array(
            "zl_is_index" => 1,
            "zl_visible" => 1
        );

        $page_config = array(
            'field' => "`id`,`zc_headimg` as headimg ,`zc_nickname` as nickname,`zc_image` as image,`zc_title` as title,`zn_area` as area,`zn_page_view` as page_view,`zn_agrees` as agrees,`zn_household` as household,`zc_style` as style",
            'where' => $sqlwhere_parameter,
            'order' => 'zn_cdate DESC',
        );
        $count = $this->D_HDDIARY->lqCount($sqlwhere_parameter);
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")["diary_list"]);//载入分页类
        //分页尽头
        $note = '0';
        if ($pageno >= $page->totalPages) {
            $note = '0';
        } else {
            if ($count == (C("API_PAGESIZE")["works_list"] * $pageno)) {
                $note = '0';
            } else {
                $note = '1';
            }
        }
        $list = $this->D_HDDIARY->lqList($page->firstRow, $page->listRows, $page_config);

        if (!$list) $list = array();
        else {
            foreach ($list as $lnKey => $laValue) {
                $list[$lnKey]["is_agress"] = $this->model_member->apiTestLove($laValue["id"], 1, $this->login_member_info) ? 1 : 0;
            }
        }

        //会员信息
        if ($this->login_member_info['zc_headimg']) {
            if (substr($this->login_member_info['zc_headimg'], 0, 4) == 'http') {
                $data['member_headimg'] = $this->login_member_info['zc_headimg'];
            } else {
                $img = $this->login_member_info['zc_headimg_thumb'] ? $this->login_member_info['zc_headimg_thumb'] : $this->login_member_info['zc_headimg'];

                $data['member_headimg'] = API_DOMAIN . $img;
            }
        } else {
            $data['member_headimg'] = NO_HEADIMG;
        }

        $data["banner"] = D("Api/AdPosition")->getAdPositionById(2)["list"][0]["image"];
        $data["diary"] = $list;

        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => $note), $this->JSONP);
    }

    /*
     * 日记详情
    */
    public function diary_detail()
    {
        self::apiCheckToken(0);//用户认证
        $note = '日记详情';
        if (!$this->lqgetid) $this->ajaxReturn(array('status' => 1, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        $data = $this->D_HDDIARY->getInfoById($this->lqgetid);

        if ($data) {
            //微信的JSSDK
            if (is_weixin()) {
                $wx_share_config = array("url" => 'http://wx.lxjjz.cn/wx/views/diary/details.html?tnid=' . $data["id"], "title" => $data["title"], "link" => 'http://wx.lxjjz.cn/wx/views/diary/details.html?tnid=' . $data["id"], "imgUrl" => $data["image"], "desc" => $data["area"] . " - " . $data["household"] . " - " . $data["style"]);
                $data["wx_jssdk"] = lq_get_jssdk(C("WECHAT"), $wx_share_config);
            }
            $data["detail"]["is_agress"] = $this->model_member->apiTestLove($this->lqgetid, 1, $this->login_member_info) ? 1 : 0;
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => $note), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        }
    }

}