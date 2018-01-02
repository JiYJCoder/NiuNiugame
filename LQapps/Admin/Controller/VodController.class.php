<?php //文章管理 Article 页面操作 
namespace Admin\Controller;

use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use LQLibs\Util\Category as Category;//树状分类

class VodController extends PublicController
{
    public $myTable, $model_apply;
    protected $myForm = array(
        //标题
        'tab_title' => array(1 => '基本信息', 2 => '课程状态'),
        //通用信息
        '1' => array(
            array('buttonDialog', 'zn_cat_id', "课程分类", 1, '{"required":"1","dataLength":"","readonly":1,"disabled":0,"controller":"LessonCat","type":"tree","checkbox":"0"}'),
            array('text', 'zc_title', "课程标题", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":120}'),
            array('image', 'zc_image', "封面图", 1, '{"type":"images","allowOpen":1}'),
            array('textarea', 'zc_summary', "课程简介", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
            array('text', 'zc_expect_lesson', "预计课节", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
            array('editor', 'zc_content', "课程描述", 1, '{"required":"0","model":"1","ext":"LQF","width":"100%","height":"300px"}'),
            array('text', 'zn_sort', "排序", 1, '{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
            array('date', 'zd_send_time', "发布时间", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"format":"Y-m-d H:i"}'),
        ),
        "2" => array(
            array('radio', 'zl_status', "课程状态", 1, '{"class":"","required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
            array('textarea', 'zc_reason', "原因", 1, '{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
        )


    );

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->myTable = M($this->pcTable);//主表实例化
        $this->model_apply = D("LessonApply");
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
            'keymode' => urldecode(I('get.keymode', '0', 'int')),
            'open_time' => urldecode(I('get.open_time', '0', 'int')),
            'time_start' => I('get.time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('get.time_end', lq_cdate(0, 0)),
            'cat_id' => I('get.cat_id', '', 'int'),
            'status' => I('get.status', '', 'int'),
            'teacher_id' => I('get.teacher_id', '', 'int'),
        );

        $this->assign("search_content", $search_content_array);//搜索表单赋值
        $catList = F('lesson_cat', '', COMMON_ARRAY);
        foreach ($catList as $k => $v) {
            if ($v["zl_visible"] == 0) unset($catList[$k]);
        }
        $this->assign("zn_cat_id_str", lqCreatOption(lq_return_array_one($catList, 'id', 'fullname'), $search_content_array["cat_id"], "选择分类"));//文件类型

        $this->assign("sys_heading", '录播列表');
        $status = C('LIVE_STATUS');
        $this->assign("recommend_str", lqCreatOption($status, $search_content_array["status"], "请选择"));

        //sql合并
        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["fkeyword"] && $search_content_array["fkeyword"] != $this->keywordDefault) {
            if ($search_content_array["keymode"] == 1) {
                $sqlwhere_parameter .= " and zc_title ='" . $search_content_array["fkeyword"] . "' ";
            } else {
                $sqlwhere_parameter .= " and (zc_title like '%" . $search_content_array["fkeyword"] . "%') ";
            }
        }
        if ($search_content_array["cat_id"]) {
            $tree = new Category('lesson_cat', array('id', 'zn_fid', 'zc_caption'));
            $child_ids = $tree->get_child($search_content_array["cat_id"], 10, 'zl_visible=1');
            if (ereg("^[0-9]+$", $child_ids)) {
                $sqlwhere_parameter .= " and zn_cat_id = " . intval($child_ids);
            } else {
                $sqlwhere_parameter .= " and zn_cat_id in (" . $child_ids . ") ";
            }
        }
        if ($search_content_array["status"]) {
            $sqlwhere_parameter .= " and zl_status = " . $search_content_array["status"];
            $this->assign("sys_heading", C('VOD_STATUS')[$search_content_array["status"]]);
        }
        if ($search_content_array["teacher_id"]) {
            $sqlwhere_parameter .= " and zn_teacher_id = " . $search_content_array["teacher_id"];
        }
        if ($search_content_array["open_time"] == 1 && $search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zd_send_time >=" . $ts . " and zd_send_time<=" . $te;
        }

        //首页设置
        $page_title = array('checkbox' => 'checkbox', 'no' => L("LIST_NO"), 'zc_image' => '图片', 'zn_cat_id' => '文章类型', 'zc_title' => '文章标题', 'zn_spe' => '推荐', 'status' => L("LIST_STAYUS"), 'os' => L("LIST_OS"));
        $page_config = array(
            'field' => "`id`,`zn_fid`,`zn_cat_id`,`zn_teacher_id`,`zc_image`,`zc_title`,`zl_is_index`,`zc_expect_lesson`,`zl_is_good`,`zl_status`",
            'where' => $sqlwhere_parameter,
            'order' => 'zn_sort,id DESC',
            'title' => $page_title,
            'thinkphpurl' => __CONTROLLER__ . "/",
        );
        if ($search_content_array["pagesize"]) C("PAGESIZE", $search_content_array["pagesize"]);
        //列表表单初始化****end
        //$this->assign('set_arr',C('LIVE_STATUS'));

        $count = $this->myTable->alias("p")->where($sqlwhere_parameter)->count();
        $page = new \LQLibs\Util\Page($count,5, $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->C_D->lqList($page->firstRow, $page->listRows, $page_config));
        $this->assign('empty_msg', $this->tableEmptyMsg(count($page_title)));
        $this->assign("page_config", $page_config);//列表设置赋值模板
        $this->display();
    }

    //ajax搜索
    public function ajaxSearch()
    {
        //...地址栏参数
        $page_parameter["s"] = $this->getSafeData('s');
        $this->reSearchPara($page_parameter["s"]);//反回搜索数
        $search_content_array = array(
            'fkeyword' => trim(urldecode(I('get.fkeyword', $this->keywordDefault))),
            'cat_id' => I('get.cat_id', '', 'int'),
        );
        //sql合并s
        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["fkeyword"] && $search_content_array["fkeyword"] != $this->keywordDefault) {
            if (preg_match("/^\d*$/", $search_content_array["fkeyword"])) {
                $sqlwhere_parameter .= " and id=" . intval($search_content_array["fkeyword"]);
            } else {
                $sqlwhere_parameter .= " and (zc_title like '%" . $search_content_array["fkeyword"] . "%') ";
            }
        }
        if ($search_content_array["cat_id"]) {
            $tree = new Category('article_cat', array('id', 'zn_fid', 'zc_caption'));
            $child_ids = $tree->get_child($search_content_array["cat_id"], 10, 'zl_visible=1');
            if (ereg("^[0-9]+$", $child_ids)) {
                $sqlwhere_parameter .= " and zn_cat_id = " . intval($child_ids);
            } else {
                $sqlwhere_parameter .= " and zn_cat_id in (" . $child_ids . ") ";
            }
        }
        $html = '';
        $list = M("Vod")->field("`id`,`zc_title`")->where($sqlwhere_parameter)->order('zn_sort,id DESC')->limit("0,30")->select();
        if ($list) {
            $html = lqCreatOption(lq_return_array_one($list, 'id', 'zc_title'), '', "");
            $this->ajaxReturn(array('status' => 1, 'msg' => '搜索成功', 'data' => $html));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '搜索失败', 'data' => ''));
        }
    }

    // 插入/添加
    public function add()
    {
        if (IS_POST) {
            $this->ajaxReturn($this->C_D->lqSubmit());
        } else {
            $lcdisplay = 'Public/common-edit';//引用模板

            //表单数据初始化s
            $form_array = lq_post_memory_data();//获得上次表单的记忆数据
            $form_array["id"] = '';
            $form_array["zd_send_time"] = 0;
            $form_array["zl_status_data"] = C('VOD_STATUS');
            $form_array["zn_sort"] = C("COM_SORT_NUM");
            $form_array["zn_page_view"] = $form_array["zn_share"] = $form_array["zn_agrees"] = 0;
            $Form = new Form($this->myForm, $form_array, $this->myTable->getCacheComment());
            $this->assign("LQFdata", $Form->createHtml());//表单数据
            //表单数据初始化s
            $this->display($lcdisplay);
        }
    }

    // 更新/编辑
    public function edit()
    {
        if (IS_POST) {
            $returnData = $this->C_D->lqSubmit();
            $this->ajaxReturn($returnData);
        } else {
            $lcdisplay = 'Public/common-edit';

            //读取数据
            $data = $this->myTable->where("id=" . $this->lqgetid)->find();
            if (!$data) {
                $this->error(C("ALERT_ARRAY")["recordNull"]);
            }//无记录
            $this->pagePrevNext($this->myTable, "id", "zc_title");//上下页


            //表单数据初始化s
            $form_array = array();
            //操作时间
            $form_array["os_record_time"] = $this->osRecordTime($data);
            foreach ($data as $lnKey => $laValue) {
                $form_array[$lnKey] = $laValue;
            }
            $form_array["zn_cat_id_label"] = lq_return_array_one(F('lesson_cat', '', COMMON_ARRAY), 'id', 'zc_caption')[$data["zn_cat_id"]];
            $form_array["zl_status_data"] = C('VOD_STATUS');
            $Form = new Form($this->myForm, $form_array, $this->myTable->getCacheComment());
            $this->assign("LQFdata", $Form->createHtml());//表单数据
            //表单数据初始化s

            $this->display($lcdisplay);
        }
    }


    //更改字段值
    public function opProperty()
    {
        $data = array();
        $data["id"] = I("get.tnid", '0', 'int');
        $data["zl_status"] = I("get.vlaue", '0', 'int');

        $this->myTable->save($data);
        $op_data = array("status" => $data['zl_status'], "txt" => C('LIVE_STATUS')[$data["zl_status"]]);
        $dataReturn = array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' => $op_data, 'id' => $data["id"]);
        $this->ajaxReturn($dataReturn);
    }


    //列表页
    public function lesson_apply()
    {
        //列表表单初始化****start
        $page_parameter["s"] = $this->getSafeData('s');
        $this->reSearchPara($page_parameter["s"]);//反回搜索数
        $search_content_array = array(
            'pagesize' => urldecode(I('get.pagesize', '0', 'int')),
            'fkeyword' => trim(urldecode(I('get.fkeyword', $this->keywordDefault))),
            'keymode' => urldecode(I('get.keymode', '0', 'int')),
            'open_time' => urldecode(I('get.open_time', '0', 'int')),
            'time_start' => I('get.time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('get.time_end', lq_cdate(0, 0)),
            'status' => I('get.status', '', 'int'),
        );
        $this->assign("search_content", $search_content_array);//搜索表单赋值
        $catList = F('lesson_cat', '', COMMON_ARRAY);
        foreach ($catList as $k => $v) {
            if ($v["zl_visible"] == 0) unset($catList[$k]);
        }
        $this->assign("zn_cat_id_str", lqCreatOption(lq_return_array_one($catList, 'id', 'fullname'), $search_content_array["cat_id"], "选择分类"));//文件类型


        //sql合并
        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["fkeyword"] && $search_content_array["fkeyword"] != $this->keywordDefault) {
            if ($search_content_array["keymode"] == 1) {
                $sqlwhere_parameter .= " and zc_title ='" . $search_content_array["fkeyword"] . "' ";
            } else {
                $sqlwhere_parameter .= " and (zc_title like '%" . $search_content_array["fkeyword"] . "%') ";
            }
        }
        if ($search_content_array["cat_id"]) {
            $tree = new Category('lesson_cat', array('id', 'zn_fid', 'zc_caption'));
            $child_ids = $tree->get_child($search_content_array["cat_id"], 10, 'zl_visible=1');
            if (ereg("^[0-9]+$", $child_ids)) {
                $sqlwhere_parameter .= " and zn_cat_id = " . intval($child_ids);
            } else {
                $sqlwhere_parameter .= " and zn_cat_id in (" . $child_ids . ") ";
            }
        }
        if ($search_content_array["status"]) {
            $sqlwhere_parameter .= " and zn_status = " . $search_content_array["status"];

        }else{
            $sqlwhere_parameter .=" and zn_status <> 2";
        }
        if ($search_content_array["open_time"] == 1 && $search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zd_send_time >=" . $ts . " and zd_send_time<=" . $te;
        }


        //首页设置
        $page_title = array('checkbox' => 'checkbox', 'no' => L("LIST_NO"), 'zc_image' => '图片', 'zn_cat_id' => '文章类型', 'zc_title' => '文章标题', 'zn_spe' => '推荐', 'status' => L("LIST_STAYUS"), 'os' => L("LIST_OS"));
        $page_config = array(
            'field' => "`id`,`zn_type`,`zn_lesson_id`,`zn_member_id`,`zn_status`,`zn_cdate`",
            'where' => $sqlwhere_parameter,
            'order' => 'zn_cdate,id DESC',
            'title' => $page_title,
            'thinkphpurl' => __CONTROLLER__ . "/lesson_apply/",
        );
        if ($search_content_array["pagesize"]) C("PAGESIZE", $search_content_array["pagesize"]);
        //列表表单初始化****end
        //$this->assign('set_arr',C('LIVE_STATUS'));
        $status = C('LESSON_APPLY_STATUS');
        $this->assign("status_str", lqCreatOption($status, $search_content_array["status"], "请选择"));

        $count = $this->model_apply->alias("p")->where($sqlwhere_parameter)->count();
        $page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->model_apply->lqList($page->firstRow, $page->listRows, $page_config));
        $this->assign('empty_msg', $this->tableEmptyMsg(count($page_title)));
        $this->assign("page_config", $page_config);//列表设置赋值模板
        $this->display();
    }


    ///打开窗体
    public function apply_window()
    {
        if (IS_POST) {
            $status = I("post.status");
            $lesson = I("post.lesson");
            $savaData = array(
                "id" => I("post.id"),
                "zn_status" => $status,
            );
            if ($status == 2) $this->myTable->where("id=" . $lesson)->setField("zl_status", 6);

            $this->model_apply->save($savaData);
            $this->ajaxReturn(array("status" => 1, msg => "设置成功"));
        } else {
            $where = array(
                "id" => I("get.tnid")
            );
            $data = $this->model_apply->lqList(0, 1, $page_config = array('field' => '*', 'where' => $where, 'order' => '`id` DESC'));

            $this->assign("status_list", C('LESSON_APPLY_STATUS'));
            $this->assign("LQFdata", $data[0]);
            $this->display();
        }

    }

    /////常规统计汇总
    public function ajaxSearchData()
    {
        $list  = array();
        /////直播统计
        $list['vod_effect'] = M("Vod")->where("zl_status = 6")->count();
        $list['vod_finish'] = M("Vod")->where("zl_status = 1")->count();
        $list['vod_closed'] = M("Vod")->where("zl_status = 2")->count();
        $list['vod_apply'] = M("Vod")->where("zl_status = 4")->count();
        $list['teacher_total'] = count(M("Vod")->group("zn_teacher_id")->select());
        $list['student_total'] = count(M("VodRecord")->group("zn_member_id")->select());
        $list['vod_total'] = intval(M("LessonVod")->where("zc_vod_info <> ''")->count());

        $this->ajaxReturn($list);

    }

}

?>