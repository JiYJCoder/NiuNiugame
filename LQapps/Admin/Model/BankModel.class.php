<?php //银行信息表 Bank 数据处理，数据回调
namespace Admin\Model;
use Think\Model;
use Member\Api\MemberApi as MemberApi;

class BankModel extends PublicModel {
	protected $model_member;
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_content','0,65000','银行装修贷内容在0~65000个字符',self::MUST_VALIDATE,'length'),
		array('zc_contact','1,50','联系人在1~50个字符',self::MUST_VALIDATE,'length'),
		array('zc_contact_tel','1,50','联系电话在1~50个字符',self::MUST_VALIDATE,'length'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zl_is_index', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zc_content', 'lqNull', self::MODEL_BOTH,'function'),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),
	);	
	//保护字段（add或edit不能操作）
	protected $_protected_field=array('zn_member_id','zc_member_account','zn_mdate');//,'zl_level'
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_bank_name";//数据表显示标题字段
		$this->pc_index_list =  "Bank/index";//列表首页
		$this->model_member = new MemberApi;
	}

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();	
		foreach ($list as $lnKey => $laValue) {
			if(ACTION_NAME=='window'){
			$list[$lnKey]['headimg'] = $this->model_member->apiGetFieldByID($laValue["zn_member_id"],'zc_headimg');
			}
       		$list[$lnKey]['loan_apply_count']  = M("loan_apply")->where("zn_bank_id=".$laValue["id"])->count();
			$list[$lnKey]['zl_is_index_label'] = $laValue['zl_is_index'] == 1 ? '是首页' : '非首页';
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }

	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}


    //更改-是非首页 
    public function setProperty() {
		$lcop=I("get.tcop",'is_index');
		$data=array();
        $data["id"] = I("get.tnid",'0','int');
		if($lcop=='is_index'){
			$data['zl_is_index'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
			$op_data= array("status" => $data['zl_is_index'], "txt" => $data['zl_is_index'] == 1 ? "是首页" : "非首页" ) ;			
		}else{
			return array('status' => 0, 'msg' => L("ALERT_ARRAY")["dataOut"]);
		}
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' =>$op_data );
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }

}

?>
