<?php
/*
 * 会员管理中心公共Controller
 * Author：国雾院theone（438675036@qq.com）
 * Date:2013-06-27
 A 的命名 - 方法命名
 页面 list , edit , images , sort_list
 */
namespace Ucenter\Controller;
use LQPublic\Controller\Base;
use Member\Api\MemberApi as MemberApi;
use Attachment\Api\AttachmentApi as AttachmentApi;

class PublicController extends Base{
	public $model_member,$login_member_info,$lqgetid,$lqpostid,$keywordDefault,$C_D,$action_no_login_array;
    public function __construct() {
		parent::__construct();
		$this->model_member = new MemberApi;
		
		//免死金牌
		$this->action_no_login_array=array("images","op_multi_image_up");
		//上传模块：根据地址栏重构 当前用户登录信息
		if(!in_array(ACTION_NAME,$this->action_no_login_array,false)){
			self::checkLogin();//用户认证
		}		
		$this->commonAssign();//页面公共标签赋值
    }
	
	//空白页
	public function blank(){$this->redirect('Ucenter/Index/index');}

	//用户认证**************************************************
	protected function checkLogin() {		
        if(!lq_is_login('member')){
                $this->redirect('Ucenter/Login/index');
        }else{
			$this->login_member_info = $this->model_member->apiGetInfo(session('member_auth')["id"]);
			if($this->login_member_info["zc_headimg"]){
				if(substr($this->login_member_info["zc_headimg"],0,4)=='http'){
					$this->login_member_info["zc_headimg"]=$this->login_member_info["zc_headimg"];
				}else{
					$this->login_member_info["zc_headimg"]=API_DOMAIN.$this->login_member_info["zc_headimg_thumb"];
				}
			}else{
					$this->login_member_info["zc_headimg"]=NO_HEADIMG;
			}
			$this->assign("login_member_info",$this->login_member_info);	
		 }
	}
	//设计师认证
	protected function isDesigner(){ 
		if($this->login_member_info["zl_role"]!=1) $this->redirect('Ucenter/Index/index');
	}
	//银行认证
	protected function isBank(){
		if($this->login_member_info["zl_role"]!=6) $this->redirect('Ucenter/Index/index');
    }

	//更改 Label 
    public function op_label() {
        $this->ajaxReturn($this->C_D->lqLabel());
    }

	//单记录删除 
    public function op_delete($is_tree=0) {
        $this->ajaxReturn($this->C_D->lqDelete($is_tree));
    }	
	
	//多记录删除
    public function op_delete_checkbox() {
        $this->ajaxReturn($this->C_D->lqDeleteCheckbox());
    }	
	
	//列表页的空记录展示
	protected function tableEmptyMsg($colspan){
		return '<tr><td colspan="'.$colspan.'" align="center"><span id="null_record">'.L("LABEL_NULL_RECORD").'</span></td></tr>';	
	}

	//修改页-记录展示
	protected function osRecordTime($data){
		return '<span class="os-record-time">记录ID['.$data["id"].'],插入时间:'.lq_cdate_format($data["zn_cdate"]).',更新时间:'.lq_cdate_format($data["zn_mdate"]).'</span>';
	}
	

    //单文件上传 *********************************************************************************
    public function op_upload_images() {
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
							  'zn_uid'=>intval(session('member_auth')["id"]),
							  'zc_account'=>session('member_auth')["zc_account"],	
							  'zc_table'=>$lc_table,
							  'zc_controller'=>CONTROLLER_NAME,
							  'zn_type'=>in_array($upfile_info["ext"], array('jpg', 'gif', 'png', 'jpeg')) ? 0 : 1,
							  'zn_user_type'=>2,
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
			 $this->model_member->addMemberLog("upload_image",'',$lnLastInsID);
			 echo "<script>parent.util.uploadImagesCallback('".$upfile_data["zc_file_path"]."',true,'".$fileid."');top.layer.closeAll();</script>";
		
		}else{
			  echo "<script>parent.util.uploadImagesCallback('".$upload->getError()."',false);top.layer.closeAll();</script>";
		}
    }
	
	//编辑器图片上传
    public function op_upload_editor() {
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
								  'zn_uid'=>intval(session('member_auth')["id"]),
								  'zc_account'=>session('member_auth')["zc_account"],	
								  'zc_table'=>$lc_table,
								  'zc_controller'=>CONTROLLER_NAME,
								  'zn_type'=>in_array($upfile_info["ext"], array('jpg', 'gif', 'png', 'jpeg')) ? 0 : 1,
								  'zn_user_type'=>2,
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
				 $this->model_member->addMemberLog("upload_image",'',$lnLastInsID);
	
				//返回数据
				$this->ajaxReturn(array('state' =>'SUCCESS','url' =>$upfile_data["zc_file_path"],'title' =>$upfile_data["zc_sys_name"],'original' =>$upfile_data["zc_original_name"],'type' => '.'.$upfile_data["zc_suffix"],'size' =>$upfile_data["zn_size"]));
			}else{
				$this->ajaxReturn(array('state' =>$upload->getError()));
			}		
    }
	
	//多图片上传 *********************************************************************************
    public function op_multi_image_up() {
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
		$member_info = $this->model_member->apiGetInfoByID($uid);
        $auth = array(
            'id'             => $member_info["id"],
            'zc_account'        => $member_info["zc_account"],
            'zl_role'        => $member_info["zl_role"],
            'zn_last_login_time' => $member_info["zn_last_login_time"],
        );
		$member_auth_sign=lq_data_auth_sign($auth);
		
		//火狐登录认证
		if($token===$member_auth_sign){
			session('member_auth', $auth);
			session('member_auth_sign',$member_auth_sign);			
		
			
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
								  'zn_uid'=>intval($member_info["id"]),
								  'zc_account'=>$member_info["zc_account"],	
								  'zc_table'=>$lc_table,
								  'zc_controller'=>CONTROLLER_NAME,
								  'zn_type'=>in_array($upfile_info["ext"], array('jpg', 'gif', 'png', 'jpeg')) ? 0 : 1,
								  'zn_user_type'=>2,
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
				 $this->model_member->addMemberLog("upload_image",'',$lnLastInsID);
				 //返回数据
				 $this->ajaxReturn(array('error' => 0,'id' =>$lnLastInsID,'url' =>$upfile_data["zc_file_path"]));
			}else{
				 $this->ajaxReturn(array('error' => 1, 'message' =>$upload->getError() ));
			}
		}else{
			$this->ajaxReturn(array('error' => 1, 'message' => '用户未登录'));
		}
    }    	
	
	//***********************页面常用操作************end**************************************************


	//页面公共标签赋值
	private function commonAssign(){
		$this->lqgetid=isset($_GET["tnid"])?intval($_GET["tnid"]):0;
		$this->lqpostid=isset($_POST["fromid"])?intval($_POST["fromid"]):0;
		//过滤直接访问public
		if(CONTROLLER_NAME=='Public') $this->redirect('Ucenter/Index/index');
		
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
				$this->assign("edit_index_url",U(CONTROLLER_NAME."/index")); //通用按钮
			break;
			case "edit":
				$label_location='编辑页面';
				$current_url= U(CONTROLLER_NAME."/index");
				if(session('index_current_url')){
						$current_url= session('index_current_url');
				}
				$this->assign("edit_index_url",$current_url); //通用按钮
			break;			
			case "edit_pass":
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
		}
		
		
		//菜单页面
		$current_controller_name=strtolower(CONTROLLER_NAME);
		$sys_menu=array();
		if($this->login_member_info["zl_role"]==1){//设计师菜单
			$top_menu=C("DESIGNER_MENU")["top_menu"];
			foreach($top_menu as $k=>$v){
				if($k==$current_controller_name){
				$top_menu[$k]["css"]='class="active"';	
				}else{
				$top_menu[$k]["css"]='';	
				}
				$top_menu[$k]["url"]=U($v["url"]);
			}
			$sys_menu["top"]=$top_menu;
			$sys_menu["top_name"]=$top_menu["$current_controller_name"]["title"];
			$sys_menu["current_controller"]=$current_controller_name;
			
			if($current_controller_name=="index"){
				$left_menu_1=C("DESIGNER_MENU")["index"];
				foreach($left_menu_1 as $k=>$v) $left_menu_1[$k]["url"]=U($v["url"]);
				$left_menu[1]=array("title"=>$top_menu["index"]["title"],'list'=>$left_menu_1);
				$left_menu_2=C("DESIGNER_MENU")["member"];
				foreach($left_menu_2 as $k=>$v) $left_menu_2[$k]["url"]=U($v["url"]);
				$left_menu[2]=array("title"=>$top_menu["member"]["title"],'list'=>$left_menu_2);
				$left_menu_3=C("DESIGNER_MENU")["works"];
				foreach($left_menu_3 as $k=>$v) $left_menu_3[$k]["url"]=U($v["url"]);
				$left_menu[3]=array("title"=>$top_menu["works"]["title"],'list'=>$left_menu_3);				
				$sys_menu["left"]=$left_menu;
				$sys_menu["current_title"]="桌面";
			}else{
				$left_menu=C("DESIGNER_MENU")[$current_controller_name];
				foreach($left_menu as $k=>$v){
					if($v["action"]==ACTION_NAME){
						$left_menu[$k]["class"]='list-group-item active';
					}else{
						$left_menu[$k]["class"]='list-group-item';
					}
					$left_menu[$k]["url"]=U($v["url"]);
				}
				$sys_menu["left"]=$left_menu;	
				$sys_menu["current_title"]=$left_menu[ACTION_NAME]["title"];
			}
			if(!$sys_menu["current_title"]) $sys_menu["current_title"] = $label_location;
			$this->assign("sys_menu",$sys_menu);
			
		}else if($this->login_member_info["zl_role"]==6){//银行家菜单
		
			$top_menu=C("BANK_MENU")["top_menu"];
			foreach($top_menu as $k=>$v){
				if($k==$current_controller_name){
				$top_menu[$k]["css"]='class="active"';	
				}else{
				$top_menu[$k]["css"]='';	
				}
				$top_menu[$k]["url"]=U($v["url"]);
			}
			$sys_menu["top"]=$top_menu;
			$sys_menu["top_name"]=$top_menu["$current_controller_name"]["title"];
			$sys_menu["current_controller"]=$current_controller_name;
			
			if($current_controller_name=="index"){
				$left_menu_1=C("BANK_MENU")["index"];
				foreach($left_menu_1 as $k=>$v) $left_menu_1[$k]["url"]=U($v["url"]);
				$left_menu[1]=array("title"=>$top_menu["index"]["title"],'list'=>$left_menu_1);
				$left_menu_2=C("BANK_MENU")["member"];
				foreach($left_menu_2 as $k=>$v) $left_menu_2[$k]["url"]=U($v["url"]);
				$left_menu[2]=array("title"=>$top_menu["member"]["title"],'list'=>$left_menu_2);
				$left_menu_3=C("BANK_MENU")["loan"];
				foreach($left_menu_3 as $k=>$v) $left_menu_3[$k]["url"]=U($v["url"]);
				$left_menu[3]=array("title"=>$top_menu["loan"]["title"],'list'=>$left_menu_3);				
				$sys_menu["left"]=$left_menu;
				$sys_menu["current_title"]="桌面";
			}else{
				$left_menu=C("BANK_MENU")[$current_controller_name];
				foreach($left_menu as $k=>$v){
					if($v["action"]==ACTION_NAME){
						$left_menu[$k]["class"]='list-group-item active';
					}else{
						$left_menu[$k]["class"]='list-group-item';
					}
					$left_menu[$k]["url"]=U($v["url"]);
				}
				$sys_menu["left"]=$left_menu;	
				$sys_menu["current_title"]=$left_menu[ACTION_NAME]["title"];
			}
			if(!$sys_menu["current_title"]) $sys_menu["current_title"] = $label_location;
			$this->assign("sys_menu",$sys_menu);
		}else{
			if(!in_array(ACTION_NAME,$this->action_no_login_array,false)){
			$this->redirect('Ucenter/Login/index');
			}
		}
		$this->assign("sys_heading",$sys_menu["current_title"]);//当前标题
		$this->assign("sys_current",'<ol class="breadcrumb" style="padding:10px 0px;margin:5px 0px;"><span><a><i class="fa fa-location-arrow"></i> 当前位置：</a></span><li><a href="'.U("Ucenter/index/index").'">'.$this->login_member_info["zl_role_label"].'管理中心</a></li><li><a href="'.U("Ucenter/index/index").'">'.$sys_menu["top_name"].'</a></li><li class="active">'.$sys_menu["current_title"].'</li></ol>');//当前标题
		
	}
	
	
	
}
?>