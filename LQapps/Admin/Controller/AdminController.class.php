<?php //管理员 Admin 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class AdminController extends PublicController{
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'用户信息',2=>'个人信息'),
		//信息
		'1'=>array(
		array('text', 'zc_account', "用户帐号",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('password', 'zc_password', "用户密码",1,'{"required":"1","dataType":"password","dataLength":"","readonly":0,"disabled":0}'),
		array('password', 'zc_password_chk', "确认密码",0,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"confirm":"zc_password"}'),
		array('text', 'zc_email', "用户邮箱",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_sort', "排序",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),				   
		),
		//信息
		'2'=>array(
		array('text', 'zc_name', "姓名",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('select', 'zn_role_id', "用户角色",1,'{"class":"mb5","required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('text', 'zc_mobile', "紧急联系电话",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		),
	);
    /** 初始化*/
    public function __construct() {
		parent::__construct();
	}
    
	//列表页
    public function index() {
		//列表表单初始化****start
		$page_parameter["s"]=$this->getSafeData('s');
		$this->reSearchPara($page_parameter["s"]);//反回搜索数据
		$search_content_array=array(
			'pagesize'=>urldecode(I('get.pagesize','0','int')),
			'fkeyword'=>trim(urldecode(I('get.fkeyword',$this->keywordDefault))),
			'keymode'=>urldecode(I('get.keymode','1','int')),
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值

		//sql合并s
		$sqlwhere_parameter=" 1 ";//sql条件
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_account ='".$search_content_array["fkeyword"]."'";
			}else{
			$sqlwhere_parameter.=" and zc_account like'%".$search_content_array["fkeyword"]."%'";
			}			
		}
		//sql合并e
		
		//首页设置s
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'id'=>L("LIST_ID"),'zc_account'=>'帐户名','zn_role_id'=>'帐户角色','zc_email'=>'邮箱','zc_mobile'=>'联系电话','zn_sort'=>L("LIST_SOTR"),'member_login_clear'=>'尝试','status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zc_account`,`zc_name`,`zn_role_id`,`zc_email`,`zc_mobile`,`zn_sort`,`zl_visible`",
				'where'=>$sqlwhere_parameter,
				'order'=>'zn_sort,id DESC',
				'title'=>$page_title,
				'thinkphpurl'=>__CONTROLLER__."/",
		);			
		//列表表单初始化****end
		
        $count = $this->UserModel->apiListCount($sqlwhere_parameter);
		$page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->UserModel->apiListAdmin($page->firstRow, $page->listRows,$page_config));
		$this->assign('empty_msg',$this->tableEmptyMsg(count($page_title)));
		$this->assign("page_config",$page_config);//列表设置赋值模板
        $this->display();
    }
	
	// 插入/添加
    public function add() {
        if (IS_POST) {
			$data = $this->UserModel->apiInsertAdmin();
		     if(preg_match('/^([1-9]\d*)$/',$data)){
				$this->ajaxReturn( array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"],'url' => U(CONTROLLER_NAME.'/add')));
			}else{
				$this->ajaxReturn(array('status' => 0, 'msg' => $data));
			}
        } else {
			$lcdisplay='Public/common-edit';//引用模板
			
			//表单数据初始化s
			$form_array=lq_post_memory_data();//获得上次表单的记忆数据
			$form_array["id"]='';
			$form_array["zc_account"]=lq_random_string(7,2)."@163.com";
			$form_array["zc_password"]='123456';
			$form_array["zc_password_chk"]='123456';
			$form_array["zc_email"]=rand(100000000,999999999)."@qq.com";
			$form_array["zc_mobile"]='134'.rand(10000000,99999999);
			$form_array["zc_name"]='张好人';	
			$form_array["zn_sort"]=C("COM_SORT_NUM");
			$form_array["zn_role_id_data"]=lq_return_array_one(F('admin_role','',COMMON_ARRAY),'id','title');
			$Form=new Form($this->myForm,$form_array,$this->UserModel->apiGetCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s
			
            $this->display($lcdisplay);
        }
    }
	
	// 更新/编辑
    public function edit() {
        if (IS_POST) {
			$data = $this->UserModel->apiUpdateAdmin();
		     if(preg_match('/^([1-9]\d*)$/',$data)){
				$this->ajaxReturn( array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"],'url' => U(CONTROLLER_NAME.'/edit/tnid/'.$data)));
			}else{
				$this->ajaxReturn(array('status' => 0, 'msg' => $data));
			}
        } else {
			$lcdisplay='Public/common-edit';

			//读取数据
			$data = $this->UserModel->apiGetInfoByID($this->lqgetid);
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["zn_role_id_data"]=lq_return_array_one(F('admin_role','',COMMON_ARRAY),'id','title');
			$this->myForm[1][0]=array('text', 'zc_account', "用户帐号",0,'{"required":"1","dataType":"","dataLength":"","readonly":1,"disabled":1}');
			unset($this->myForm[1][1]);
			unset($this->myForm[1][2]);
			unset($this->myForm[1][3]);
			$Form=new Form($this->myForm,$form_array,$this->UserModel->apiGetCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化e

            $this->display($lcdisplay);
        }
    }


	//管理员管理 - 修改密码
    public function editPass() {
        if (IS_POST) {
			 $data = $this->UserModel->apiEditPass();
		     if(preg_match('/^([1-9]\d*)$/',$data)){
				$this->ajaxReturn( array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"],'url' => U(CONTROLLER_NAME.'/index')));
			}else{
				$this->ajaxReturn(array('status' => 0, 'msg' => $data));
			}
        } else {
			$lcdisplay='edit-password';
			
			//读取数据
			$data = $this->UserModel->apiGetInfoByID($this->lqgetid);
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			
			//表单数据初始化s
			$form_array=array();
			//操作时间
			$form_array["os_record_time"]=$this->osRecordTime($data);
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$this->assign("LQFdata",$form_array);//表单数据
            $this->display($lcdisplay);
        }
    }	
	

	//设置权限验证
	public function opIsOriginalAdmin() {
		$tnid=$this->lqgetid;
		if($tnid==1) $this->ajaxReturn(array('status' => 0, 'msg' => "原始用户不能操作。" ));
		$this->ajaxReturn(array('status' => 1, 'msg' => 'ok' ));
	}	

	//设置权限页面
	public function setPopedomWindow() {
        if (IS_POST) {
			 $data = $this->UserModel->apiUpdatePopedom();
		     if(preg_match('/^([1-9]\d*)$/',$data)){
				$this->ajaxReturn( array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"],'url' => U(CONTROLLER_NAME.'/setPopedomWindow/tnid/'.$data)));
			}else{
				$this->ajaxReturn(array('status' => 0, 'msg' => $data));
			}
			
        } else {
			//读取数据
			$data = $this->UserModel->apiGetInfoByID($this->lqgetid);
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录

			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			
			$form_array["last_login_time"]=lq_cdate($data["zn_last_login_time"]);
			$form_array["cdate"]=lq_cdate($data["zn_cdate"]);
			$form_array["zn_role_id_string"]=lqCreatOption(lq_return_array_one(F('admin_role','',COMMON_ARRAY),'id','title'),$form_array["zn_role_id"],'请选择角色');
			$this->assign("LQFdata",$form_array);//表单数据
			$list=F('SystemMenu','',COMMON_ARRAY);
			
			$listtree=$popedom=array();
			if($data["zc_popedom"]) $popedom = explode(",",$data["zc_popedom"]);
			foreach ($list as $lnKey => $laValue) {
				if($laValue["zl_visible"]){
					if(in_array($laValue["id"],$popedom)){
						$laValue["checked"]='true';
					}else{
						$laValue["checked"]='false';
					}
					$listtree[]=$laValue;
				}
			}
			$this->assign("listtree",$listtree);
			$lcdisplay='set-popedom-window';//引用模板
			$this->display($lcdisplay);
			
		}
	}
	//更改字段值 
    public function opProperty() {
        $this->ajaxReturn($this->UserModel->apiProperty());
    }
	//更改  zlvisible 
    public function opVisible() {
        $this->ajaxReturn($this->UserModel->apiVisible());
    }
	//单记录删除 
    public function opDelete() {
        $this->ajaxReturn($this->UserModel->apiDelete());
    }	
	//多记录删除
    public function opDeleteCheckbox() {
        $this->ajaxReturn($this->UserModel->apiDeleteCheckbox());
    }		

}
?>