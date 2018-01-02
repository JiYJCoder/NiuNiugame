<?php //设计师说 数据处理，数据回调
namespace Api\Model;
use Member\Api\MemberApi as MemberApi;
defined('in_lqweb') or exit('Access Invalid!');

class DesignerModel extends PublicModel {
	protected $model_works,$model_member,$model_application;
    // 模型名称 - 数据表名（不包含表前缀）
	protected $tableName        =   'designer';	
    public function __construct() {
		parent::__construct();
		$this->model_works=M("designer_works");//作品模型
		$this->model_member = new MemberApi;
        $this->model_application = M("hd_application");
        $this->model_region = M("region");
	}
	
	//获取美家风格缓存数据
    public function getAttributeCache() {
		$list=$style=$household=$area=array();
//		$style[0]=array("id"=>0,"title"=>"不限");
//		$household[0]=array("id"=>0,"title"=>"不限");
//		$area[0]=array("id"=>0,"title"=>"不限");
		foreach (F('hd_attribute_1','',COMMON_ARRAY) as $k => $v) $style[]=array("id"=>$k,"title"=>$v);
		foreach (F('hd_attribute_2','',COMMON_ARRAY) as $k => $v) $household[]=array("id"=>$k,"title"=>$v);
		foreach (F('hd_attribute_3','',COMMON_ARRAY) as $k => $v) $area[]=array("id"=>$k,"title"=>$v);
		$list["style"]=$style;//风格
		$list["household"]=$household;//户型
		$list["area"]=$area;//面积
        return $list;
    }	

	//设计师-记录总数
    public function lqCount($sqlwhere = '1'){return  $count = $this->where($sqlwhere)->count();}
	//设计师-列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC'),$member="") {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
			//会员信息
			$headimg = $this->model_member->apiGetField("id=".$laValue["zn_member_id"],'zc_headimg,zc_headimg_thumb');
			if($headimg){
				if(substr($headimg['zc_headimg'],0,4)=='http'){
					$list[$lnKey]['headimg'] = $headimg['zc_headimg'];
				}else{
                    $img = $headimg['zc_headimg_thumb'] ? $headimg['zc_headimg_thumb'] : $headimg['zc_headimg'];

                    $list[$lnKey]['headimg'] = API_DOMAIN.$img;
				}
			}else{
					$list[$lnKey]['headimg'] = NO_HEADIMG;			
			}

            if($member) {
                if ($this->model_member->apiTestLove($laValue["id"],5,$member)) {
                    $list[$lnKey]["subscribe_designer_label"] = "已关注";
                    $list[$lnKey]["subscribe_designer_status"] = 1;
                    $list[$lnKey]["is_agress"] = 1;
                } else {
                    $list[$lnKey]["subscribe_designer_label"] = "关注";
                    $list[$lnKey]["subscribe_designer_status"] = 0;
                    $list[$lnKey]["is_agress"] = 0;
                }
            }else {
                $list[$lnKey]["subscribe_designer_label"] = "未关注";
                $list[$lnKey]["subscribe_designer_status"] = 2;
                $list[$lnKey][" is_agress"] = 2;
            }

			$list[$lnKey]['content'] = lq_kill_html($laValue["zc_resume"],20);
			//$list[$lnKey]['subscribe'] = 1;
			unset($list[$lnKey]['zn_member_id']);
			unset($list[$lnKey]['zc_resume']);
        }
        return $list;
    }
	

	//作品-记录总数
    public function lqWorksCount($sqlwhere = '1'){return  $count = $this->model_works->where($sqlwhere)->count();}
	//作品-列表页
    public function lqWorksList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' zl_visible=1 ','order'=>'`zd_send_time` DESC')) {
		$list = $this->model_works->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();

		$attribute_style=F('hd_attribute_1','',COMMON_ARRAY);
		$attribute_household=F('hd_attribute_2','',COMMON_ARRAY);
		$attribute_area=F('hd_attribute_3','',COMMON_ARRAY);

        foreach ($list as $lnKey => $laValue) {
			if($laValue["image"]){
				$list[$lnKey]['image'] = API_DOMAIN.$laValue["image"];
			}else{
				$list[$lnKey]['image'] = NO_PICTURE;
			}	
			$list[$lnKey]['headimg'] = NO_PICTURE;		
			$headimg = $this->model_member->apiGetFieldByID($laValue["zn_member_id"],'zc_headimg');
			if($headimg) $list[$lnKey]['headimg'] = API_DOMAIN.$headimg;

			$list[$lnKey]['time'] = lq_cdate_format($laValue["time"],"Y-m-d H:i:s");
			$list[$lnKey]['content'] = lq_kill_html($laValue["content"],20);
            $list[$lnKey]['content_index'] = lq_kill_html($laValue["content"],80);

			$list[$lnKey]['thumb_width'] = intval($laValue["thumb_width"]);
			if($list[$lnKey]['thumb_width']==0) $list[$lnKey]['thumb_width']=357;
			$list[$lnKey]['thumb_height'] = intval($laValue["thumb_height"]);
			if($list[$lnKey]['thumb_height']==0) $list[$lnKey]['thumb_height']=357;
			if($laValue["zn_area"]){
			$list[$lnKey]['tag'] = "#".$attribute_style[$laValue["zn_style"]]." #".$attribute_household[$laValue["zn_household"]]." #".$attribute_area[$laValue["zn_area"]];
			}else{
			$list[$lnKey]['tag'] = "#".$attribute_style[$laValue["zn_style"]]." #".$attribute_household[$laValue["zn_household"]];
			}

            $designer = $this->where("id=" .$laValue["designer_id"])->find();
            $list[$lnKey]['designer_name'] = $designer["zc_nickname"];
            $list[$lnKey]['personality_sign'] = $designer["zc_personality_sign"];
            $list[$lnKey]["share_url"] = API_DOMAIN ."/wx/views/designer/caseDetails.html?tnid=".$laValue["id"];
            //图册
            if($laValue["zc_works_photos"]){
                $album=explode(",",$laValue["zc_works_photos"]);

                foreach ($album as $k => $v) {
                    $album[$k]=API_DOMAIN.$v;
                }
                $list[$lnKey]["album"] = $album;

                $list[$lnKey]["album_num"] = ($k+1);
            }else{
                $list[$lnKey]["album"] = 0;
                $list[$lnKey]["album_num"] = 0;
            }

            unset($list[$lnKey]['zn_member_id']);
			unset($list[$lnKey]['zn_style']);
			unset($list[$lnKey]['zn_household']);
			unset($list[$lnKey]['zn_area']);
        }
        return $list;
    }

	//通过ID获取设计师信息
    public function getDesignerById($id,$mustCache=0) {
        $attribute_style=F('hd_attribute_1','',COMMON_ARRAY);

		if($mustCache==0){
		$info=PAGE_S("page_designer_".$id,'',$this->cache_options); //读取缓存数据
		if($info) return $info;
		}
		$data = $this->where("id=" .$id)->find();

		if(!$data)  return 0;

		$info=array();
		$info['id'] = $data["id"];
		$info['nickname'] =$info['seo_title']= $data["zc_nickname"];
        $info["personality_sign"] = $data["zc_personality_sign"];
        $info["work_year"] = ch_num(date("Y")-$data["zn_join_year"])."年设计经验";

        $subscribe_where = " zc_action= 'subscribe_designer' and zn_object_id=".$id;//sql条件
        $subscribe_num = $this->model_member->apiLogCount($subscribe_where);
        $info["subscribe_num"] = $subscribe_num;


        $info['level'] = C("DESIGNER_LEVEL")[$data['zl_level']].'设计师';


        $city=M("region")->where('id='.$data["zn_city"])->getField('zc_name');
        $city = $city? "服务城市为".$city : "服务城市未定";
        $info["city"] = $city;

        $attribute_style=F('hd_attribute_1','',COMMON_ARRAY);
        $style = explode(",",$data["zc_style_tag"]);
        foreach ($style as $lnKey => $laValue) {
            $style_tag[] = $attribute_style[$laValue];
        }
        $style_tag = $data["zc_style_tag"] ? "个人风格为".implode(",",$style_tag) : "个人风格未定";

        $info["style_tag"] = $style_tag;
        $info["resume"]=lq_format_content($data["zc_resume"]);

		$info['headimg'] = NO_PICTURE;		

        $headimg = $this->model_member->apiGetField("id=".$data["zn_member_id"],'zc_headimg,zc_headimg_thumb');
        if($headimg){
            if(substr($headimg['zc_headimg'],0,4)=='http'){
                $info['headimg'] = $headimg['zc_headimg'];
            }else{
                $img = $headimg['zc_headimg_thumb'] ? $headimg['zc_headimg_thumb'] : $headimg['zc_headimg'];

                $info['headimg'] = API_DOMAIN.$img;
            }
        }

		$info['application_url'] = U("home/designer/application?tnid=".$id);
			
		//作品列表
		$sqlwhere_parameter=" zl_visible=1 and zn_designer_id=".$id;//sql条件
		$page_config = array(
				'field'=>"`id`,`zn_style`,`zn_household`,`zn_designer_id` as designer_id,`zn_member_id`,`zc_caption` as title,`zc_works_photo` as image,`zc_introduction` as content",
				'where'=>$sqlwhere_parameter,
				'order'=>'zn_sort ASC,zn_work_release DESC',
		);	
		$info['works_list'] = $this->lqWorksList(0,21,$page_config);
        $info['works_num'] = $this->model_works->where($sqlwhere_parameter)->count();
        $sqlwhere_application = "zn_designer_id=".$id;
        $info["application_num"] = $this->model_application->where($sqlwhere_application)->count();
        $info["share_url"] = API_DOMAIN ."/wx/views/designer/designerDetails.html?tnid=".$id;

		PAGE_S("page_designer_".$id,$info,$this->cache_options); //缓存数据
		return $info;
	}
	

	//通过ID获取作品信息
    public function getWorksById($id,$mustCache=0) {
		if($mustCache==0){
		$info=PAGE_S("page_works_".$id,'',$this->cache_options); //读取缓存数据
		if($info) return $info;
		}
		$data = $this->model_works->where("id=" .$id)->find();
		if(!$data)  return 0;

		$info=array();
		$info['id'] = $data["id"];
		$info['designer_nickname']=$info['seo_title']=$data["zc_designer_nickname"];
		$info['designer_headimg'] = NO_PICTURE;
        $headimg = $this->model_member->apiGetField("id=".$data["zn_member_id"],'zc_headimg,zc_headimg_thumb');
        if($headimg){
            if(substr($headimg['zc_headimg'],0,4)=='http'){
                $info['designer_headimg'] = $headimg['zc_headimg'];
            }else{
                $img = $headimg['zc_headimg_thumb'] ? $headimg['zc_headimg_thumb'] : $headimg['zc_headimg'];

                $info['designer_headimg'] = API_DOMAIN.$img;
            }
        }

		$resume = $this->where("id=".$data["zn_designer_id"])->getField("zc_resume");
		$info["designer_resume"]=lq_kill_html($resume,20);
		$info["designer_id"]=$data["zn_designer_id"];
		$info["works_photo"]= API_DOMAIN.$data["zc_works_photo"];
		$info["title"]=$data["zc_caption"];
		$info["content"]=lq_format_content($data["zc_introduction"]);
		$info["clicks"]=$data["zn_clicks"];
		$info["agrees"]=$data["zn_agrees"];
		$info['api_display'] = U("api/designer/works-display?tnid=".$data["id"],'',true,true);
        $info["share_url"] = API_DOMAIN ."/wx/views/designer/caseDetails.html?tnid=".$id;

		//图册
		if($data["zc_works_photos"]){
			$album=explode(",",$data["zc_works_photos"]);

			foreach ($album as $k => $v) {
				$album[$k]=API_DOMAIN.$v;
			}
			$info["album"] = $album;

            $info["album_num"] = ($k+1);
		}else{
			$info["album"] = 0;
            $info["album_num"] = 0;
		}		
		
		PAGE_S("page_works_".$id,$info,$this->cache_options); //缓存数据
		return $info;
	}
	
	//访问统计
	public function setViewCount($id){
		$this->model_works->where('zl_visible=1 and id='.$id)->setInc('zn_clicks',1);
		$page_view = $this->model_works->where('zl_visible=1 and id='.$id)->getField('zn_clicks');
		return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"] ,'data' => $page_view );		
	}
	
	//设计师案例点赞统计
	public function setAgreeWorkCount($id){
		$this->model_works->where('zl_visible=1 and id='.$id)->setInc('zn_agrees',1);

		$page_view = $this->model_works->where('zl_visible=1 and id='.$id)->getField('zn_agrees');
		return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"] ,'data' => $page_view );			
	}

    //设计师点赞数量统计
    public function setAgreeCount($id){
        $this->where('zl_visible=1 and id='.$id)->setInc('zn_subscribe',1);
        return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"] ,'data' => $this->where('zl_visible=1 and id='.$id)->getField('zn_subscribe') );
    }

}

?>
