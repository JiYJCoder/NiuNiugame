<?php
/*
 * 后台公共Controller
 * Author：国雾院theone（438675036@qq.com）
 * Date:2013-06-27
 A 的命名 - 方法命名
 页面 list , edit , images , sort_list
 */
namespace Admin\Controller;
use LQPublic\Controller\Base;
use User\Api\AdminApi as AdminApi;
use Attachment\Api\AttachmentApi as AttachmentApi;

class PublicController extends Base{
	//get方法tnid、post方法tnid//请输入关键字
	public $UserModel,$login_admin_info,$lqgetid,$lqpostid,$keywordDefault,$C_D,$pcTable,$index_lock,$set_config;
	
    /* 保存禁止通过url访问的公共方法,例如定义在控制器中的工具方法 ;deny优先级高于allow*/
    static protected $deny  = array();

    /* 保存允许访问的公共方法 */
    static protected $allow = array();
	
    /**
      +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::__construct();
      +----------------------------------------------------------
     */
    public function __construct() {
		parent::__construct();
		/* 实例化用户Model */
		$this->UserModel = new AdminApi;
		//免读金牌D方法
		$d_array=array("Index","Export","Sms");		
		if(!in_array(CONTROLLER_NAME,$d_array)){
			$this->C_D=D(CONTROLLER_NAME);//实例D方法
			$this->pcTable=CONTROLLER_TO_TABLE(CONTROLLER_NAME);//数据表名称
		}else{
			if(!in_array(CONTROLLER_NAME,array("Member","Admin","Attachment"))){
			$this->pcTable=CONTROLLER_TO_TABLE(CONTROLLER_NAME);//数据表名称
			}
		}
		//action收集器
		if(ACTION_OPEN==1) self::actionInsert();
		
		//免死金牌
		$action_no_login_array=array("images","tree","opMultiImageUp");
		//上传模块：根据地址栏重构 当前用户登录信息
		if(!in_array(ACTION_NAME,$action_no_login_array) ){
			self::checkLogin();//用户认证
		}
		$this->commonAssign();//页面公共标签赋值
		//缓存数据
		if($this->getSafeData('clearcache')){
			//注册允许首页缓存操作的文件
			$cacheArray=array("AdminAction","AdminAllowIp","SystemMenu","AdminRole","Region","WebConfig","ProductCat","ArticleCat",
			"ArticleCat","AdPosition","HdAttribute");
			if(in_array(CONTROLLER_NAME,$cacheArray))$this->C_D->lqCacheData(0);
		}				
		$this->set_config=F('set_config','',COMMON_ARRAY);
		$this->assign("SET_CONFIG",$this->set_config);//基本设置
    }
	
	//空白页
	public function blank(){
		
	}

	//用户认证**************************************************
	private function checkLogin() {		
        if(!lq_is_login()){
                $this->redirect('Login/login');
        }else{		
			$this->login_admin_info = $this->UserModel->apiGetInfo(session('admin_auth')["id"]);
			$this->assign("login_admin_info",$this->login_admin_info);

			//系统菜单 s
			$this->assign("system_top_menu",$this->login_admin_info["zc_popedom"]["system"]);		
			$this->assign("system_left_menu",$this->login_admin_info["zc_popedom"]["menu"]);//左则菜单-列表
			//系统菜单 e				
			
			// 检测节点访问权限
			$node_no_check_array=array('Index','Attachment');
			$access=99;
			if( !in_array(CONTROLLER_NAME,$node_no_check_array) ){
				$access = $this->UserModel->apiAccessControl($this->login_admin_info);
			}
			if($access<1){
				switch ($access) {
					case 0:
					$this->error('对不起，您未授权访问该页面！'.$this->systemMsg, U("/Index"));
					break;	
					case -1: //权值表为空或
					$this->error('对不起，系统维护中！'.$this->systemMsg, U("/Index"));
					break;	
					case -2://404:无页面
					$this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->systemMsg, U("/Index"));
					break;
					case -3://
					$this->error('对不起，403:禁止访问！'.$this->systemMsg, U("/Index"));
					break;
					case -4://
       			    $this->ajaxReturn(array('status' => 0, 'msg' => "对不起，该操作您未被授权！"));
					break;									
				}
			}
		 }
	}

	//默认页面 s
	public function sort() {
		$this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->systemMsg, U("/Index"));
	}
	//默认页面 e
	
	//action收集器
	private function actionInsert() {
		if( !M("admin_action")->where("zc_action_key='".ACTION_NAME."'")->getField('id') ){
			M()->execute( "INSERT INTO __PREFIX__admin_action (`zc_action_key`,`zc_caption`,`zn_cdate`,`zn_mdate`) VALUES ('".ACTION_NAME."','".ACTION_NAME."','".NOW_TIME."','".NOW_TIME."')");
		}
	}	
	
	
	//***********************页面常用操作************start**************************************************
	//上下分页 
    protected function pagePrevNext($M_PAGE,$id,$title) {
			$data_prev=$M_PAGE->field("`$id`,`$title`")->where("$id>" .$this->lqgetid)->order("`$id` ASC")->limit("0,1")->select();
			$data_next=$M_PAGE->field("`$id`,`$title`")->where("$id<" .$this->lqgetid)->order("`$id` DESC")->limit("0,1")->select();
			$data_up_down_page='';
			if($data_prev){
				$data_up_down_page.='<li><a href="'.__ACTION__."/tnid/".$data_prev[0]["$id"].'" title="上一条：'.lq_kill_html($data_prev[0]["$title"],20).'"><i class="fa fa-arrow-circle-left"></i> 上一条</a></li>';
			}else{
				$data_up_down_page.='<li class="line-th"><a href="javasrctpt:;" title="空记录"><i class="fa fa-arrow-circle-left"></i> 上一条</a></li>';
			}
			if($data_next){
				$data_up_down_page.='<li><a href="'.__ACTION__."/tnid/".$data_next[0]["$id"].'" title="下一条：'.lq_kill_html($data_next[0]["$title"],20).'"><i class="fa fa-arrow-circle-right"></i> 下一条</a></li>';
			}else{
				$data_up_down_page.='<li class="line-th"><a href="javasrctpt:;" title="空记录"><i class="fa fa-arrow-circle-right"></i> 下一条</a></li>';
			}
			$this->assign("data_up_down_page",$data_up_down_page);
    }
	
	//单记录审批
    public function opVisible($is_tree=0) {
        $this->ajaxReturn($this->C_D->lqVisible($is_tree));
    }

	//单记录更改Sort 
    public function opSort() {
        $this->ajaxReturn($this->C_D->lqSort());
    }
	
	//更改  Sortlist 列表 
    public function opSortList() {
        $this->ajaxReturn($this->C_D->lqSortList());
    }	
	
	//单记录更改Label 
    public function opLabel() {
        $this->ajaxReturn($this->C_D->lqLabel());
    }

	//单记录更改字段值 
    public function opProperty(){
    	$this->ajaxReturn($this->C_D->lqProperty());
    }    

	//单记录删除 
    public function opDelete($is_tree=0) {
        $this->ajaxReturn($this->C_D->lqDelete($is_tree));
    }	
	
	//多记录删除
    public function opDeleteCheckbox() {
        $this->ajaxReturn($this->C_D->lqDeleteCheckbox());
    }		

	//多记录审批 
    public function opVisibleCheckbox() {
        $this->ajaxReturn($this->C_D->lqVisibleCheckbox());
    }

	
    //单文件上传 *********************************************************************************
    public function opUploadImages() {
		ob_end_clean();
		//上传控件 ID
		$fileid=I("get.fileid",'');
		//file表单控件名
		$file_widget=$_FILES["file_".$fileid];
		if($file_widget['size']==0){
			die("<script type='text/javascript'>parent.util.uploadImagesCallback('上传的文件不存在或为空',false);top.layer.closeAll();</script>");
		}		
		//文件类型
		$type=I("get.type",'images');
		if(!array_key_exists($type,C("UPLOAD_EXT"))){
			die("<script type='text/javascript'>parent.util.uploadImagesCallback('上传的目录不存在...',false);top.layer.closeAll();</script>");
		}

		$upload = new \Think\Upload();// 实例化上传类	
		$upload->rootPath='./'.C("UPLOAD_PATH")["folder"];//文件上传保存的根路径
		$upload->maxSize  = C("UPLOAD_MAX_SIZE")[$type];// 设置附件上传大小
		$upload->exts  = C("UPLOAD_EXT")[$type] ;// 设置附件上传类型
		$upload->savePath =  C("UPLOAD_PATH")["list"][$type];//上传目录
		$upload->subName =  array('date', 'Ymd');//上传目录
		$water_open=C("UPLOAD_WATER")[$type];
		if($upfile_info = $upload->uploadOne($file_widget)) {// 上传错误提示错误信息
			 $Attachment = new AttachmentApi;
			 $lc_table="attachment";
			 $lc_folder_path=$upload->rootPath.$upfile_info["savepath"];
			 $lc_folder_path=substr($lc_folder_path,1);
			 $upfile_data=array(
							  'zn_uid'=>intval(session('admin_auth')["id"]),
							  'zc_account'=>session('admin_auth')["zc_account"],	
							  'zc_table'=>$lc_table,
							  'zc_controller'=>CONTROLLER_NAME,
							  'zn_type'=>in_array($upfile_info["ext"], array('jpg', 'gif', 'png', 'jpeg')) ? 0 : 1,
							  'zn_user_type'=>1,
							  'zc_original_name'=>str_replace(".".$upfile_info["ext"],"",$upfile_info["name"]),
							  'zc_sys_name'=>$upfile_info["savename"],
							  'zc_folder_path'=>$lc_folder_path,
							  'zc_file_path'=>$lc_folder_path.$upfile_info["savename"],
							  'zc_suffix'=>strtolower($upfile_info['ext']),
							  'zn_size'=>$upfile_info["size"],
							  'zc_folder'=>$type,
							  'zn_day'=>strtotime(C("LQ_TIME_DAY")),
							  'zn_cdate'=>NOW_TIME
			 );	
			 $lnLastInsID=$Attachment->insertAttachment($lc_table,$upfile_data);
					//写入日志
					$log_data=array(
							'id'=>$lnLastInsID,
							'action'=>"add",
							'table'=>"attachment",
							'url'=>$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],
							'operator'=>session('admin_auth')["id"],
					);	
					$this->UserModel->addAdminLog($log_data);
				
				
			 echo "<script>parent.util.uploadImagesCallback('".$upfile_data["zc_file_path"]."',true,'".$fileid."');top.layer.closeAll();</script>";
		
		}else{
			  echo "<script>parent.util.uploadImagesCallback('".$upload->getError()."',false);top.layer.closeAll();</script>";
		}
    }
	
	
	//编辑器上传
    public function opUploadEditor() {
		ob_end_clean();
		$isfile=I("get.isfile",'0','int');//操作标识
		
		//上传控件 ID
		$file_widget=$_FILES["upfile"];
		if($file_widget['size']==0)$this->ajaxReturn(array('state' =>'上传的文件不存在或为空'));	
		
		//文件类型
		if($isfile==1){
		$type='file';
		}else{
		$type='editorfile';
		}
		if(!array_key_exists($type,C("UPLOAD_EXT")))$this->ajaxReturn(array('state' =>'上传的目录不存在'));
		
			$upload = new \Think\Upload();// 实例化上传类	
			$upload->rootPath='./'.C("UPLOAD_PATH")["folder"];//文件上传保存的根路径
			$upload->maxSize  = C("UPLOAD_MAX_SIZE")[$type];// 设置附件上传大小
			$upload->exts  = C("UPLOAD_EXT")[$type] ;// 设置附件上传类型
			$upload->savePath =  C("UPLOAD_PATH")["list"][$type];//上传目录
			$upload->subName =  array('date', 'Ymd');//上传目录
			if($upfile_info = $upload->uploadOne($file_widget)) {
	
				 $Attachment = new AttachmentApi;
				 $lc_table="attachment";
				 $lc_folder_path=$upload->rootPath.$upfile_info["savepath"];
				 $lc_folder_path=substr($lc_folder_path,1);
				 $upfile_data=array(
								  'zn_uid'=>intval(session('admin_auth')["id"]),
								  'zc_account'=>session('admin_auth')["zc_account"],	
								  'zc_table'=>$lc_table,
								  'zc_controller'=>CONTROLLER_NAME,
								  'zn_type'=>in_array($upfile_info["ext"], array('jpg', 'gif', 'png', 'jpeg')) ? 0 : 1,
								  'zn_user_type'=>1,
								  'zc_original_name'=>str_replace(".".$upfile_info["ext"],"",$upfile_info["name"]),
								  'zc_sys_name'=>$upfile_info["savename"],
								  'zc_folder_path'=>$lc_folder_path,
								  'zc_file_path'=>$lc_folder_path.$upfile_info["savename"],
								  'zc_suffix'=>strtolower($upfile_info['ext']),
								  'zn_size'=>$upfile_info["size"],
								  'zc_folder'=>$type,
								  'zn_day'=>strtotime(C("LQ_TIME_DAY")),
								  'zn_cdate'=>NOW_TIME
				 );	
				$lnLastInsID=$Attachment->insertAttachment($lc_table,$upfile_data);
				//写入日志
				$log_data=array('id'=>$lnLastInsID,'action'=>"add",'table'=>"attachment",'url'=>$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],'operator'=>$upfile_data["zn_uid"],);	
				$this->UserModel->addAdminLog($log_data);			 
	
				//返回数据
				$this->ajaxReturn(array('state' =>'SUCCESS','url' =>$upfile_data["zc_file_path"],'title' =>$upfile_data["zc_sys_name"],'original' =>$upfile_data["zc_original_name"],'type' => '.'.$upfile_data["zc_suffix"],'size' =>$upfile_data["zn_size"]));
			}else{
				$this->ajaxReturn(array('state' =>$upload->getError()));
			}
    }


	//多图片上传 *********************************************************************************
    public function opMultiImageUp() {
		ob_end_clean();
		//上传控件 ID
		$uid=I("get.uid",'');
		$token=I("get.token",'');
		$fileid=I("get.fileid",'');
		$msg='未知错误！';
		//file表单控件名
		$file_widget=$_FILES['imgFile'];
		if($file_widget['size']==0){
			$this->ajaxReturn(array('error' => 1, 'message' => '上传的文件不存在或为空'));
		}		
		//文件类型
		$type=I("get.type",'images');
		if(!array_key_exists($type,C("UPLOAD_EXT"))){
			$this->ajaxReturn(array('error' => 1, 'message' => '上传的目录不存在'));
		}
		//授权
		$admin_info = $this->UserModel->apiGetInfoByID($uid);
        $auth = array(
            'id'             => $admin_info["id"],
            'zc_account'        => $admin_info["zc_account"],
            'zn_last_login_time' => $admin_info["zn_last_login_time"],
        );
		$admin_auth_sign=lq_data_auth_sign($auth);
		
		//火狐登录认证
		if($token===$admin_auth_sign){
			session('admin_auth',$auth);
			session('admin_auth_sign',$admin_auth_sign);			
			
			$upload = new \Think\Upload();// 实例化上传类	
			$upload->rootPath='./'.C("UPLOAD_PATH")["folder"];//文件上传保存的根路径
			$upload->maxSize  = C("UPLOAD_MAX_SIZE")[$type];// 设置附件上传大小
			$upload->exts  = C("UPLOAD_EXT")[$type] ;// 设置附件上传类型
			$upload->savePath =  C("UPLOAD_PATH")["list"][$type];//上传目录
			$upload->subName =  array('date', 'Ymd');//上传目录
			if($upfile_info = $upload->uploadOne($file_widget)) {
	
				 $Attachment = new AttachmentApi;
				 $lc_table="attachment";
				 $lc_folder_path=$upload->rootPath.$upfile_info["savepath"];
				 $lc_folder_path=substr($lc_folder_path,1);
				 $upfile_data=array(
								  'zn_uid'=>intval($admin_info ["id"]),
								  'zc_account'=>$admin_info ["zc_account"],	
								  'zc_table'=>$lc_table,
								  'zc_controller'=>CONTROLLER_NAME,
								  'zn_type'=>in_array($upfile_info["ext"], array('jpg', 'gif', 'png', 'jpeg')) ? 0 : 1,
								  'zn_user_type'=>1,
								  'zc_original_name'=>str_replace(".".$upfile_info["ext"],"",$upfile_info["name"]),
								  'zc_sys_name'=>$upfile_info["savename"],
								  'zc_folder_path'=>$lc_folder_path,
								  'zc_file_path'=>$lc_folder_path.$upfile_info["savename"],
								  'zc_suffix'=>strtolower($upfile_info['ext']),
								  'zn_size'=>$upfile_info["size"],
								  'zc_folder'=>$type,
								  'zn_day'=>strtotime(C("LQ_TIME_DAY")),
								  'zn_cdate'=>NOW_TIME
				 );	
				 $lnLastInsID=$Attachment->insertAttachment($lc_table,$upfile_data);
				 //写入日志
				 $log_data=array(
								'id'=>$lnLastInsID,
								'action'=>"add",
								'table'=>"attachment",
								'url'=>$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],
								'operator'=>$admin_info ["id"],
				);	
				 $this->UserModel->addAdminLog($log_data);			 
	
				 //返回数据67
				 $this->ajaxReturn(array('error' => 0,'id' =>$lnLastInsID,'url' =>$upfile_data["zc_file_path"]));
			}else{
				 $this->ajaxReturn(array('error' => 1, 'message' =>$upload->getError() ));
			}
		}else{
			$this->ajaxReturn(array('error' => 1, 'message' => '用户未登录'));
		}
    }    
	//***********************页面常用操作************end**************************************************


	//列表页的空记录展示
	protected function tableEmptyMsg($colspan){
		return '<tr><td colspan="'.$colspan.'" align="center"><span id="null_record">'.L("LABEL_NULL_RECORD").'</span></td></tr>';	
	}

	//修改页-记录展示
	protected function osRecordTime($data){
		return '<span class="os-record-time">记录ID['.$data["id"].'],插入时间:'.lq_cdate_format($data["zn_cdate"]).',更新时间:'.lq_cdate_format($data["zn_mdate"]).'</span>';
	}
	

	//页面公共标签赋值
	private function commonAssign(){
		$this->lqgetid=isset($_GET["tnid"])?intval($_GET["tnid"]):0;
		$this->lqpostid=isset($_POST["fromid"])?intval($_POST["fromid"]):0;
		//过滤直接访问public
		if(CONTROLLER_NAME=='Public') $this->redirect('Index/index');
		
		//html显示 ******************************** s
		$this->assign("lq_form_save", 'LQForm');//添加、编辑
		$this->assign("lq_form_list", 'LQFormList');//列表 - 表单		
		$this->keywordDefault='请输入关键字...';
		$this->assign("keywordDefault",$this->keywordDefault);
		//html显示 ******************************** e		
		
		//操作
		switch (ACTION_NAME) {
			case "desktop":
				$label_location='桌面';
				lq_post_memory_data(1);
			break;
			case "modifyMyself":
				$label_location='修改用户信息';
			break;					
			case "clearCache":
				$label_location='清除缓存';
			break;
			case "index":
				$label_location='列表页面';
				lq_post_memory_data(1);
			break;
			case "add":
				$label_location='添加页面';
			break;
			case "edit":
				$label_location='编辑页面';
				$current_url= U(CONTROLLER_NAME."/index");
				if(session('index_current_url')){
						$current_url= session('index_current_url');
				}
				$this->assign("edit_index_url",$current_url); //通用按钮
			break;			
			case "editPass":
				$label_location='修改密码';
				$current_url= U(CONTROLLER_NAME."/index");
				if(session('index_current_url')){
						$current_url= session('index_current_url');
				}				
				$this->assign("edit_index_url",$current_url); //通用按钮			
			break;
			case "sort":
				$label_location='排序页面';
			break;			
			case "view":
				$label_location='详情页面';
			break;				
			default:
				$label_location='无定义页面';
			    //操作页 - json_encode须有数据输出
				//header("Content-Type:text/html; charset=utf-8");
				//header('Content-Type:application/json; charset=utf-8');				
		}


		#当前标题/当前路径
		if(CONTROLLER_NAME=='Index'){
				$lc_sys_current='<span>当前位置：</span><ul class="placeul"><li><a href="'.U("Index/index").'" title="">'.L("SYS_HOME").'</a></li><li class="current">'.$label_location.'</li></ul>';
			   //当前标题
			   $this->assign("sys_heading", $label_location);
			   //当前路径
			   $this->assign("sys_current", $lc_sys_current);				   
			   
		}else{
			//读缓存
			$data_sys_current = F($this->pcTable.C("S_PREFIX").'data_sys_current','',C(SYSTEM_MENU_CURRENT));

//pr($this->pcTable.C("S_PREFIX").'data_sys_current','',C(SYSTEM_MENU_CURRENT));
			if($data_sys_current){
				$lclocation=F($this->pcTable.C("S_PREFIX").'syslocation',"",C(SYSTEM_MENU_CURRENT));
				$lc_sys_current=lqSysmuenLocation($lclocation);
				//当前标题
				$this->assign("sys_heading", $data_sys_current["zc_caption"].".".$label_location);
				//当前路径
				$this->assign("sys_current", $lc_sys_current);		
			}
			
			//列表表单初始化  1开锁/0开锁  s
			if($data_sys_current["zc_index_lock"]){

				$os_lock = explode("|",$data_sys_current["zc_index_lock"]);
				$this->index_lock=array(
						'edit'=>$os_lock[0],//列表编辑锁
						'delete'=>$os_lock[1],//列表删除锁
						'visible'=>$os_lock[2],//列表审批锁
						'cache'=>$os_lock[3],//列表缓存锁
						'search'=>$os_lock[4],//列表查找锁
						'sort'=>$os_lock[5],//列表排序锁						
				);
				$this->assign("os_lock",$this->index_lock);//列表锁
			}
			//列表表单初始化  1开锁/0开锁  e
		}
	}

}
?>