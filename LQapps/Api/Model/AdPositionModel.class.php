<?php //广告系统 AdPosition 数据处理，数据回调
namespace Api\Model;
defined('in_lqweb') or exit('Access Invalid!');

class AdPositionModel extends PublicModel {
	protected $model_ad;
    // 模型名称 - 数据表名（不包含表前缀）
	protected $tableName        =   'ad_position';	
    /**
      +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::_initialize();
      +----------------------------------------------------------
     */
    public function __construct(){
		$this->model_ad=M("ad_list");//广告模型
		parent::__construct();
	}

	//通过ID获取广告位置数据与列表数据
    public function getAdPositionById($id) {
		$data=PAGE_S("page_ad_position_".$id,'',$this->cache_options); //读取缓存数据

		if($data) return $data;
			$info = $this->where("id=" .$id)->find();

			if(!$info)  return 0;
			$data=array();
			$data['position_id']=$info["id"];
			$data['position_name']=$info["zc_caption"];
			$data['width']=$info["zn_image_width"];
			$data['height']=$info["zn_image_height"];
			$data['max_ad']=$info["zn_max_ad"];
			
			$data['list']=$this->adList($info["id"],$info["zn_max_ad"]);

			PAGE_S("page_ad_position_".$id,$data,$this->cache_options); //缓存数据
			unset($info);
			return $data;
	}	


	//广告列表
	public function adList($ad_position_id,$max=0){
		if(!$ad_position_id)  return 0;
		if($max){
			$data=M("ad_list")->field('id,zc_caption,zc_link_url,zc_image,zl_client_type')->where('zl_visible=1 and zn_ad_position_id='.intval($ad_position_id) )->order("zn_sort,id DESC")->limit("0 , $max")->select();
		}else{
			$data=M("ad_list")->field('id,zc_caption,zc_link_url,zc_image,zl_client_type')->where('zl_visible=1 and zn_ad_position_id='.intval($ad_position_id) )->order("zn_sort,id DESC")->select();
		}
			
			$list=array();
			foreach($data as $lnKey=>$laValue){
				$list[$lnKey]['id']=$laValue["id"];
				$list[$lnKey]['index']=$lnKey+1;
				if($laValue["zc_image"]){
				$list[$lnKey]['image'] = API_DOMAIN.$laValue["zc_image"];
				}else{
				$list[$lnKey]['image'] = NO_PICTURE;
				}
				$list[$lnKey]['title'] = $laValue["zc_caption"];
				$list[$lnKey]['url'] = $laValue["zc_link_url"];
				$list[$lnKey]['client'] = $laValue["zl_client_type"];
			}
		unset($data);
		return $list;
	}

	//点击统计
	public function setViewCount($id){
		$page_view = $this->model_ad->where('id='.$id)->getField('zn_clicks') ;
		$data=array();
        $data["id"] = $id;
        $data["zn_clicks"] = $page_view+1 ;
        if ($this->model_ad->save($data)) {
            return array('status' => 1, 'info' => C("ALERT_ARRAY")["success"] ,'data' => $page_view+1 );
        } else {
            return array('status' => 0, 'info' => C("ALERT_ARRAY")["fail"], 'data' => 0 );
        }
	}


}

?>
