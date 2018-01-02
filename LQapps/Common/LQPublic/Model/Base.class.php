<?php
/*
 * 后台公共Action
 * Author：国雾院theone（438675036@qq.com）
 * Date:2013-06-27
 A 的命名 - 方法命名
 页面 list , edit , images , sort_list
 */
namespace LQPublic\Model;
use Think\Model;
class Base extends Model{
    /**
      +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::__construct();
      +----------------------------------------------------------
     */
    public function __construct() {
		parent::__construct();
		//加global $LQcfg 数据
		$LQcfg= C();
		if(APP_DEBUG==false) include_once APP_COMMON_PATH."Conf/lqcfg.php";
    }

	//获取干净数据 $type: post/get
    public function getSafeData($param,$type='g') {
		if($type=='g'){
			if($param){
				$returndata=I("get.".$param);
			}else{
				$returndata=I("get.");
			}
			
		}elseif($type=='p'){
			if($param){
						$lqf_data=I("post.".$param);
						if($param=='LQL') return $lqf_data;
						if( is_array($lqf_data) ){
							unset($returndata[$param]);
							$la_newlqf=array();
							foreach($lqf_data as $lcKey =>$lcValue){
								
							   $lcdatatype=substr($lcKey,1,1);
							   switch ($lcdatatype) {
								   case "n"://数字型(整数)
									 $la_newlqf[$lcKey]=I("data.".$lcKey,'0','int',$lqf_data);
								   break;
								   case "f"://数值型(浮点数)
									 $la_newlqf[$lcKey]=I("data.".$lcKey,0,'float',$lqf_data);
								   break;				   
								   case "l"://针对的逻辑类型 作数字
									 $la_newlqf[$lcKey]=I("data.".$lcKey,1,'int',$lqf_data);
								   break;		   
								   case "c"://针对文本域的
								     $get_c_data=I("data.".$lcKey,'','',$lqf_data);
								     if( is_array($get_c_data) ){
										 $la_newlqf[ str_replace("[]","",$lcKey) ]= implode(",",$get_c_data) ;
									 }else{ $la_newlqf[$lcKey]=I("data.".$lcKey,'','',$lqf_data);  }
									 
								   break;
								   case "d"://针对日期的 作字符
									 $la_newlqf[$lcKey]=I("data.".$lcKey,'','',$lqf_data);
								   break;
							   }
							   
							}
							$la_newlqf["id"]=intval($lqf_data["id"]);
							$la_newlqf["__hash__"]=$lqf_data["__hash__"];
							
							//加强数据库敏感字段过滤
							if($param=='LQF'){
								unset($la_newlqf["zl_visible"]);
								unset($la_newlqf["zn_cdate"]);
								unset($la_newlqf["zn_mdate"]);
								if($this->_protected_field){
									foreach($this->_protected_field as $k =>$v){
										unset($la_newlqf[$v]);
									}
								}
							}
							return $la_newlqf;
						}
				$returndata=I("post.".$param);	
			}else{
				$returndata=I("post.");
			}
		}
		return $returndata;
    }	
	
	
	//编辑页返回列表地址
	public function editBackurl($str){
		$editbackurl= U($str."/index");
		if(session($str.'_cookie_indexlasturl')){
				$editbackurl= session($str.'_cookie_indexlasturl');
		}			
		return $editbackurl;
	}
	
	
	//获取表单数据 并 create 验证
	public function lqPostData($rules=array()) {
		//表单数据构建
        $data = $this->getSafeData('LQF',"p");
		
		//数据验证s
		if(is_array($rules)){
			$data=$this->validate($rules)->create($data);
			if (!$data){
				return $this->getError();			
			}			
		}else{
			$data=$this->create($data);
			if (!$data){
				return $this->getError();			
			}			
		}
		unset($data["__hash__"]);
		//数据验证e
		if($data["id"]){
			//记录更新时间
			$data= array_merge($data, array('zn_mdate'=>NOW_TIME) );				
		}else{
			//记录插入时间
			$data= array_merge($data, array('zn_cdate'=>NOW_TIME,'zn_mdate'=>NOW_TIME) );			
		}
	 	return $data;	
	 }	

	 
}
?>