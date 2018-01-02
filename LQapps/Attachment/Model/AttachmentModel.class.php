<?php
/*
附件API
*/
namespace Attachment\Model;
use Think\Model;
/**
 * 附件模型
 */
class AttachmentModel extends Model{
	/**
	 * 条件-用户列表总数
	 */
	public function list_Count($sqlwhere_parameter){
		return $this->where($sqlwhere_parameter)->count();
	}
	
	/**
	 * 附件列表
	 * @param  firstRow int               记录开始
	 * @param  listRows int               记录条数
	 * @param  page_config  array         配置数组
	 */
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		session('index_current_url',__SELF__);
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['zn_type_label'] = $laValue['zn_type'] == 1 ? "文件" : "图片";
			$list[$lnKey]['zn_cdate_label'] = lq_cdate_format($laValue['zn_cdate']);
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }

	/**
	 * 新增附件信息
	 * @param array $table 表名
	 * @param array $data 修改的字段数组
	 * @return true 修改成功，false 修改失败
	 */
	public function insert_Attachment($table,$data){
		if(empty($data)|empty($table)){
			return -1;
		}
		//新增
		$id = $this->table($this->tablePrefix.$table)->add($data);
		return $id ? $id : 0; //0-未知错误，大于0-插入成功
	}
	
	/**
	 * 删除附件信息
	 * @param array $data id集
	 * @return true 修改成功，false 修改失败
	 */
	public function deleteAttachment($data){
		if(empty($data)){
			return 0;
		}
		//先删除文件
		$list = $this->field("`zc_file_path`")->where($data)->order("`id` DESC")->select();
        foreach ($list as $lnKey => $laValue) {
				if(file_exists(WEB_ROOT.$laValue["zc_file_path"])&&$laValue["zc_file_path"]){
					 @unlink(WEB_ROOT.$laValue["zc_file_path"]);
				}			
        }		
		if ($this->where($data)->delete()){
			return 1;
		}else{
			return 0;	
		}
	}	
	

}
