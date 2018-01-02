<?php //粉丝管理 Follow 介面操作 
namespace Admin\Controller;

use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use Member\Api\MemberApi as MemberApi;

class FollowController extends PublicController
{
    public $myTable;
    protected $myForm = array(
        //标题
        'tab_title' => array(1 => '基本信息', 2 => '数据', 3 => '其他'),
        //基本信息
        '1' => array(
            array('show_id_value', 'zn_member_id', "会员", 1, '{"islabel":1}'),
            array('show', 'zc_openid', "OPENID", 1, '{}'),
            array('show', 'zc_nickname', "昵称", 1, '{}'),
            array('show_id_value', 'zn_sex', "性别", 1, '{"islabel":1}'),
            array('show_img', 'zc_headimg_url', "头像", 1, '{"type":"images"}'),
        ),
        //数据
        '2' => array(
            array('show_id_value', 'zn_subscribe_time', "关注时间", 1, '{"islabel":1}'),
            array('show', 'zl_status', "用户状态", 1, '{}'),
            array('show', 'zn_score', "财富值", 1, '{}'),
            array('show', 'zn_experience', "经验值", 1, '{}'),
            array('show', 'zn_groupid', "用户所在分组", 1, '{}'),
            array('show', 'zn_sort', "排序", 1, '{}'),
        ),
        //其他
        '3' => array(
            array('show', 'zc_country', "国家", 1, '{}'),
            array('show', 'zc_province', "省份", 1, '{}'),
            array('show', 'zc_city', "城市", 1, '{}'),
            array('show', 'zc_mobile', "手机号", 1, '{}'),
            array('show', 'zc_language', "语言", 1, '{}'),
            array('show', 'zc_remark', "用户备注名", 1, '{}'),
        ),
    );

    /**
     * +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::_initialize();
     * +----------------------------------------------------------
     */
    public function __construct()
    {
        parent::__construct();
        $this->myTable = M($this->pcTable);//主表实例化
    }

    //列表页
    public function index()
    {
        //列表表单初始化****start
        $page_parameter["s"] = $this->getSafeData('s');
        $this->reSearchPara($page_parameter["s"]);//反回搜索数
        $search_content_array = array(
            'pagesize' => urldecode(I('get.pagesize', '0', 'int')),
            'fkeyword' => trim(urldecode(I('get.fkeyword', $this->keywordDefault))),
            'keymode' => urldecode(I('get.keymode', '1', 'int')),
            'open_time' => urldecode(I('get.open_time', '0', 'int')),
            'time_start' => I('get.time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('get.time_end', lq_cdate(0, 0)),
            'sex' => I('get.sex', ''),
            'use' => I('get.use', ''),
        );
        $this->assign("search_content", $search_content_array);//搜索表单赋值
        $this->assign("sex_str", lqCreatOption(C("_SEX"), $search_content_array["sex"], "请选择性别"));
        $this->assign("use_str", lqCreatOption(array(0 => '取消关注', 1 => '已关注'), $search_content_array["use"], "请选择关注状态"));
        if ($search_content_array["pagesize"]) C("PAGESIZE", $search_content_array["pagesize"]);

        //sql合并
        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["fkeyword"] && $search_content_array["fkeyword"] != $this->keywordDefault) {
            if ($search_content_array["keymode"] == 1) {
                $sqlwhere_parameter .= " and zc_nickname ='" . $search_content_array["fkeyword"] . "' ";
            } else {
                $sqlwhere_parameter .= " and (zc_nickname like '%" . $search_content_array["fkeyword"] . "%' or zc_openid like '%" . $search_content_array["fkeyword"] . "%' ) ";
            }
        }

        if ($search_content_array["use"] != '') {
            $search_content_array["use"] = intval($search_content_array["use"]);
            $sqlwhere_parameter .= " and zl_visible = " . $search_content_array["use"];

            if ($search_content_array["open_time"] == 1 && $search_content_array["time_start"] && $search_content_array["time_end"]) {
                $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
                $te = strtotime($search_content_array["time_end"] . " 23:59:59");
                if ($search_content_array["use"] == 1) {
                    $sqlwhere_parameter .= " and zn_subscribe_time >=" . $ts . " and zn_subscribe_time<=" . $te;
                } else {
                    $sqlwhere_parameter .= " and zn_unsubscribe_time >=" . $ts . " and zn_unsubscribe_time<=" . $te;
                }
            }

        } else {
            if ($search_content_array["open_time"] == 1 && $search_content_array["time_start"] && $search_content_array["time_end"]) {
                $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
                $te = strtotime($search_content_array["time_end"] . " 23:59:59");
                $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
            }
        }


        //首页设置s
        $page_title = array('checkbox' => 'checkbox', 'no' => L("LIST_NO"), 'headimg' => '头像', 'member' => '邦定会员/昵称', 'openid' => 'OPENID', 'area' => '区域', 'zn_sex' => '性别', 'zn_subscribe_time' => 'OPENID/关注时间', 'os' => L("LIST_OS"));
        $page_config = array(
            'field' => "`id`,`zc_openid`,`zn_member_id`,`zc_member_account`,`zn_sex`,`zc_nickname`,`zc_headimg_url`,`zn_subscribe_time`,`zn_unsubscribe_time`,`zc_country`,`zc_province`,`zc_city`,`zl_visible`",
            'where' => $sqlwhere_parameter,
            'order' => 'id DESC',
            'title' => $page_title,
            'thinkphpurl' => __CONTROLLER__ . "/",
        );
        //列表表单初始化 e
        $count = $this->myTable->where($sqlwhere_parameter)->count();
        $page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->C_D->lqList($page->firstRow, $page->listRows, $page_config));
        $this->assign('empty_msg', $this->tableEmptyMsg(count($page_title)));
        $this->assign("page_config", $page_config);//列表设置赋值模板
        $this->display();
    }

    // 更新/编辑
    public function edit()
    {
        if (IS_POST) {
            $this->ajaxReturn(array('status' => 0, 'msg' => "该功能已屏敝"));
        } else {
            $lcdisplay = 'Public/com_edit';

            //读取数据s
            $data = $this->myTable->where("id=" . $this->lqgetid)->find();
            if (!$data) {
                $this->error(C("ALERT_ARRAY")["recordNull"]);
            }//无记录
            //读取数据e

            //上下页 s
            $this->pagePrevNext($this->myTable, "id", "zc_nickname");
            //上下页 e

            //表单数据初始化s
            $FORM_ARRAY = array();
            //操作时间
            $FORM_ARRAY["os_record_time"] = $this->osRecordTime($data);
            foreach ($data as $lnKey => $laValue) {
                $FORM_ARRAY[$lnKey] = $laValue;
            }
            $Member = new MemberApi;
            $FORM_ARRAY["zn_member_id_label"] = $Member->info($FORM_ARRAY['zn_member_id'])["zc_account"];
            $FORM_ARRAY["zn_sex_label"] = C("_SEX")[$FORM_ARRAY['zn_sex']];
            $FORM_ARRAY["zn_subscribe_time_label"] = LQ_cdate($FORM_ARRAY['zn_subscribe_time']);
            $Form = new Form($this->myForm, $FORM_ARRAY, $this->myTable->getCacheComment());
            $this->assign("LQFdata", $Form->createHtml());//表单数据
            //表单数据初始化s

            $this->display($lcdisplay);
        }
    }

    /////微信关注列表同步
    public function reset_follow()
    {
        import('Vendor.Wechat.TPWechat');
        $WxObj = new \Wechat(C("WECHAT"));

        $list = $WxObj->getUserList();

        $i = 0;
        $j = 0;
        $model_memeber = M("member");
        $model_follow = M("follow");
        $model_none =  M();
        $model_member_api = new \Member\Api\MemberApi;//实例化会员
        foreach ($list['data']['openid'] as $k => $v) {
            $is_follow = $model_follow->field("id,zl_type")->where("zc_openid = '" . $v . "'")->find();

            /////更新粉丝会员信息
            if ($is_follow) {
                if($is_follow['zl_type'] != 1) {
                    $model_none->execute("update `lq_follow` set zl_type = 1 where zc_openid = '" . $v . "'");
                    $i++;
                }
            } else {
                $memberInfo = $model_memeber->field("id,zc_account")->where("zc_openid = '" . $v . "'")->find();
                $UserInfo = $WxObj->getUserInfo($v);

                //入粉丝库
                $dataFollow = array();
                $dataFollow["zl_type"] = 1;
                $dataFollow["zc_openid"] = $UserInfo["openid"];
                $dataFollow["zn_member_id"] = $memberInfo['id'] ? $memberInfo['id'] : 0;
                $dataFollow["zc_member_account"] = $memberInfo['zc_account'] ? $memberInfo['zc_account'] : 0;
                $dataFollow["zc_nickname"] = lq_set_nickname($UserInfo["nickname"]);
                $dataFollow["zn_sex"] = $UserInfo["sex"];
                $dataFollow["zc_country"] = $UserInfo["country"];
                $dataFollow["zc_province"] = $UserInfo["province"];
                $dataFollow["zc_city"] = $UserInfo["city"];
                $dataFollow["zc_language"] = $UserInfo["language"];
                $dataFollow["zc_headimg_url"] = $UserInfo["headimgurl"];
                $dataFollow["zc_remark"] = lqNull($UserInfo["remark"]);
                $dataFollow["zn_groupid"] = intval($UserInfo["groupid"]);
                if (!$UserInfo["subscribe_time"]) $UserInfo["subscribe_time"] = NOW_TIME;
                $dataFollow["zn_subscribe_time"] = $UserInfo["subscribe_time"];
                $dataFollow["zn_unsubscribe_time"] = 0;
                $dataFollow["zl_visible"] = 1;
                $dataFollow["zn_cdate"] = $UserInfo["subscribe_time"];

                if (!$model_member_api->apiFollowCount("zc_openid='" . $v . "'")) {
                    $model_member_api->apiInsertFollow($dataFollow);
                    $j++;
                }
            }
        }

        $success_str = "同步成功：共 ".($i+$j)." 个 ,更新：".$i ."个，插入".$j ."个";
        $this->success($success_str,U("index"));
    }

}

?>