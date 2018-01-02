<?php
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:上传文件
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

defined('in_lqweb') or exit('Access Invalid!');
class UploadController extends PublicController{
	protected $D_DESIGNER,$D_ART,$D_PRO,$D_SMS,$model_region;
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->D_DESIGNER=D("Api/Designer");//接口设计师实例化
			self::apiCheckToken();//用户认证
    }
	
	//会员首页
    public function index(){
		$this->ajaxReturn(array('status'=>0,'msg'=>'当前端口暂时关闭','data' =>array(),"url"=>"","note"=>""),$this->JSONP);
    }

    //单文件上传 *********************************************************************************
    public function upload_image() {
		ob_end_clean();
		if($this->model_member->apiIsAllowOs('upload_image',$this->login_member_info)){
			$this->ajaxReturn(array('status'=>0,'msg'=>'编辑次数过于频繁，请歇息一下！','data' =>'',"url"=>"","note"=>"单文件上传"),$this->JSONP);
		}		
		//上传控件 ID
		$fileid=I("get.fileid",'');
		//file表单控件名
		$file_widget=$_FILES["file_".$fileid];
		if($file_widget['size']==0){
			$this->ajaxReturn(array('status'=>0,'msg'=>'上传的文件不存在或为空','data' =>'',"url"=>"","note"=>""),$this->JSONP);
		}		
		//文件类型
		$type=I("get.type",'images');
		if(!array_key_exists($type,C("UPLOAD_EXT"))){
			$this->ajaxReturn(array('status'=>0,'msg'=>'上传的目录不存在','data' =>'',"url"=>"","note"=>""),$this->JSONP);
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
							  'zn_uid'=>$this->login_member_info["id"],
							  'zc_account'=>$this->login_member_info["zc_account"],	
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
			 if($lnLastInsID){
					$this->model_member->addMemberLog('upload_image',$this->login_member_info);
					$this->ajaxReturn(array('status'=>1,'msg'=>'上传成功','data' =>$upfile_data["zc_file_path"],"url"=>"","note"=>""),$this->JSONP);
			 }else{
					$this->ajaxReturn(array('status'=>0,'msg'=>'上传失败','data' =>'',"url"=>"","note"=>""),$this->JSONP);
			 }
		}else{
			$this->ajaxReturn(array('status'=>0,'msg'=>$upload->getError(),'data' =>'',"url"=>"","note"=>""),$this->JSONP);
		}
    }
	
}