<?php //操作行为 Attachment 页面操作 
namespace Ucenter\Controller;
use Think\Controller;
use Attachment\Api\AttachmentApi as AttachmentApi;

class AttachmentController extends PublicController{
	public $Attachment;
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->Attachment = new AttachmentApi;
	}
    

//**************************************************************************************************************************************************************************
	//图片库
    public function images(){
		//列表表单初始化****start
		$page_parameter["lqs"]=$this->getSafeData('lqs');
		$this->reSearchPara($page_parameter["lqs"]);//反回搜索数
		$search_content_array=array(
			'fkeyword'=> urldecode(I('get.fkeyword',$this->keywordDefault)),
			'field'=> urldecode(I('get.field','')) ,
			'returnData'=> urldecode(I('get.returnData','')) ,
			'checkbox'=> urldecode(I('get.checkbox','0','int')),
			'quantity'=> urldecode(I('get.quantity','0','int')),
			'type'=> urldecode(I('get.type','')) ,
		);
		if(!array_key_exists($search_content_array["type"],C("UPLOAD_EXT"))){
			$this->error('对不起，上传目录不存在！'.$this->systemMsg, U("/Index"));
		}
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		//sql合并
		$sqlwhere_parameter=" zn_type=0 and zn_user_type=2 and zn_uid=".session('member_auth')["id"];//sql条件
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			$sqlwhere_parameter.=" and zc_original_name like '".$search_content_array["fkeyword"]."%' ";
		}
		if($search_content_array["type"]){
			$sqlwhere_parameter.=" and zc_folder='".$search_content_array["type"]."'";
		}	
		//首页设置
		$page_title=array();
		$page_config = array(
				'field'=>"`id`,`zc_account`,`zn_type`,`zc_file_path`,`zc_original_name`,`zc_suffix`",
				'where'=>$sqlwhere_parameter,
				'order'=>'id DESC',
		);
		C("PAGESIZE",12);
		//列表表单初始化****end
		
        $count = $this->Attachment->listCount($sqlwhere_parameter);
		$page = new \LQLibs\Util\Page($count,C("PAGESIZE"),$page_parameter);//载入分页类
        $showPage = $page->window_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->Attachment->lqList($page->firstRow, $page->listRows,$page_config));
		$this->assign('empty_msg',$this->tableEmptyMsg(count($page_title)));
		$this->assign("page_config",$page_config);//列表设置赋值模板
		$this->assign("img_default",REL_ROOT."Public/Static/images/upload-pic.png");
		$this->assign("title",'图片库');//标题
		
        $lcdisplay='Public/images-list';//引用模板
        $this->display($lcdisplay);		
    }
	
	
//**************************************************************************************************************************************************************************
	//编辑器的图片
    public function editor_images(){
		//列表表单初始化****start
		$start=I("get.start",'0','int');//页码
		$keyword=I("get.keyword",'','base64_decode');//关键字
		$keyword=urldecode($keyword);
		$listRows = 20;
		//sql合并
		$sqlwhere_parameter=" zn_type=0 and zn_user_type=2 and zc_folder='editorfile' and zn_uid=".session('member_auth')["id"];//sql条件
		if($keyword){
			$sqlwhere_parameter.=" and zc_original_name like '".$keyword."%'";
			$listRows = 500;
		}
        $count = $this->Attachment->listCount($sqlwhere_parameter);
		$page_config = array(
				'field'=>"`id`,`zc_account`,`zn_type`,`zc_file_path`,`zc_original_name`,`zc_suffix`,`zn_cdate`",
				'where'=>$sqlwhere_parameter,
				'order'=>'id DESC',
		);
		//列表表单初始化****end
        $arr=array();
		$list = $this->Attachment->lqList($start,$listRows,$page_config);
		foreach($list as $key=>$value) $arr[] = array("url"=>$value["zc_file_path"],"title"=>$value["zc_original_name"],"mtime"=>$value["zn_cdate"]);
		$this->ajaxReturn(array("state"=>"SUCCESS","list"=>$arr,"start"=>$start,"total"=>$count));
    }
	
	//编辑器的文件
    public function editor_files(){
		//列表表单初始化****start
		$start=I("get.start",'0','int');//页码
		$keyword=I("get.keyword",'','base64_decode');//关键字
		$keyword=urldecode($keyword);
		$listRows = 20;
		//sql合并
		$sqlwhere_parameter=" zn_type=0 and zn_user_type=2 and zc_folder='file' and zn_uid=".session('member_auth')["id"];//sql条件
		if($keyword){
			$sqlwhere_parameter.=" and zc_original_name like '".$keyword."%'";
		}
		if(session('admin_auth')["id"]!=1){
		$sqlwhere_parameter.=" zn_uid=".session('admin_auth')["id"];//sql条件
		}
        $count = $this->Attachment->listCount($sqlwhere_parameter);
		$page_config = array(
				'field'=>"`id`,`zc_account`,`zn_type`,`zc_file_path`,`zc_original_name`,`zc_suffix`,`zn_cdate`",
				'where'=>$sqlwhere_parameter,
				'order'=>'id DESC',
		);
		//列表表单初始化****end
        $arr=array();
		$list = $this->Attachment->lqList($start,$listRows,$page_config);
		foreach($list as $key=>$value) $arr[] = array("url"=>$value["zc_file_path"],"title"=>$value["zc_original_name"],"mtime"=>$value["zn_cdate"]);
		$this->ajaxReturn(array("state"=>"SUCCESS","list"=>$arr,"start"=>$start,"total"=>$count));
				
    }		

}
?>