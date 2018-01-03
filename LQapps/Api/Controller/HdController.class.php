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
use LQLibs\Util\Category as Category;//树状分类

defined('in_lqweb') or exit('Access Invalid!');

class HdController extends PublicController
{
    protected $D_DESIGNER, $D_ART, $D_PRO, $D_HDDIARY, $D_LOANAPPLY,$D_SMS;

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();



        $this->D_SMS = D("Api/SmsLog");//接口短信实例化
        //免死金牌
        $action_no_login_array = array('get_attribute', 'product-view-count', 'designer-view-count', 'designer-agrees-count');
        if (in_array(ACTION_NAME, $action_no_login_array)) {

        } else {
            self::apiCheckToken();//用户认证
        }

    }

    //首页数据包
    public function index()
    {
        $this->ajaxReturn(array('status' => 0, 'msg' => '当前端口暂时关闭', 'data' => array(), "url" => "", "note" => ""), $this->JSONP);
    }

    //获得设计师说属性
    public function get_attribute()
    {
        $type = I("get.type", '');//类型
        $data = $this->D_DESIGNER->getAttributeCache();
        if ($type) {
            $data = $data[$type];
        }
        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => "获取美家风格"), $this->JSONP);
    }

    //设计师说-作品列表-数据输出
    public function works_list()
    {
        $style = I("get.style", '', 'lqSafeExplode');//风格
        $household = I("get.household", '', 'lqSafeExplode');//户型
        $area = I("get.area", '', 'lqSafeExplode');//面积
        $pageno = I("get.p", '1', 'int');//页码

        //作品列表
        $sqlwhere_parameter = " zl_visible=1 ";//sql条件
        if ($style) {//风格
            if (is_numeric($style)) {
                $sqlwhere_parameter .= " and zn_style =" . $style;
            } else {
                $sqlwhere_parameter .= " and zn_style in($style) ";
            }
        }
        if ($household) {//户型
            if (is_numeric($household)) {
                $sqlwhere_parameter .= " and zn_household =" . $household;
            } else {
                $sqlwhere_parameter .= " and zn_household in($household) ";
            }
        }
        if ($area) {//面积
            if (is_numeric($area)) {
                $sqlwhere_parameter .= " and zn_area =" . $area;
            } else {
                $sqlwhere_parameter .= " and zn_area in($area) ";
            }
        }

        $page_config = array(
            'field' => "`id`,`zn_style`,`zn_household`,`zn_area`,`zn_designer_id` as designer_id,`zn_member_id`,`zc_designer_nickname` as designer_nickname,`zc_caption` as title,`zc_thumb` as image,`zn_thumb_width` as thumb_width,`zn_thumb_height` as thumb_height,`zn_work_release` as time,`zn_clicks` as clicks,`zn_agrees` as agrees,`zc_introduction` as content",
            'where' => $sqlwhere_parameter,
            'order' => 'zn_sort ASC,zn_work_release DESC',
        );
        $count = $this->D_DESIGNER->lqWorksCount($sqlwhere_parameter);
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")["works_list"]);//载入分页类
        //分页尽头
        if ($pageno >= $page->totalPages) {
            $note = '0';
        } else {
            if ($count == (C("API_PAGESIZE")["works_list"] * $pageno)) {
                $note = '0';
            } else {
                $note = '1';
            }
        }
        $list = $this->D_DESIGNER->lqWorksList($page->firstRow, $page->listRows, $page_config);
        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $list, "url" => "", "note" => $note), $this->JSONP);
    }

    //作品详情-数据输出
    public function works_show()
    {
        $data = $this->D_DESIGNER->getWorksById($this->lqgetid);
        if ($data) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => '作品详情'), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => '作品详情'), $this->JSONP);
        }
    }

    //设计师筛选条件
    public function designer_condition()
    {
        $data = $style = array();
        foreach (F('hd_attribute_1', '', COMMON_ARRAY) as $k => $v) $style[] = array("id" => $k, "title" => $v);

        $data["style"] = $style;
        $experience = $city = array();
        $experience[] = array("id" => 1, "title" => "1-3年设计经验");
        $experience[] = array("id" => 2, "title" => "3-5年设计经验");
        $experience[] = array("id" => 3, "title" => "5年以上设计经验");
        $city[] = array("id" => 440100, "title" => "广州");
        $city[] = array("id" => 440300, "title" => "深圳");
        $city[] = array("id" => 310000, "title" => "上海");
        $city[] = array("id" => 110000, "title" => "北京");
        $data["experience"] = $experience;
        $data["city"] = $city;
        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => '设计师筛选条件'), $this->JSONP);
    }

    //设计师列表-数据输出
    public function designer_list()
    {
        $style = I("get.style", '', 'lqSafeExplode');//风格
        $city = I("get.city", '', 'lqSafeExplode');//城市
        $experience = I("get.experience", '0', 'int');//设计经验
        $pageno = I("get.p", '1', 'int');//页码

        //作品列表
        $sqlwhere_parameter = " zl_visible=1 ";//sql条件
        if ($style) {//风格
            if (is_numeric($style)) {
                $designer_array = M()->query("SELECT DISTINCT zn_designer_id FROM __PREFIX__designer_works WHERE zn_style=$style");
            } else {
                $designer_array = M()->query("SELECT DISTINCT zn_designer_id FROM __PREFIX__designer_works WHERE zn_style in($style)");
            }
            $designer_id = '0';
            if ($designer_array) {
                foreach ($designer_array as $k => $v) {
                    $designer_id .= ',' . $v["zn_designer_id"];
                }
            }
            if ($designer_id != '0') {
                $sqlwhere_parameter .= " and id in($designer_id) ";
            }
        }
        if ($city) {//城市
            if (is_numeric($city)) {
                $member_array = M()->query("SELECT DISTINCT id FROM __PREFIX__member WHERE zn_city=$city");
            } else {
                $member_array = M()->query("SELECT DISTINCT id FROM __PREFIX__member WHERE zn_city in($city)");
            }
            $member_id = '0';
            if ($member_array) {
                foreach ($member_array as $k => $v) {
                    $member_id .= ',' . $v["id"];
                }
            }
            if ($member_id != '0') {
                $sqlwhere_parameter .= " and zn_member_id in($member_id) ";
            }
        }
        if ($experience) {//设计经验
            $lnday = date("Y");//当天日期
            if ($experience == 1) {
                $sqlwhere_parameter .= " and zn_join_year >" . ($lnday - 3) . " and zn_join_year<=" . $lnday;
            } else if ($experience == 2) {
                $sqlwhere_parameter .= " and zn_join_year >" . ($lnday - 5) . " and zn_join_year<=" . ($lnday - 3);
            } else if ($experience == 3) {
                $sqlwhere_parameter .= " and zn_join_year <=" . ($lnday - 5);
            }
        }
        $page_config = array(
            'field' => "id,zc_nickname as nickname,zn_member_id,zc_resume",
            'where' => $sqlwhere_parameter,
            'order' => 'zl_good_index ASC,zl_level ASC',
        );
        $count = $this->D_DESIGNER->lqCount($sqlwhere_parameter);
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")["designer_list"]);//载入分页类
        //分页尽头
        if ($pageno >= $page->totalPages) {
            $note = '0';
        } else {
            if ($count == (C("API_PAGESIZE")["designer_list"] * $pageno)) {
                $note = '0';
            } else {
                $note = '1';
            }
        }
        $list = $this->D_DESIGNER->lqList($page->firstRow, $page->listRows, $page_config);
        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $list, "url" => "", "note" => $note), $this->JSONP);
    }

    //设计师-数据输出
    public function designer()
    {
        $data = $this->D_DESIGNER->getDesignerById($this->lqgetid);
        if ($data) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => '设计师详情'), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => '设计师详情'), $this->JSONP);
        }
    }

    //建材甄选首页-数据输出
    public function zxgl()
    {
        $data = array();
        $data["good_article"] = $this->D_ART->getGoodArticle(5);
        $data["cat_index_article"] = $this->D_ART->getCatIndexArticle(5, 'zxgl-list');
        if ($data) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => '建材甄选首页'), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => '建材甄选首页'), $this->JSONP);
        }
    }

    //装修攻略分类-数据输出
    public function zxgl_cat()
    {
        $list = M("article_cat")->field("id,zc_caption as title")->where("zl_visible=1 and zn_fid=5")->order("")->select();
        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $list, "url" => "", "note" => '装修攻略分类'), $this->JSONP);
    }

    //文章列表-数据输出
    public function article_list()
    {

        //self::apiCheckToken(0);//用户认证
       // $catid = I("get.catid", '0', 'int');//分类
        $pageno = I("get.p", '1', 'int');//页码

       // if ($catid == 0) $this->ajaxReturn(array('status' => 0, 'msg' => '访问参数出错！', 'data' => array(), "url" => "", "note" => ""), $this->JSONP);
        $cat = $this->D_ART->getCatById($catid);
       // if (!$cat) $this->ajaxReturn(array('status' => 0, 'msg' => '没有数据！', 'data' => array(), "url" => "", "note" => ""), $this->JSONP); //无记录

        $sqlwhere_parameter = " zl_visible=1 ";//sql条件
        //$tree = new Category('article_cat', array('id', 'zn_fid', 'zc_caption'));
        //$child_ids = $tree->get_child($catid, 10, 'zl_visible=1');


        $page_config = array(
            'field' => "`id`,`zn_cat_id` as cat_id ,`zc_image` as image,`zc_title` as title,`zd_send_time` as send_time,`zc_summary` as summary,`zn_page_view` as page_view,`zn_share` as share,`zc_author`,`zc_source`,`zn_agrees` as agrees",
            'where' => $sqlwhere_parameter,
            'order' => 'zn_sort ASC,zd_send_time DESC',
        );
        $count = $this->D_ART->lqCount($sqlwhere_parameter);
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")["article_list"]);//载入分页类
        //分页尽头
        if ($pageno >= $page->totalPages) {
            $note = '0';
        } else {
            if ($count == (C("API_PAGESIZE")["article_list"] * $pageno)) {
                $note = '0';
            } else {
                $note = '1';
            }
        }
        $list = $this->D_ART->lqList($page->firstRow, $page->listRows, $page_config);
        foreach ($list as $lnKey => $laValue) {
            $list[$lnKey]["is_agress"] = $this->model_member->apiTestLove($laValue["id"], 3, $this->login_member_info) ? 1 : 0;
        }
        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $list, "url" => "", "note" => $note), $this->JSONP);
    }

    //文章详情-数据输出
    public function article_show()
    {
        $data = $this->D_ART->getArticleById($this->lqgetid);
        if ($data) {
            //微信的JSSDK
            if (is_weixin()) {
                $wx_share_config = array("url" => cookie('referer'), "title" => $data["title"], "link" => 'http://wx.lxjjz.cn/wx/views/strategy/details.html?tnid=' . $data["id"], "imgUrl" => $data["image"], "desc" => lq_kill_html($data["content"], 30));
                $data["wx_jssdk"] = lq_get_jssdk(C("WECHAT"), $wx_share_config);
            }

            $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => '文章详情'), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => '文章详情'), $this->JSONP);
        }
    }

    public function article_display()
    {
        $data = $this->D_ART->getArticleById($this->lqgetid);
        $this->assign("data", $data);
        $this->display("Display/article");
    }

    //建材甄选列表-数据输出
    public function product_list()
    {
        $data = $this->D_PRO->brandProduct();
        if ($data) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => '建材甄选列表'), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => '建材甄选列表'), $this->JSONP);
        }
    }

    //建材甄选详情-数据输出
    public function product_show()
    {
        $data = $this->D_PRO->getProductById($this->lqgetid);
        if ($data) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => '建材详情'), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => '建材详情'), $this->JSONP);
        }
    }

    //装修贷申请
    public function loan_apply()
    {
        self::apiCheckToken(0);//用户认证
        $mobile = I("get.mobile", '');//装修贷申请人手机号码
        $bank_id = I("get.bank_id", "1", "int");
        $name = I("get.name", '');//装修贷申请人
        $area = I("get.area", '');//装修贷申请人所在地
        $name_len = lqAbslength($name);
        $area_len = lqAbslength($area);

        $check_code=I("get.check_code",'');//手机验证码
        if(!$this->D_SMS->isEffective($mobile,'loan_apply',$check_code)){
            $this->ajaxReturn(array('status'=>0,'msg'=>'验证码无效！','data' =>'',"url"=>"","note"=>'装修贷申请'),$this->JSONP);
        }

        if (!isName($name)) $this->ajaxReturn(array('status' => 0, 'msg' => '申请失败,请正确输入-装修贷申请人', 'data' => '', "url" => "", "note" => '装修贷申请'), $this->JSONP);
        if ($name_len < 1 || $name_len > 10) $this->ajaxReturn(array('status' => 0, 'msg' => '申请失败,请正确输入-装修贷申请人', 'data' => '', "url" => "", "note" => '装修贷申请'), $this->JSONP);
        if (!isMobile($mobile)) $this->ajaxReturn(array('status' => 0, 'msg' => '申请失败,请正确输入-装修贷申请人手机号码！', 'data' => '', "url" => "", "note" => '装修贷申请'), $this->JSONP);
        if ($area_len < 1 || $area_len > 22) $this->ajaxReturn(array('status' => 0, 'msg' => '申请失败,请正确输入-装修贷申请人所在地', 'data' => '', "url" => "", "note" => '装修贷申请'), $this->JSONP);

        $data = array();
        /** 允许申请一次 **/
        if ($this->login_member_info) {
            $where = "zn_member_id=" . $this->login_member_info['id'];

            $data["zn_member_id"] = $this->login_member_info["id"];
            $data["zc_member_account"] = $this->login_member_info["zc_account"];

            $detailArr['zl_role'] = 1;
            $detailArr['zn_operate_id'] = $this->login_member_info["id"];
            $detailArr['zc_operate_account'] = $this->login_member_info["zc_account"];
        } else {
            $where = "zc_mobile='" . $mobile . "'";

            $detailArr['zl_role'] = 0;
            $detailArr['zn_operate_id'] = 0;
            $detailArr['zc_operate_account'] = '游客';
        }

        $is_apply = $this->D_LOANAPPLY->isApply($where);
        if ($is_apply) $this->ajaxReturn(array('status' => 0, 'msg' => '申请失败,您已提交过申请，请等待审核结果', 'data' => '', "url" => "", "note" => '装修贷申请'), $this->JSONP);

        $bank_name = M("bank")->where("id=" . $bank_id)->getField("zc_bank_name");

        $data["zc_order_no"] = 'LA.' . NOW_TIME . rand(100, 999);
        $data["zc_name"] = $name;
        $data["zc_name"] = $name;
        $data["zn_bank_id"] = $bank_id;
        $data["zc_bank_name"] = $bank_name;
        $data["zc_mobile"] = $mobile;
        $data["zc_area"] = $area;
        $data["zl_status"] = 1;
       // $data["zc_status_log"] = $log_arr;
        $data["zn_cdate"] = NOW_TIME;
        $data["zn_mdate"] = NOW_TIME;

        if ($loan_id = M("loan_apply")->add($data)) {
            ////插入贷款进度明细
            $this->D_LOANAPPLY->addDetail($loan_id,$detailArr);
            $this->D_SMS->updateUse($mobile, 'loan_apply', $check_code);//改变短信状态

            //通知运营人员
            lqSendSms('13560444215,13249131367',array($data["zc_order_no"],'装修贷申请',$bank_name,$data["zc_name"],$mobile),166347);

            $this->ajaxReturn(array('status' => 1, 'msg' => '申请成功', 'data' => '', "url" => "", "note" => '装修贷申请'), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '申请失败', 'data' => '', "url" => "", "note" => '装修贷申请'), $this->JSONP);
        }
    }

    //收录报价信息
    public function offer()
    {
        $this->ajaxReturn(array('status' => 0, 'msg' => '报价失败', 'data' => '', "url" => "", "note" => '收录报价信息'), $this->JSONP);
    }

    //美家设计申请
    public function design_apply()
    {
        $this->ajaxReturn(array('status' => 0, 'msg' => '申请失败', 'data' => '', "url" => "", "note" => '美家设计申请'), $this->JSONP);
    }

    //文章访问统计
    public function article_view_count()
    {
        //设置请求记录*************start***************
        if (!check_session_request("article_view_count")) $this->ajaxReturn(array('status' => 0, 'msg' => '您的请求次数太频繁,请休息一会！', 'data' => '', "url" => "", "note" => ''), $this->JSONP);
        set_session_request("article_view_count");//设置请求记录
        //设置请求记录*************start***************

        $id = $this->lqgetid;
        $returnData = $this->D_ART->setViewCount($id);
        if ($returnData["status"]) {
            $info = PAGE_S("page_article_" . $id, '', $this->cache_options); //读取缓存数据
            $info["page_view"] = $returnData["data"];
            PAGE_S("page_article_" . $id, $info, $this->cache_options); //缓存数据
        }
        $this->ajaxReturn($returnData, $this->JSONP);
    }

    //产品访问统计
    public function product_view_count()
    {
        $id = $this->lqgetid;
        $returnData = $this->D_PRO->setViewCount($id);
        if ($returnData["status"]) {
            $info = PAGE_S("page_product_" . $id, '', $this->cache_options); //读取缓存数据
            $info["page_view"] = $returnData["data"];
            PAGE_S("page_product_" . $id, $info, $this->cache_options); //缓存数据
        }
        $this->ajaxReturn($returnData, $this->JSONP);

    }

    //作品点赞统计
    public function works_agrees_count()
    {
        //设置请求记录*************start***************
        if (!check_session_request("works_agrees_count")) $this->ajaxReturn(array('status' => 0, 'msg' => '您的请求次数太频繁,请休息一会！', 'data' => '', "url" => "", "note" => ''), $this->JSONP);
        set_session_request("works_agrees_count");//设置请求记录
        //设置请求记录*************start***************

        $id = $this->lqgetid;
        $returnData = $this->D_DESIGNER->setAgreesCount($id);
        if ($returnData["status"]) {
            $info = PAGE_S("page_works_" . $id, '', $this->cache_options); //读取缓存数据
            $info["agrees"] = $returnData["data"];
            PAGE_S("page_works_" . $id, $info, $this->cache_options); //缓存数据
        }
        $this->ajaxReturn($returnData, $this->JSONP);
    }

    //作品访问统计
    public function works_view_count()
    {
        //设置请求记录*************start***************
        if (!check_session_request("works_view_count")) $this->ajaxReturn(array('status' => 0, 'msg' => '您的请求次数太频繁,请休息一会！', 'data' => '', "url" => "", "note" => ''), $this->JSONP);
        set_session_request("works_view_count");//设置请求记录
        //设置请求记录*************start***************
        $id = $this->lqgetid;
        $returnData = $this->D_DESIGNER->setViewCount($this->lqgetid);
        if ($returnData["status"]) {
            $info = PAGE_S("page_works_" . $id, '', $this->cache_options); //读取缓存数据
            $info["clicks"] = $returnData["data"];
            PAGE_S("page_works_" . $id, $info, $this->cache_options); //缓存数据
        }
        $this->ajaxReturn($returnData, $this->JSONP);
    }

    //日记访问量
    public function diary_view_count()
    {
        $id = $this->lqgetid;
        $returnData = $this->D_HDDIARY->setViewCount($id);
        if ($returnData["status"]) {
            $info = PAGE_S("page_diary_" . $id, '', $this->cache_options); //读取缓存数据
            $info["clicks"] = $returnData["data"];
            PAGE_S("page_diary_" . $id, $info, $this->cache_options); //缓存数据
        }
        $this->ajaxReturn($returnData, $this->JSONP);
    }
}