<?php
/*
附件API
*/

namespace Attachment\Api;
use Attachment\Api\Api;
use Attachment\Model\AttachmentModel;
use User\Api\AdminApi as AdminApi;


//require_cache(dirname(__FILE__) . '/Api.class.php');

class AttachmentApi extends Api{
	/**
	 * 构造方法，实例化操作模型
	 */
	protected function _init(){
		$this->model = new AttachmentModel();
	}
	
	/**
	 * 条件-附件列表总数
	 */
	public function listCount($sqlwhere_parameter){
		return $this->model->list_Count($sqlwhere_parameter);
	}

	/**
	 * 条件-附件列表
	 * @param  firstRow int               记录开始
	 * @param  listRows int               记录条数
	 * @param  page_config  array         配置数组
	 */
	public function lqList($page_firstRow, $page_listRows,$page_config){
		return $this->model->lqList($page_firstRow, $page_listRows,$page_config);
	}
	
	/**
	 * 插入附件表记录
	 */
	public function insertAttachment($lc_table,$data){
		return $this->model->insert_Attachment($lc_table,$data);
	}

    //删除文件 
    public function DelAttachment($where) {
		$deal=$this->model->deleteAttachment($where);
		if($deal==1){
			//写入日志
			$log_data=array(
					'id'=>"",
					'action'=>"opDeleteCheckbox",
					'table'=>"Attachment",
					'url'=>$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],
					'operator'=>session('admin_auth')["id"],
			);	
			$User = new AdminApi;
			$User->addAdminLog($log_data);
			return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => session('index_current_url') );
		}else{
			return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"], 'url' => session('index_current_url') );
		}
	}
	




}
