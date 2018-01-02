<?php //操作行为 Attachment 页面操作 
namespace Admin\Controller;

use Think\Controller;
use Attachment\Api\AttachmentApi as AttachmentApi;

class AttachmentController extends PublicController
{
    public $Attachment;

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->Attachment = new AttachmentApi;

        // 检测节点访问权限
        $action_array = array('images', 'editorImages', 'editorFiles');
        if (!in_array(ACTION_NAME, $action_array)) {
            if (session('admin_auth')["id"] != 1) {
                //$this->error('对不起，您未授权访问该页面！' . $this->systemMsg, U("/Index"));
            }
        }


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
            'time_start' => I('get.time_start', lq_cdate(0, 0)),
            'time_end' => I('get.time_end', lq_cdate(0, 0)),
        );
        $this->assign("search_content", $search_content_array);//搜索表单赋值

        //sql合并
        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["fkeyword"] && $search_content_array["fkeyword"] != $this->keywordDefault) {
            if ($search_content_array["keymode"] == 1) {
                $sqlwhere_parameter .= " and zc_original_name ='" . $search_content_array["fkeyword"] . "' ";
            } elseif ($search_content_array["keymode"] == 2) {
                $sqlwhere_parameter .= " and zc_suffix ='" . $search_content_array["fkeyword"] . "' ";
            } elseif ($search_content_array["keymode"] == 3) {
                $sqlwhere_parameter .= " and zc_account ='" . $search_content_array["fkeyword"] . "' ";
            } else {
                $sqlwhere_parameter .= " and (zc_original_name like '%" . $search_content_array["fkeyword"] . "%' or zc_suffix like '%" . $search_content_array["fkeyword"] . "%' or zc_account like '%" . $search_content_array["fkeyword"] . "%')";
            }
        }
        if ($search_content_array["time_start"] && $search_content_array["open_time"]==1) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
        }


        //首页设置
        $page_title = array('checkbox' => 'checkbox', 'id' => L("LIST_ID"), 'zc_account' => '上传用户', 'zn_type' => '文件类别', 'zc_original_name' => '文件名', 'zc_file_path' => '文件', 'time' => '上传时间');
        $page_config = array(
            'field' => "`id`,`zc_account`,`zn_type`,`zc_original_name`,`zc_suffix`,`zc_file_path`,`zn_cdate`",
            'where' => $sqlwhere_parameter,
            'order' => 'id DESC',
            'title' => $page_title,
            'thinkphpurl' => __CONTROLLER__ . "/",
        );
        if ($search_content_array["pagesize"]) C("PAGESIZE", $search_content_array["pagesize"]);
        //列表表单初始化****end
        C("PAGESIZE", 5);
        $count = $this->Attachment->listCount($sqlwhere_parameter);
        $page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->Attachment->lqList($page->firstRow, $page->listRows, $page_config));
        $this->assign('empty_msg', $this->tableEmptyMsg(count($page_title)));
        $this->assign("page_config", $page_config);//列表设置赋值模板
        $this->display();
    }

    //多记录删除
    public function opDeleteCheckbox()
    {
        //删除文件
        $data["id"] = array('in', I("get.tcid", '', 'lqSafeExplode'));
        $this->ajaxReturn($this->Attachment->DelAttachment($data));
    }
    //处理文件 上传、删除 e

//**************************************************************************************************************************************************************************
    //图片库
    public function images()
    {
        //列表表单初始化****start
        $page_parameter["s"] = $this->getSafeData('s');
        $this->reSearchPara($page_parameter["s"]);//反回搜索数
        $search_content_array = array(
            'fkeyword' => urldecode(I('get.fkeyword', $this->keywordDefault)),
            'field' => urldecode(I('get.field', '')),
            'returnData' => urldecode(I('get.returnData', '')),
            'checkbox' => urldecode(I('get.checkbox', '0', 'int')),
            'quantity' => urldecode(I('get.quantity', '0', 'int')),
            'type' => urldecode(I('get.type', '')),
        );
        if (!array_key_exists($search_content_array["type"], C("UPLOAD_EXT"))) {
            $this->error('对不起，上传目录不存在！' . $this->systemMsg, U("/Index"));
        }
        $this->assign("search_content", $search_content_array);//搜索表单赋值
        //sql合并
        $sqlwhere_parameter = " zn_type=0 ";//sql条件
        if ($search_content_array["fkeyword"] && $search_content_array["fkeyword"] != $this->keywordDefault) {
            $sqlwhere_parameter .= " and (zc_original_name like '" . $search_content_array["fkeyword"] . "%' or zc_sys_name like '" . $search_content_array["fkeyword"] . "%') ";
        }
        if ($search_content_array["type"]) {
            $sqlwhere_parameter .= " and zc_folder='" . $search_content_array["type"] . "'";
        }
        //首页设置
        $page_title = array();
        $page_config = array(
            'field' => "`id`,`zc_account`,`zn_type`,`zc_file_path`,`zc_original_name`,`zc_suffix`",
            'where' => $sqlwhere_parameter,
            'order' => 'id DESC',
        );
        C("PAGESIZE", 12);
        //列表表单初始化****end

        $count = $this->Attachment->listCount($sqlwhere_parameter);
        $page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->window_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->Attachment->lqList($page->firstRow, $page->listRows, $page_config));
        $this->assign('empty_msg', $this->tableEmptyMsg(count($page_title)));
        $this->assign("page_config", $page_config);//列表设置赋值模板
        $this->assign("img_default", REL_ROOT . "Public/Static/images/upload-pic.png");
        $this->assign("title", '图片库');//标题
        $this->display('images-list');
    }


//**************************************************************************************************************************************************************************
    //编辑器的图片
    public function editorImages()
    {
        //列表表单初始化****start
        $start = I("get.start", '0', 'int');//页码
        $keyword = I("get.keyword", '', 'base64_decode');//关键字
        $keyword = urldecode($keyword);
        $listRows = 20;
        //sql合并
        $sqlwhere_parameter = " zn_type=0 and zc_folder='editorfile' ";//sql条件
        if ($keyword) {
            $sqlwhere_parameter .= " and zc_original_name like '" . $keyword . "%'";
            $listRows = 500;
        }
        if (session('admin_auth')["id"] != 1) {
            $sqlwhere_parameter .= " and zn_uid=" . session('admin_auth')["id"];//sql条件
        }
        $count = $this->Attachment->listCount($sqlwhere_parameter);
        $page_config = array(
            'field' => "`id`,`zc_account`,`zn_type`,`zc_file_path`,`zc_original_name`,`zc_suffix`,`zn_cdate`",
            'where' => $sqlwhere_parameter,
            'order' => 'id DESC',
        );
        //列表表单初始化****end
        $arr = array();
        $list = $this->Attachment->lqList($start, $listRows, $page_config);
        foreach ($list as $key => $value) $arr[] = array("url" => $value["zc_file_path"], "title" => $value["zc_original_name"], "mtime" => $value["zn_cdate"]);
        $this->ajaxReturn(array("state" => "SUCCESS", "list" => $arr, "start" => $start, "total" => $count));
    }

    //编辑器的文件
    public function editorFiles()
    {
        //列表表单初始化****start
        $start = I("get.start", '0', 'int');//页码
        $keyword = I("get.keyword", '', 'base64_decode');//关键字
        $keyword = urldecode($keyword);
        $listRows = 20;
        //sql合并
        $sqlwhere_parameter = " zc_folder='file' ";//sql条件
        if ($keyword) {
            $sqlwhere_parameter .= " and zc_original_name like '" . $keyword . "%'";
        }
        if (session('admin_auth')["id"] != 1) {
            $sqlwhere_parameter .= " zn_uid=" . session('admin_auth')["id"];//sql条件
        }
        $count = $this->Attachment->listCount($sqlwhere_parameter);
        $page_config = array(
            'field' => "`id`,`zc_account`,`zn_type`,`zc_file_path`,`zc_original_name`,`zc_suffix`,`zn_cdate`",
            'where' => $sqlwhere_parameter,
            'order' => 'id DESC',
        );
        //列表表单初始化****end
        $arr = array();
        $list = $this->Attachment->lqList($start, $listRows, $page_config);
        foreach ($list as $key => $value) $arr[] = array("url" => $value["zc_file_path"], "title" => $value["zc_original_name"], "mtime" => $value["zn_cdate"]);
        $this->ajaxReturn(array("state" => "SUCCESS", "list" => $arr, "start" => $start, "total" => $count));

    }


}

?>