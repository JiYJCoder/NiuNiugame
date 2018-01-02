<?php

namespace Ucenter\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use Member\Api\MemberApi as MemberApi;
use Admin\Model\RegionModel as RegionApi;

defined('in_lqweb') or exit('Access Invalid!');
class MemberController extends PublicController{
	protected $sub_info;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'会员信息'),
		//帐户信息
		'1'=>array(
		array('textShow', 'zc_account', "会员帐号",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zc_mobile', "会员手机",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('text', 'zc_nickname', "会员昵称",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":25}'),
		array('select', 'zl_sex', "会员性别",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择性别"}'),
		array('selectRegion', 'zn_province|zn_city|zn_district', "会员地区",1,'{"label":"zc_area","required":"1","please":"请选择"}'),
		array('text', 'zc_address', "会员地址",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('image', 'zc_headimg', "会员头像",1,'{"type":"avatar","allowOpen":1}'),		
		),
	);	
	
    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
		if($this->login_member_info["zl_role"]==1){//设计师
			$this->myForm["tab_title"][2]='设计师信息';
			$this->myForm[2] = array(
			array('select','zn_join_year',"入行年份",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
			array('checkbox', 'zc_style_tag', "个人风格",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"menu":0}'),
			array('textarea', 'zc_personality_sign', "个性签名",1,'{"class":"textarea","required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
			array('textarea', 'zc_resume', "设计理念",1,'{"class":"textarea","required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
			);
			//设计师数据
			$this->sub_info = M("designer")->where("zn_member_id=".$this->login_member_info["id"])->find();
			
		}elseif($this->login_member_info["zl_role"]==6){//银行
			$this->myForm["tab_title"][2]='银行信息';
			$this->myForm[2] = array(
			array('text', 'zc_contact', "联系人",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
			array('text', 'zc_contact_tel', "联系电话",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
			array('editor', 'zc_content', "银行装修贷内容",1,'{"required":"0","model":"1","ext":"LQF","width":"100%","height":"300px"}'),	
			);
			//银行数据
			$this->sub_info = M("bank")->where("zn_member_id=".$this->login_member_info["id"])->find();						
		}
		
		if(!$this->sub_info){  
				$this->model_member->apiLoginOut();	 			
				$this->error(C("ALERT_ARRAY")["recordNull"],U('ucenter/login/index'));
		}		
		
    }


    //会员首页
    public function index(){
        $this->display('index');
    }
	
    //修改我的信息
    public function edit(){
        if (IS_POST) {
			 if ($this->model_member->apiIsAllowOs('edit_member', $this->login_member_info)) {
				$this->ajaxReturn(array('status' => 0, 'msg' => '编辑次数过于频繁，明天再操作吧！', 'data' => '', "url" => ""), $this->JSONP);
			 }
			 $data_form=$this->getSafeData('LQF','p');
			 $data_member =array(
			 	"id" => $this->login_member_info["id"],
			 	"zl_sex" => $data_form["zl_sex"],
			 	"zn_province" => $data_form["zn_province"],
			 	"zn_city" => $data_form["zn_city"],
			 	"zn_district" => $data_form["zn_district"],
			 	"zc_area" => $data_form["zc_area"],
			 	"zc_nickname" => $data_form["zc_nickname"],
			 	"zc_address" => $data_form["zc_address"],
			 	"zc_headimg" => $data_form["zc_headimg"],
				"__hash__" => $_POST["LQF"]["__hash__"],
			 );
			 if($this->login_member_info["zl_role"]==1){//设计师
				 $designer_data =array(
					"zc_nickname" => $data_form["zc_nickname"],
					"zn_join_year" => $data_form["zn_join_year"],
					"zc_style_tag" => implode(",",$data_form["zc_style_tag"]),
					"zc_personality_sign" => $data_form["zc_personality_sign"],
					"zc_resume" => $data_form["zc_resume"],
				 );
				 if($designer_data["zn_join_year"]<1980||$designer_data["zn_join_year"]>intval(date("Y"))) $this->ajaxReturn(array('status' => 0, 'msg' => '入行年份不正确！', 'data' => '', "url" => ""));
				 $style_tag_len = mb_strlen($designer_data["zc_style_tag"]);
				 if($style_tag_len<1||$style_tag_len>200) $this->ajaxReturn(array('status' => 0, 'msg' => '个人风格1~200个字符间！', 'data' => '', "url" => ""));
				 $resume_len = mb_strlen($designer_data["zc_resume"]);
				 if($resume_len>65000) $this->ajaxReturn(array('status' => 0, 'msg' => '设计理念字符太长了！', 'data' => '', "url" => ""));
				 $personality_sign_len = mb_strlen($designer_data["zc_personality_sign"]);
				 if($personality_sign_len>200) $this->ajaxReturn(array('status' => 0, 'msg' => '个性签名字符太长了！', 'data' => '', "url" => ""));
			 }elseif($this->login_member_info["zl_role"]==6){//银行
				 $bank_data =array(
					"zc_contact" => $data_form["zc_contact"],
					"zc_contact_tel" => $data_form["zc_contact_tel"],
					"zc_content" => $data_form["zc_content"],
				 );
				 $contact_len = mb_strlen($bank_data["zc_contact"]);
				 if($contact_len<1||$contact_len>50) $this->ajaxReturn(array('status' => 0, 'msg' => '联系人1~50个字符间！', 'data' => '', "url" => ""));
				 $contact_tel_len = mb_strlen($bank_data["zc_contact_tel"]);
				 if($contact_tel_len<1||$contact_tel_len>50) $this->ajaxReturn(array('status' => 0, 'msg' => '联系电话1~50个字符间！', 'data' => '', "url" => ""));				
				 $content_len = mb_strlen($bank_data["zc_content"]);
				 if($content_len>65000) $this->ajaxReturn(array('status' => 0, 'msg' => '银行装修贷内容字符太长了！', 'data' => '', "url" => ""));
			 }
			
			 $mid = $this->model_member->apiUpdateMember($data_member);
		     if(preg_match('/^([1-9]\d*)$/',$mid)){
				if($this->login_member_info["zl_role"]==1){//设计师
					M("designer")->where("zn_member_id=".$this->login_member_info["id"])->save($designer_data);//修改设计师信息
					PAGE_S("page_designer_".$designer_data["id"],NULL);	//清除设计师缓存
				}elseif($this->login_member_info["zl_role"]==6){//银行
					M("bank")->where("zn_member_id=".$this->login_member_info["id"])->save($bank_data);//修改银行信息
				}
				$this->model_member->addMemberLog("edit_member");//写入日志
				$this->ajaxReturn( array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"],'url' => U('ucenter/member/edit')));
			}else{
				$this->ajaxReturn(array('status' => 0, 'msg' => $mid));
			}
        } else {
			$lcdisplay='edit';
			//读取数据s
			$data = $this->login_member_info;
			
			
			//表单数据初始化s
			$form_array=array();
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			//会员
			$form_array["zl_role_data"]=C('MEMBER_ROLE');
			$form_array["zn_province_data"]=F('province','',COMMON_ARRAY);//省
			$form_array["zn_city_data"]=$this->returnRegionList($form_array["zn_province"]);//市
			$form_array["zn_district_data"]=$this->returnRegionList($form_array["zn_city"]);//区
			$form_array["zl_sex_data"]=C('_SEX');
			
			if($this->login_member_info["zl_role"]==1){//设计师
				$this_year=date("Y");
				for($index=$this_year;$index>=1980;$index--)	$year_array[$index]=$index;
				$form_array["zn_join_year_data"]=$year_array;//年
				$form_array["zc_style_tag_data"]=F('hd_attribute_1','',COMMON_ARRAY);
				$form_array["zn_join_year"]=$this->sub_info["zn_join_year"];
				$form_array["zc_style_tag"]=$this->sub_info["zc_style_tag"];
				$form_array["zc_personality_sign"]=$this->sub_info["zc_personality_sign"];
				$form_array["zc_resume"]=$this->sub_info["zc_resume"];
			}else{
				$form_array["zc_contact"]=$this->sub_info["zc_contact"];
				$form_array["zc_contact_tel"]=$this->sub_info["zc_contact_tel"];
				$form_array["zc_content"]=$this->sub_info["zc_content"];				
			}
			
			$Form=new Form($this->myForm,$form_array,$this->model_member->apiGetCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化e

            $this->display($lcdisplay);
        }		
    }
	


    //修改我的密码
    public function edit_pass(){
        if (IS_POST) {
			 if ($this->model_member->apiIsAllowOs('edit_pass', $this->login_member_info)) {
				$this->ajaxReturn(array('status' => 0, 'msg' => '修改次数过于频繁，明天再操作吧！', 'data' => '', "url" => ""), $this->JSONP);
			 }
			 $mid = $this->model_member->apiEditPass();
		     if(preg_match('/^([1-9]\d*)$/',$mid)){
				$this->model_member->addMemberLog("edit_pass");//写入日志
				$this->model_member->apiLoginOut();
				$this->ajaxReturn( array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"],'url' => U('ucenter/login/index')));
			}else{
				$this->ajaxReturn(array('status' => 0, 'msg' => $mid));
			}
        } else {
			$lcdisplay='edit-pass';
			$this->myForm = array(
				//标题
				'tab_title'=>array(1=>'修改我的密码'),
				//帐户信息
				'1'=>array(
					array('password', 'zc_password', "会员密码",1,'{"required":"1","dataType":"password","dataLength":"","readonly":0,"disabled":0}'),
					array('password', 'zc_password_chk', "确认密码",0,'{"required":"1","dataType":"","dataLength":"","confirm":"zc_password","readonly":0,"disabled":0}'),
				),
			);				
			
			//表单数据初始化s
			$form_array=array();
			$form_array["id"]=$this->login_member_info["id"];//年
			$Form=new Form($this->myForm,$form_array,$this->model_member->apiGetCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化e
            $this->display($lcdisplay);
        }		
    }	
	
    //我的消息
    public function msg(){
		
        $this->display('msg');
    }	
	
	
}