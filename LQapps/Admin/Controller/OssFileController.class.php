<?php //操作行为 Attachment 页面操作 
namespace Admin\Controller;

use Think\Controller;
use Video\Api\ossApi;

class OssFileController extends PublicController
{
    public $ossApi, $ossFile;

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->ossApi = new ossApi();
        $this->ossFile = D("OssFile");

        // 检测节点访问权限
        $action_array = array('images', 'editorImages', 'editorFiles');
        if (!in_array(ACTION_NAME, $action_array)) {
            if (session('admin_auth')["id"] != 1) {
               // $this->error('对不起，您未授权访问该页面！' . $this->systemMsg, U("/Index"));
            }
        }
    }


    public function index()
    {
        //列表表单初始化****start
        $page_parameter["s"] = $this->getSafeData('s');
        $this->reSearchPara($page_parameter["s"]);//反回搜索数
        $search_content_array = array(
            'pagesize' => urldecode(I('get.pagesize', '0', 'int')),
            'fkeyword' => trim(urldecode(I('get.fkeyword', $this->keywordDefault))),
            'keymode' => urldecode(I('get.keymode', '1', 'int')),
            'zn_fid' => urldecode(I('get.zn_fid', '0', 'int')),
            'open_time' => urldecode(I('get.open_time', '0', 'int')),
            'time_start' => I('get.time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('get.time_end', lq_cdate(0, 0)),
        );
        $this->assign("search_content", $search_content_array);//搜索表单赋值

        //sql合并
        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["fkeyword"] && $search_content_array["fkeyword"] != $this->keywordDefault) {
            if ($search_content_array["keymode"] == 1) {
                $sqlwhere_parameter .= " and (zc_original_name ='" . $search_content_array["fkeyword"] . "' or zc_nickname='" . $search_content_array["fkeyword"] . "') ";
            } else {
                $sqlwhere_parameter .= " and (zc_original_name like '%" . $search_content_array["fkeyword"] . "%' or zc_nickname like '%" . $search_content_array["fkeyword"] . "%')";
            }
        }
        if ($search_content_array["open_time"] == 1 && $search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
        }
        if ($search_content_array["zn_fid"]) {
            $ids = M("Live")->where("id=".$search_content_array['zn_fid'])->getField("zc_file");
            $sqlwhere_parameter .= " and id IN (" . $ids .")";
        }
        //首页设置
        $page_title = array('id' => L("LIST_ID"), 'title' => '课程', 'user' => '上传用户', 'zc_original_name' => '文件名', 'zn_size' => '文件大小', 'time' => '上传时间', 'op' => '操作');
        $page_config = array(
            'field' => "`id`,`zn_type`,`zn_lesson_type`,`zn_uid`,`zn_fid`,`zc_file_name`,`zn_lesson_id`,`zn_size`,`zn_cdate`",
            'where' => $sqlwhere_parameter,
            'order' => 'id DESC',
            'title' => $page_title,
            'thinkphpurl' => __CONTROLLER__ . "/",
        );
        if ($search_content_array["pagesize"]) C("PAGESIZE", $search_content_array["pagesize"]);
        //列表表单初始化****end
        C("PAGESIZE", 5);
        $count = $this->ossFile->where($sqlwhere_parameter)->count();
        $page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->ossFile->lqList($page->firstRow, $page->listRows, $page_config));
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

    /*
     * 下载附件，附件位置阿里云
     */
    public function downlodFile()
    {
        $file_id = I("get.tnid");

        if (!$file_id) $this->error("参数错误...");

        $fileInfo = M("OssFile")->field("zc_file_path")->find($file_id);
        if(!$fileInfo) $this->error("附件不存在或已经删除...");
        download($fileInfo['zc_file_path']);
    }

    /*
     * 删除附件 本地库删除+阿里云端删除
     */
    public function deleteFile()
    {
        $reData = array(
            "status" => 0,
            "msg" => "操作失败"
        );
        $file_id = I("get.tnid");

        if (!$file_id) $this->ajaxReturn($reData);

        $db = M("OssFile");
        $fileInfo = $db->field("zc_object")->find($file_id);

        $this->ossApi->deleteObject($fileInfo['zc_object']);
        $del = $db->where("id=" . $file_id)->delete();

        if ($del) {
            $reData = array(
                "status" => 1,
                "msg" => "附件删除成功",
            );
        }

        $this->ajaxReturn($reData);

    }
//**************************************************************************************************************************************************************************

}

?>