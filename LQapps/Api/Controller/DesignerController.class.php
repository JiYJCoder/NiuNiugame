<?php
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
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
use LQLibs\Util\Category as Category;//树状分类

defined('in_lqweb') or exit('Access Invalid!');
class DesignerController extends PublicController {
	protected $D_DESIGNER;
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->D_DESIGNER=D("Api/Designer");//设计师
    }
	
	//首页数据包
    public function index(){
        self::apiCheckToken(0);//用户认证

        $data=array();
        $works_config = array(
            'field'=>array("id","zc_caption"=>"caption","zc_introduction"=>"content","zc_works_photos","zn_designer_id"=>"designer_id","zn_agrees"=>"agrees","zn_clicks"=>"clicks","zn_cdate"=>"cdate","zn_member_id"),
            'where'=>"zl_visible=1 and zl_is_index=1",
            'order'=>'zn_sort ASC,zn_work_release DESC',
        );
        $works = $this->D_DESIGNER->lqWorksList(0,5,$works_config);
        foreach($works as $lnKey => $laValue)
        {
            $works[$lnKey]["is_agree"] = $works[$lnKey][" is_agress"] = $this->model_member->apiTestLove($laValue["id"],2,$this->login_member_info) ? 1:0;
            if($this->login_member_info) {
                if ($this->model_member->apiTestLove($laValue["designer_id"],5,$this->login_member_info)) {
                    $works[$lnKey]["subscribe_designer_label"] = "已关注";
                    $works[$lnKey]["subscribe_designer_status"] = 1;
                    $works[$lnKey]["is_agress"] = 1;
                } else {
                    $works[$lnKey]["subscribe_designer_label"] = "关注";
                    $works[$lnKey]["subscribe_designer_status"] = 0;
                    $works[$lnKey]["is_agress"] = 0;
                }
            }else {
                $works[$lnKey]["subscribe_designer_label"] = "未关注";
                $works[$lnKey]["subscribe_designer_status"] = 2;
                $works[$lnKey]["is_agress"] = 2;
            }

            $works[$lnKey]["time"] = date("Y-m-d H:i:s",$laValue["cdate"]);
            $works[$lnKey]["content"] = $laValue["content_index"];
            unset($works[$lnKey]["zc_works_photos"]);
            unset($works[$lnKey]["cdate"]);
            unset($works[$lnKey]["image"]);
            unset($works[$lnKey]["tag"]);
            unset($works[$lnKey]["content_index"]);
        }
        $data["works"] = $works;

        $designers_config = array(
            'field'=>"id,zc_nickname as nickname,zn_member_id,zc_resume,zc_personality_sign as personality_sign",
            'where'=>"zl_visible=1 and zl_is_index=1",
            'order'=>'zl_good_index ASC,zl_level ASC',
        );
        $data["designers"] = $this->D_DESIGNER->lqList(0,5,$designers_config,$this->login_member_info);

        if(!$data){
            $this->ajaxReturn(array('status'=>1,'msg'=>'返回失败','data' =>"","url"=>"","note"=>"设计师首页"),$this->JSONP);
        }
        else{
            $this->ajaxReturn(array('status'=>1,'msg'=>'返回成功','data' =>$data,"url"=>"","note"=>"设计师首页"),$this->JSONP);
        }

    }

	//获得设计师说属性
    public function get_attribute(){
		$type= I("get.type",'');//类型
		$data=$this->D_DESIGNER->getAttributeCache();
		if($type){
			$data=$data[$type];
		}
		$this->ajaxReturn(array('status'=>1,'msg'=>'返回成功','data' =>$data,"url"=>"","note"=>"获取美家风格"),$this->JSONP);
	}
	
	//设计师说-作品列表-数据输出
	public function works_list(){
		$style= I("get.style",'','lqSafeExplode');//风格
		$household= I("get.household",'','lqSafeExplode');//户型
		$area=I("get.area",'','lqSafeExplode');//面积
		$pageno=I("get.p",'1','int');//页码
	
		//作品列表
		$sqlwhere_parameter=" zl_visible=1 ";//sql条件
		if($style){//风格
			if(is_numeric($style)){
				$sqlwhere_parameter.=" and zn_style =".$style;
			}else{
				$sqlwhere_parameter.=" and zn_style in($style) ";
			}
		}
		if($household){//户型
			if(is_numeric($household)){
				$sqlwhere_parameter.=" and zn_household =".$household;
			}else{
				$sqlwhere_parameter.=" and zn_household in($household) ";
			}
		}		
		if($area){//面积
			if(is_numeric($area)){
				$sqlwhere_parameter.=" and zn_area =".$area;
			}else{
				$sqlwhere_parameter.=" and zn_area in($area) ";
			}
		}	
			
		$page_config = array(
				'field'=>"`id`,`zn_style`,`zn_household`,`zn_area`,`zn_designer_id` as designer_id,`zn_member_id`,`zc_designer_nickname` as designer_nickname,`zc_caption` as title,`zc_thumb` as image,`zn_thumb_width` as thumb_width,`zn_thumb_height` as thumb_height,`zn_work_release` as time,`zn_clicks` as clicks,`zn_agrees` as agrees,`zc_introduction` as content",
				'where'=>$sqlwhere_parameter,
				'order'=>'zn_sort ASC,zn_work_release DESC',
		);	 
        $count = $this->D_DESIGNER->lqWorksCount($sqlwhere_parameter);
		$page = new \LQLibs\Util\Page($count,C("API_PAGESIZE")["works_list"]);//载入分页类
		//分页尽头
	    if($pageno>=$page->totalPages){
				$note='0';
		}else{
			if($count==(C("API_PAGESIZE")["works_list"]*$pageno)){
				$note='0';
			}else{
				$note='1';
			}
		}
		$list=$this->D_DESIGNER->lqWorksList($page->firstRow, $page->listRows,$page_config);
		$this->ajaxReturn(array('status'=>1,'msg'=>'返回成功','data' =>$list,"url"=>"","note"=>$note),$this->JSONP);
	}
	//作品详情-数据输出
	public function works_show(){
        self::apiCheckToken(0);//用户认证
		$data = $this->D_DESIGNER->getWorksById($this->lqgetid);
		if($data){
            $data["is_agress"] = $this->model_member->apiTestLove($data["id"],2,$this->login_member_info) ? 1:0;
            $data["subscribe_designer_status"] = $this->model_member->apiTestLove($data["designer_id"],5,$this->login_member_info) ? 1:0;
            $data["subscribe_designer_label"] = $data["subscribe_designer_status"] ? "已关注":"关注";
			//微信的JSSDK
			if(is_weixin()){
                $wx_share_config=array("url"=>cookie('referer'),"title"=>$data["title"],"link"=>'http://wx.lxjjz.cn/wx/views/designer/caseDetails.html?tnid='.$data["id"],"imgUrl"=>$data["works_photo"],"desc"=>lq_kill_html($data["content"],30));
				$data["wx_jssdk"]=lq_get_jssdk(C("WECHAT"),$wx_share_config);
			}			
		    $this->ajaxReturn(array('status'=>1,'msg'=>'返回成功','data' =>$data,"url"=>"","note"=>'作品详情'),$this->JSONP);
		}else{
		    $this->ajaxReturn(array('status'=>0,'msg'=>'返回失败','data' =>array(),"url"=>"","note"=>'作品详情'),$this->JSONP);
		}
	}
    public function works_display(){
		$data = $this->D_DESIGNER->getWorksById($this->lqgetid);
        $this->assign("data",$data);
		$this->display("Display/works");
    }	
	
	//设计师筛选条件
	public function designer_condition(){
		$data=$style=array();
		foreach (F('hd_attribute_1','',COMMON_ARRAY) as $k => $v) $style[]=array("id"=>$k,"title"=>$v);
		
		$data["style"]=$style;
		$experience=$city=array();
		$experience[]=array("id"=>1,"title"=>"1-3年设计经验");
		$experience[]=array("id"=>2,"title"=>"3-5年设计经验");
		$experience[]=array("id"=>3,"title"=>"5年以上设计经验");
		$city[]=array("id"=>440100,"title"=>"广州");
		$city[]=array("id"=>440300,"title"=>"深圳");
		$city[]=array("id"=>310000,"title"=>"上海");		
		$city[]=array("id"=>110000,"title"=>"北京");	
		$data["experience"]=$experience;
		$data["city"]=$city;
		$this->ajaxReturn(array('status'=>1,'msg'=>'返回成功','data' =>$data,"url"=>"","note"=>'设计师筛选条件'),$this->JSONP);
	}
	//设计师列表-数据输出
	public function designer_list(){
        self::apiCheckToken(0);//用户认证
		$style= I("get.style",'','lqSafeExplode');//风格
		$city=I("get.city",'','lqSafeExplode');//城市
		$experience= I("get.experience",'0','int');//设计经验
		$pageno=I("get.p",'1','int');//页码
	
		//作品列表
		$sqlwhere_parameter=" zl_visible=1 ";//sql条件
		if($style){//风格
			if(is_numeric($style)){
				$designer_array=M()->query("SELECT DISTINCT zn_designer_id FROM __PREFIX__designer_works WHERE zn_style=$style");
			}else{
				$designer_array=M()->query("SELECT DISTINCT zn_designer_id FROM __PREFIX__designer_works WHERE zn_style in($style)");
			}
			$designer_id='0';
			if($designer_array){

				foreach ($designer_array as $k => $v) {
					$designer_id.=','.$v["zn_designer_id"];
				}
			}
			if($designer_id!='0'){
				$sqlwhere_parameter.=" and id in($designer_id) ";	
			}
		}
		if($city){//城市
			if(is_numeric($city)){
                $sqlwhere_parameter.=" and zn_city = $city ";
            }else{
                $sqlwhere_parameter.=" and zn_city in($city) ";
            }
		}		
		if($experience){//设计经验
			$lnday=date("Y");//当天日期
			if($experience==1){
				$sqlwhere_parameter.=" and zn_join_year >".($lnday-3)." and zn_join_year<=".$lnday;
			}else if($experience==2){
				$sqlwhere_parameter.=" and zn_join_year >".($lnday-5)." and zn_join_year<=".($lnday-3);
			}else if($experience==3){
				$sqlwhere_parameter.=" and zn_join_year <=".($lnday-5);				
			}
		}	
		$page_config = array(
				'field'=>"id,zc_nickname as nickname,zn_member_id,zc_resume",
				'where'=>$sqlwhere_parameter,
				'order'=>'zl_good_index ASC,zl_level ASC',
		);	 
        $count = $this->D_DESIGNER->lqCount($sqlwhere_parameter);
		$page = new \LQLibs\Util\Page($count,C("API_PAGESIZE")["designer_list"]);//载入分页类
		//分页尽头
	    if($pageno>=$page->totalPages){
				$note='0';
		}else{
			if($count==(C("API_PAGESIZE")["designer_list"]*$pageno)){
				$note='0';
			}else{
				$note='1';
			}
		}
		$list=$this->D_DESIGNER->lqList($page->firstRow, $page->listRows,$page_config,$this->login_member_info);
		$this->ajaxReturn(array('status'=>1,'msg'=>'返回成功','data' =>$list,"url"=>"","note"=>$note),$this->JSONP);
	}
	
	//设计师-数据输出
	public function designer_show(){
        self::apiCheckToken(0);//用户认证
		$data = $this->D_DESIGNER->getDesignerById($this->lqgetid);

		if($data){
            if($this->login_member_info) {
                if ($this->model_member->apiTestLove($data["id"],5,$this->login_member_info)) {
                    $data["subscribe_designer_label"] = "已关注";
                    $data["subscribe_designer_status"] = 1;
                } else {
                    $data["subscribe_designer_label"] = "关注";
                    $data["subscribe_designer_status"] = 0;
                }
            }else {
                $data["subscribe_designer_label"] = "未关注";
                $data["subscribe_designer_status"] = 2;
            }
           // pr($data);
		$this->ajaxReturn(array('status'=>1,'msg'=>'返回成功','data' =>$data,"url"=>"","note"=>'设计师详情'),$this->JSONP);
		}else{
		$this->ajaxReturn(array('status'=>0,'msg'=>'返回失败','data' =>array(),"url"=>"","note"=>'设计师详情'),$this->JSONP);
		}
	}
		
}