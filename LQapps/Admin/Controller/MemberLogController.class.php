<?php // 会员日志 页面操作 
namespace Admin\Controller;
use Think\Controller;
use Member\Api\MemberApi as MemberApi;

class MemberLogController extends PublicController{
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->model_member = new MemberApi;//会员实例化
	}

    
	//列表页
    public function index() {
		//列表表单初始化****start
		$page_parameter["s"]=$this->getSafeData('s');
		$this->reSearchPara($page_parameter["s"]);//反回搜索数
		$search_content_array=array(
			'pagesize'=>urldecode(I('get.pagesize','0','int')),
			'fkeyword'=>trim(urldecode(I('get.fkeyword',$this->keywordDefault))),
			'keymode'=>urldecode(I('get.keymode','1','int')),
			'time_start'=>I('get.time_start',lq_cdate(0,0,(-604800))),
			'time_end'=>I('get.time_end',lq_cdate(0,0))
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		//sql合并
		$sqlwhere_parameter=" 1 ";//sql条件
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_member_account ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and (zc_action like'".$search_content_array["fkeyword"]."%' or zc_description like'".$search_content_array["fkeyword"]."%') ";
			}	
			if($search_content_array["time_start"]){
				$ts=strtotime($search_content_array["time_start"]." 00:00:00");
				$te=strtotime($search_content_array["time_end"]." 23:59:59");
				$sqlwhere_parameter.=" and zn_cdate >=".$ts." and zn_cdate<=".$te;	
			}					
		}
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zn_cdate'=>'操作时间','zn_ip'=>'操作IP','zc_member_account'=>'帐号','zc_description'=>'标题','os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zc_description`,`zc_member_account`,`zn_ip`,`zc_url`,`zn_cdate`",
				'where'=>$sqlwhere_parameter,
				'order'=>"id DESC",
				'title'=>$page_title,
				'thinkphpurl'=>__CONTROLLER__."/",
		);		
		if($search_content_array["pagesize"]) C("PAGESIZE",$search_content_array["pagesize"]);
		//列表表单初始化****end
		
        $count = $this->model_member->apiLogCount($sqlwhere_parameter);
		$page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->model_member->apiLogList($page->firstRow, $page->listRows,$page_config));
		$this->assign('empty_msg',$this->tableEmptyMsg(count($page_title)));
		$this->assign("page_config",$page_config);//列表设置赋值模板
        $this->display();
    }
	
	//多记录删除
    public function opDeleteCheckbox() {
        $this->ajaxReturn($this->model_member->apiDeleteLog());
    }

	//更改  zlvisible 
    public function opVisible() {
        $this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->tellAdmin, U("/Index/desktop"));
    }	
	
}
?>