<?php //文章系统 Article 数据处理，数据回调
namespace Api\Model;
use LQLibs\Util\Category as Category;//树状分类
defined('in_lqweb') or exit('Access Invalid!');

class ArticleModel extends PublicModel {
	protected $model_cat,$article_cat;
    // 模型名称 - 数据表名（不包含表前缀）
	protected $tableName        =   'article';	
    public function __construct() {
		parent::__construct();
		$this->model_cat=M("article_cat");//品牌模型
		$this->article_cat=lq_return_array_one(F('article_cat','',COMMON_ARRAY),'id','zc_caption');//产品分类
	}
	
	//记录总数
    public function lqCount($sqlwhere = '1'){return  $count = $this->where($sqlwhere)->count();}
	
	
	//列表页 //模板引用{$data.zc_title|lq_cutstr=30,0,'UTF-8','...'}
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' zl_visible=1 ','order'=>'`zd_send_time` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();

        $article_cat=$this->article_cat;
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['cat_id_label'] = $article_cat[$laValue["cat_id"]];
			if($laValue["image"]){
				$list[$lnKey]['image'] = API_DOMAIN.$laValue["image"];
			}else{
				$list[$lnKey]['image'] = NO_PICTURE;
			}
			$author = $laValue["zc_author"]=='' ? $laValue["zc_author"] : $laValue["zc_source"];
			$list[$lnKey]['author'] = $author =='' ? '网络' : $author;
			$list[$lnKey]['time'] = lq_cdate_format($laValue["send_time"],"Y-m-d H:i:s");
			$list[$lnKey]['send_time'] = $laValue["send_time"]-3600*TIMEZONE;//作模板使用 {$data.send_time|date="Y-m-d H:i:s",###}
			$list[$lnKey]['url'] = U("home/article/show?tnid=".$laValue["id"]);
			$list[$lnKey]['api_url'] = U("api/hd/article-show?tnid=".$laValue["id"]);
			$list[$lnKey]['webapp_url'] = U("home/webapp/article-show?tnid=".$laValue["id"]);
            $list[$lnKey]['content'] = lq_kill_html($laValue["content"],80);
            $list[$lnKey]['content_list'] = lq_kill_html($laValue["content"],20);
            $list[$lnKey]["share_url"] = API_DOMAIN ."/wx/views/strategy/details.html?tnid=".$laValue['id'];
			unset($list[$lnKey]['zc_author']);
			unset($list[$lnKey]['zc_source']);
        }
        return $list;
    }
	
	//通过ID获取文章数据 $id 文章ID ,$mustCache后台控制必须缓存
    public function getArticleById($id,$mustCache=0) {
		if($mustCache==0){
		$info=PAGE_S("page_article_".$id,'',$this->cache_options); //读取缓存数据 
		if($info) return $info;
		}
		$data = $this->where(" zl_visible=1 and id=" .$id)->find();
		if(!$data)  return 0;
		
		$info=array();
		$info['id'] = $data["id"];
		$info['title'] = $data["zc_title"];
		$info['short_title'] = LQ_cutStr($data["zc_title"],30,0,'UTF-8','...');
		if($data["zc_image"]) {
			$info["image"] = API_DOMAIN.$data["zc_image"];
		}else{
			$info["image"] = NO_PICTURE;
		}
		$info['seo_title'] = $data["zc_seo_title"]=='' ? $data["zc_title"] : $data["zc_seo_title"];
		$info['seo_keywords'] = $data["zc_seo_keywords"];
		$info['seo_description'] = $data["zc_seo_description"];
		$info["content"]=lq_format_content($data["zc_content"]);
		$info['page_view'] = $data["zn_page_view"];
		$info['api_display'] = U("api/hd/article-display?tnid=".$data["id"],'',true,true);

        $info["share_url"] = API_DOMAIN ."/wx/views/strategy/details.html?tnid=".$id;
		//分类
		$info['cat_label'] =$this->article_cat[$data["zn_cat_id"]];

		//上下页
		$data_prev=$this->field("`id`,`zc_title`")->where(" zl_visible=1 and zn_cat_id=".$data['zn_cat_id']." and id>" .$id)->order("zn_sort ASC,zd_send_time DESC")->limit("0,1")->find();
		$data_next=$this->field("`id`,`zc_title`")->where(" zl_visible=1 and zn_cat_id=".$data['zn_cat_id']." and id<" .$id)->order("zn_sort ASC,zd_send_time DESC")->limit("0,1")->find();	

			if($data_prev["id"]){
				$info['prev_id']=$data_prev["id"];
				$info['prev_title']=$data_prev["zc_title"];
				$info['prev_url']= PAGE_U("home/article/show?tnid=".$data_prev["id"]);
			}else{
				$info['prev_id']=0;
				$info['prev_title']='没有了';
				$info['prev_url']='javascript:;';				
			}
			if($data_next["id"]){
				$info['next_id']=$data_next["id"];
				$info['next_title']=$data_next["zc_title"];
				$info['next_url']= PAGE_U("home/article/show?tnid=".$data_next["id"]);
			}else{
				$info['next_id']=0;
				$info['next_title']='没有了';
				$info['next_url']='javascript:;';				
			}
			PAGE_S("page_article_".$id,$info,$this->cache_options); //缓存数据
			return $info;
	}
	//访问统计
	public function setViewCount($id){
		$this->where('zl_visible=1 and id='.$id)->setInc('zn_page_view',1);
		$page_view = $this->where('zl_visible=1 and id='.$id)->getField('zn_page_view');
		return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"] ,'data' => $page_view );
	}
	//点赞数量统计
	public function setAgreeCount($id){
		$this->where('zl_visible=1 and id='.$id)->setInc('zn_agrees',1);
		return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"] ,'data' => $this->where('zl_visible=1 and id='.$id)->getField('zn_agrees') );
	}

	/*
	精品文章数据
	@cat_id:分类ID
	@limit:获取记录数目
	return:返回的数组数据
	*/
	public function getGoodArticle($cat_id,$limit=5){
		$list=PAGE_S("good_article_".$cat_id,'',$this->cache_options); //读取缓存数据 
		if($list) return $list;		
		$cat=$this->getCatById($cat_id);
		if(!$cat) return 0;
		
		$sqlwhere_parameter=" zl_visible=1 and zl_is_good=1";//sql条件
		$tree = new Category('article_cat', array('id', 'zn_fid', 'zc_caption'));
		$child_ids = $tree->get_child($cat_id,10,'zl_visible=1');
		if (ereg("^[0-9]+$", $child_ids )){
				$sqlwhere_parameter.=" and zn_cat_id = ".intval($child_ids);
		}else{
				$sqlwhere_parameter.=" and zn_cat_id in (".$child_ids.") ";
		}
		$page_config = array(
				'field'=>"`id`,`zn_cat_id` as cat_id ,`zc_image` as image,`zc_title` as title,`zd_send_time` as send_time,`zc_summary` as summary,`zn_page_view` as page_view,`zn_share` as share,`zc_author`,`zc_source`",
				'where'=>$sqlwhere_parameter,
				'order'=>'zn_sort ASC,zd_send_time DESC',
		);		
			$list=$this->lqList(0,$limit,$page_config);
			PAGE_S("good_article_".$cat_id,$list,$this->cache_options); //缓存数据
			return $list;
	}

	/*
	首页文章数据
	@cat_id:分类ID
	@limit:获取记录数目
	return:返回的数组数据
	*/
	public function getIndexArticle($cat_id,$limit=5,$mustCache=1){
		if($mustCache==1){
		$list=PAGE_S("index_article_".$cat_id,'',$this->cache_options); //读取缓存数据 
		if($list) return $list;	
		}
		$cat=$this->getCatById($cat_id);
		if(!$cat) return 0;
		$sqlwhere_parameter=" zl_visible=1 and zl_is_index=1";//sql条件
		$tree = new Category('article_cat', array('id', 'zn_fid', 'zc_caption'));
		$child_ids = $tree->get_child($cat_id,10,'zl_visible=1');
		if (ereg("^[0-9]+$", $child_ids )){
				$sqlwhere_parameter.=" and zn_cat_id = ".intval($child_ids);
		}else{
				$sqlwhere_parameter.=" and zn_cat_id in (".$child_ids.") ";
		}
		$page_config = array(
				'field'=>"`id`,`zn_cat_id` as cat_id ,`zc_image` as image,`zc_title` as title,`zd_send_time` as send_time,`zc_summary` as summary,`zn_page_view` as page_view,`zn_share` as share,`zc_author`,`zc_source`",
				'where'=>$sqlwhere_parameter,
				'order'=>'zn_sort ASC,zd_send_time DESC',
		);		
			$list=$this->lqList(0,$limit,$page_config);
			if($mustCache==1) PAGE_S("index_article_".$cat_id,$list,$this->cache_options); //缓存数据
			return $list;
	}
		
	/*
	分类下的首页文章
	@cat_id:分类ID
	@key:列表action
	@limit:获取记录数目
	return:返回的数组数据
	*/
	public function getCatIndexArticle($cat_id,$key='index',$limit=5){
		$list=PAGE_S("cat_index_article_".$cat_id,'',$this->cache_options); //读取缓存数据 
		if($list) return $list;	
		$cat=$this->getCatById($cat_id);
		if(!$cat) return 0;
		if(!$cat["child_list"]) return 0;
		$list=$cat["child_list"];
        foreach($list as $lnKey => $laValue) {
			$list[$lnKey]["title"]=$list[$lnKey]["zc_caption"];
			$list[$lnKey]["title_alias"]=$list[$lnKey]["zc_caption_alias"];
			$list[$lnKey]['url'] = U("home/article/".$key."?tnid=".$laValue["id"]);
			$list[$lnKey]["art"]=$this->getIndexArticle($laValue["id"],$limit,0);
			unset($list[$lnKey]["zc_caption"]);
			unset($list[$lnKey]["zc_caption_alias"]);
        }
			PAGE_S("cat_index_article_".$cat_id,$list,$this->cache_options); //缓存数据
			return $list;
	}
	//通过ID - 获取当前类别信息及子级分类
    public function getCatById($id) {
		$id=intval($id);
		$data=PAGE_S("article_cat_".$id,'',$this->cache_options); //读取缓存数据
		if($data) return $data;
			$data=F('article_cat','',COMMON_ARRAY)[$id];	
			if(!$data)  return 0;
			if($data["zl_visible"]==0) return 0;
			if($id===$data["child_ids"]){
				$data['child_list'] = array();
			}else{
				$data['child_list'] = $this->getChildList($id);
			}
			PAGE_S("article_cat_".$id,$data,$this->cache_options); //缓存数据
			return $data;
	}
	
	//获取同一父级下的子分类（仅上下级）
    public function getChildList($fid) {
			return $this->model_cat->field("`id`,`zc_caption`,`zc_caption_alias`")->where(" zl_visible=1 and zn_fid=".$fid)->order("`zn_sort` ASC,`id` DESC")->select();
	}

	
}

?>
