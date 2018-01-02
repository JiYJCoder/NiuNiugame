<?php //活动 Activity 数据处理，数据回调
namespace Api\Model;
use Member\Api\MemberApi as MemberApi;
use Think\Model;

defined('in_lqweb') or exit('Access Invalid!');

class ActivityModel extends PublicModel {
	protected $model_register_share , $model_member;
    // 模型名称 - 数据表名（不包含表前缀）
	protected $tableName        =   'activity';	
    public function __construct() {
		parent::__construct();
		$this->model_register_share=M("register_share");//会员注册分享
        $this->model_member = new MemberApi;//实例化会员
	}
	
	//记录总数
    public function lqCount($sqlwhere = '1'){return  $count = $this->where($sqlwhere)->count();}
	//列表页 //模板引用{$data.zc_title|lq_cutstr=30,0,'UTF-8','...'}
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' zl_visible=1 ','order'=>'`zd_send_time` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {

        }
        return $list;
    }
	
	/*
	新增会员注册分享
	*/
	public function addRegisterShare($member){
		$type_array=array(1=>'朋友圈',2=>'朋友',3=>'APP');
		$register_share=session("register_share");
		$lc_type = $type_array[$register_share["type"]];
		if(!$lc_type) $lc_type= 'APP';
		if($register_share&&$member){
            $account = $this->model_member->apiGetField("id=".intval($register_share["referer"]),"zc_account");
			if($account){
			$data["zc_type"] = $lc_type;
			$data["zn_referer_id"] = intval($register_share["referer"]);
			$data["zc_referer_accout"] = $account;
			$data["zn_member_id"] = $member["id"];
			$data["zc_member_account"] = $member["zc_account"];
			$data["zn_cdate"] = NOW_TIME;
			$data["zn_mdate"] = NOW_TIME;
			$this->model_register_share->add($data);
			}
		}
	}
	
	
	//通过ID获取活动数据 $id 活动ID ,$mustCache后台控制必须缓存
    public function getActivityById($id,$mustCache=0) {
		if($mustCache==0){
		$info=PAGE_S("page_activity_".$id,'',$this->cache_options); //读取缓存数据 
		if($info) return $info;
		}
		$data = $this->where(" zl_visible=1 and id=" .$id)->find();
		if(!$data)  return 0;


		$info=array();
		$info['id'] = $data["id"];
		$info['title'] = $data["zc_title"];
		$info['short_title'] = LQ_cutStr($data["zc_title"],30,0,'UTF-8','...');
		$info['url'] = $data["zc_url"];
        $info['zd_start_time'] = $data["zd_start_time"];
        $info['zd_end_time'] = $data["zd_end_time"];
		if($data["zc_image"]) {
			$info["image"] = API_DOMAIN.$data["zc_image"];
		}else{
			$info["image"] = NO_PICTURE;
		}
		$info["content"]=lq_format_content($data["zc_content"]);

			PAGE_S("page_activity_".$id,$info,$this->cache_options); //缓存数据
			return $info;
	}
	  
	//访问统计
	public function setViewCount($id){
		$this->where('zl_visible=1 and id='.$id)->setInc('zn_page_view',1);
		return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"] ,'data' => $this->where('zl_visible=1 and id='.$id)->getField('zn_page_view') );
	}
	//分享数量统计
	public function setShareCount($id){
		$this->where('zl_visible=1 and id='.$id)->setInc('zn_share',1);
		return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"] ,'data' => $this->where('zl_visible=1 and id='.$id)->getField('zn_share') );
	}
	//点赞数量统计
	public function setAgreeCount($id){
		$this->where('zl_visible=1 and id='.$id)->setInc('zn_agrees',1);
		return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"] ,'data' => $this->where('zl_visible=1 and id='.$id)->getField('zn_agrees') );
	}

    //////统计排名接口
    public function getRankingList()
    {
        $model = new Model();

        $result = $model->query("select tt.zn_referer_id as referer_id,tt.total,(select count(1)+1 from (select zn_referer_id,count(zn_referer_id) total from `lq_register_share` group by zn_referer_id) a where a.total>tt.total ) as rank from (select zn_referer_id,count(zn_referer_id) total from `lq_register_share` group by zn_referer_id) tt order by rank asc limit 30");
        if($result){
            foreach($result as $lnKey => $laValue)
            {
                $headimg = '';
                $headimg = $this->model_member->apiGetField("id=".$laValue["referer_id"],'zc_headimg,zc_headimg_thumb,zc_nickname');

                if($headimg['zc_headimg']){
                    if(substr($headimg['zc_headimg'],0,4)=='http'){
                        $result[$lnKey]['headimg'] = $headimg['zc_headimg'];
                    }else{
                        $img = $headimg['zc_headimg_thumb'] ? $headimg['zc_headimg_thumb'] : $headimg['zc_headimg'];

                        $result[$lnKey]['headimg'] = API_DOMAIN.$img;
                    }
                }else{
                    $result[$lnKey]['headimg'] = NO_HEADIMG;
                }
                $result[$lnKey]['referer_nickname'] = LQ_cutStr($headimg['zc_nickname'],12,0,'UTF-8','...');
            }
        }
//pr($result);
        return $result;
    }

    /////个人排名
    public function getUserSort($member_id){
        $model = new Model();

        $result = $model->query("select tt.zn_referer_id as referer_id,tt.total,(select count(1)+1 from (select zn_referer_id,count(zn_referer_id) total from `lq_register_share` group by zn_referer_id) a where a.total>tt.total ) as rank from (select zn_referer_id,count(zn_referer_id) total from `lq_register_share` group by zn_referer_id) tt order by rank asc");

        $key = array_search($member_id, array_column($result, 'referer_id'));

        if ($key !== false) {
            $info['total'] = $result[$key]["total"];
            $info['rank'] = $result[$key]["rank"];
        }
        else{
            $info['total'] = 0;
            $info['rank'] = 0;
        }

        return $info;

    }

	
}

?>
