<?php //短信系统 Article 数据处理，数据回调
namespace Home\Model;
use Think\Model;


defined('in_lqweb') or exit('Access Invalid!');

class SearchModel extends PublicModel
{
    private $model_lesson, $model_favorite,$model_vod_record, $model_enroll,$model_member,$model_lesson_vod,$model_lesson_live,$model_live,$model_vod;
    /* 会员模型自动验证 */

    public function __construct()
    {
        parent::__construct();
        $this->model_vod = D("Vod");
        $this->model_live = D("Live");
        $this->model_lesson_vod = D("LessonVod");
        $this->model_favorite = D("MemberFavorite");
        $this->model_enroll = D("MemberEnroll");
        $this->model_lesson_live = D("LessonLive");
        $this->model_member = M("Member");
        $this->model_vod_record = D('Vodrecord');


    }

    ////通过条件获取字段
    public function lqGetField($sql, $field)
    {
        $field_arr = split(',', $field);
        if ($field == "*") {
            return $this->where($sql)->field("*")->find();
        } else {
            if (count($field_arr) > 1) {
                return $this->where($sql)->field($field)->find();
            } else {
                return $this->where($sql)->getField($field);
            }
        }
    }

    ////通过条件获取字段
    public function lqGetField_select($sql, $field)
    {
        $field_arr = split(',', $field);
        if ($field == "*") {
            return $this->where($sql)->field("*")->select();
        } else {
            if (count($field_arr) > 1) {
                return $this->where($sql)->field($field)->select();
            } else {
                return $this->where($sql)->Field($field)->select();
            }
        }
    }

    ///添加数据
    public function addData($data)
    {
        if (empty($data)) {
            return '参数错误！';
        }
        $data = $this->create($data);//验证
        if (!$data) {
            return $this->getError(); //错误详情见自动验证注释
        } else {
            $mid = $this->add($data);
            return $mid ? $mid : 0; //0-未知错误，大于0-注册成功
        }

    }

    ///保存数据
    public function saveData($data)
    {
        if (empty($data)) {
            return '参数错误！';
        }
        $mid = $this->save($data);
        return $mid ? $mid : 0; //0-未知错误，大于0-注册成功
    }

    ///删除数据
    public function delData($id, $zn_teacher_id)
    {
        if (empty($id)) {
            return '参数错误！';
        }
        // 判断删除的是否是自己的课程
        $data = array(
            'id' => $id,
            'zn_teacher_id' => $zn_teacher_id,
        );
        $list = $this->where($data)->find();

        if (!$list) {
            return -1; //错误详情见自动验证注释
        } else {
            $mid = $this->where($data)->delete();
            return $mid ? $mid : 0; //0-未知错误，大于0-删除成功
        }

    }


    ///统计
    public function getCount($where = '')
    {
        if ($where) return $this->where($where)->count();
        else return $this->count();

    }


    /*
     * 获取直据
     */
    //列表页 //模板引用{$data.zc_title|lq_cutstr=30,0,'UTF-8','...'}
    public function lqList($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' zl_visible=1 ', 'order' => '`id` DESC'))
    {
        // 选择字段,设置条件,进行排序,分页限制,查询
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();

        // 有公共缓存就从缓存中读取数据
        $cat = F('lesson_cat', '', COMMON_ARRAY);

        // 遍历处理而外地信息
        foreach ($list as $lnKey => $laValue) {
            // 从缓存中拿出课程信息
            $list[$lnKey]['cat_id_label'] = $cat[$laValue["zn_cat_id"]]['zc_caption'];

            // 判断是否有图片 (处理图片)
            // API_DOMAIN =>  网站地址
            if ($laValue["image"]) {
                $list[$lnKey]['image'] = API_DOMAIN . $laValue["image"];
            } else {
                $list[$lnKey]['image'] = NO_PICTURE;
            }
            // 调用了lesson Model  设置统计条件
            // 统计已经完成的课程
            $complete_sql = array(
                "zc_date" => date("Y-m-d"),// 今天
                "zc_start_time" => array('egt', date("H:i")),
            );
            $list[$lnKey]['finish_class'] = $this->model_lesson->getCount("zn_cat_id = '" . $laValue['id'] . "' and zl_status=1");

            // 统计全部的课程
            $list[$lnKey]['all_class'] = $this->model_lesson->getCount("zn_cat_id = '" . $laValue['id'] . "'");

            // 获取下一节课的时间和推流地址
            $next_class = $this->model_lesson->where("zn_cat_id = '" . $laValue['id'] . "' and zl_status=0")->order("zc_date asc,zc_start_time asc")->field('zc_date,zc_start_time,zc_push_url')->find();

            // 计算时间
            unset($last_time);
            unset($next_time);
            $next_time = strtotime($next_class['zc_date'] . $next_class['zc_start_time']);

            $last_time = $next_time - time();
//             计算距离下节课还有多久/判断时间
            if ($last_time > 0) {
                $list[$lnKey]['next_class_time'] = date('Y,m,d,h,i,s', $next_time);

                // 分割时间
                $day = date('d', $last_time);
                $list[$lnKey]['next_d1'] = substr($day, 0, 1);
                $list[$lnKey]['next_d2'] = substr($day, 1, 1);

                $hour = date('H', $last_time);
                $list[$lnKey]['next_h1'] = substr($hour, 0, 1);
                $list[$lnKey]['next_h2'] = substr($hour, 1, 1);

                $minute = date('i', $last_time);
                $list[$lnKey]['next_i1'] = substr($minute, 0, 1);
                $list[$lnKey]['next_i2'] = substr($minute, 1, 1);
            } else {
                $list[$lnKey]['next_class_time'] = 0;

                $list[$lnKey]['next_d1'] = 0;
                $list[$lnKey]['next_d2'] = 0;

                $hour = date('H', $last_time);
                $list[$lnKey]['next_h1'] = 0;
                $list[$lnKey]['next_h2'] = 0;

                $minute = date('i', $last_time);
                $list[$lnKey]['next_i1'] = 0;
                $list[$lnKey]['next_i2'] = 0;

            }

            // 获取下一节课的url推流地址
            $list[$lnKey]['next_class_url'] = $next_class['zc_push_url'];

            // 获取直播收藏数量
            $list[$lnKey]['favorite'] = $this->model_favorite->getCount("zn_object_id = '" . $laValue['id'] . "' and zn_type = 1");

            // 获取直播关注数量
            $list[$lnKey]['enroll'] = $this->model_enroll->getCount("zn_object_id = '" . $laValue['id'] . "' and zn_type = 1");

            // 获取相应状态的相应按钮
            $list[$lnKey]['btn'] = lq_button($list[$lnKey]['zl_status'], $list[$lnKey]['id'],$list[$lnKey]['zc_reason']);

            // 获取相应图片信息
            $list[$lnKey]['msg'] = lq_picture($laValue['zl_status'])['message'];
            $list[$lnKey]['pic'] = lq_picture($laValue['zl_status'])['status'];
            $list[$lnKey]['rank'] = get_ranking($laValue['id'],1);
        }
        return $list;
    }

    /*
 * 获取直播数据
 */
    //列表页 //模板引用{$data.zc_title|lq_cutstr=30,0,'UTF-8','...'}
    public function lqList_live($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' zl_visible=1 ', 'order' => '`id` DESC'))
    {
        // 选择字段,设置条件,进行排序,分页限制,查询
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        // 遍历处理而外地信息
        foreach ($list as $lnKey => $laValue) {
            // 获取老师的学校
            $list[$lnKey]['school'] = $this->model_member->where('id='. $laValue['zn_teacher_id'])->getField('`zc_school`');
            // 统计直播课
            $list[$lnKey]['all_class'] = $this->model_lesson->getCount("zn_cat_id = '" . $laValue['id'] . "'");
            // 判断是否有图片 (处理图片)
            // API_DOMAIN =>  网站地址
            if ($laValue["zc_image"]) {
                $list[$lnKey]['zc_image'] = API_DOMAIN . $laValue["zc_image"];
            } else {
                $list[$lnKey]['zc_image'] = NO_PICTURE;
            }
        }
        return $list;
    }



    // 获取直播课程数据
    public function lq_getmessage($sql = array('field' => '*', 'where' => ' zl_visible=1 ', 'order' => '`id` DESC'))
    {
        // 选择字段,设置条件,进行排序,分页限制,查询
        $list = $this->field($sql["field"])->where($sql["where"])->order($sql["order"])->find();
        // 有公共缓存就从缓存中读取数据
        $cat = F('lesson_cat', '', COMMON_ARRAY);
        // 从缓存中拿出课程信息
        $list['cat_id_label'] = $cat[$list["zn_cat_id"]]['zc_caption'];
        $list['zc_caption'] = $cat[$list['zn_fid']]['zc_caption'];
        // 判断是否有图片 (处理图片)
        if ($list["image"]) {
            $list['image'] = API_DOMAIN . $list["image"];
        } else {
            $list['image'] = NO_PICTURE;
        }
        return $list;
    }


    /**
     *    搜索 - 老师 - 获取数据
     * @param $list 二维数组 包含老师id
     */
    public function teacher_search($list)
    {
        $cache = S(array('prefix'=>'search_teacher_','expire'=>600));
        foreach($list as $key => $value){
            $sql = 'zn_teacher_id='.$value['id'];
            if(!$cache->$sql){
                $list[$key]['student_num'] = $this->model_enroll->where($sql)->count(); // 统计学生数量
                $list[$key]['live_course'] = $this->model_live->where($sql)->count(); // 统计直播课程
                $list[$key]['vod_course'] = $this->model_vod->where($sql)->count(); // 统计录播课程
                $list[$key]['favorite_num'] = $this->model_favorite->where($sql)->count(); // 统计收藏数量
                $cache->$sql = $list[$key];
            }else{
                $list[$key] = $cache->$sql;
            }
        }
        return $list;
    }

    /**
 *    搜索 - 老师 - 获取数据
 * @param $list 二维数组 包含老师id
 */
    public function live_search($list)
    {
        $cache = S(array('prefix'=>'search_live_','expire'=>600));
        $cat = F('lesson_cat','',COMMON_ARRAY);
        foreach($cat as $key => $value)
        {// 获取分类相应的数据
            $lq_cat[$value['id']] = $value['zc_caption'];
        }

        foreach($list as $key => $value){
            $sql = 'live_id='.$value['id'];
            if(!$cache->$sql){
                // 获取时间
                $res = $this->model_lesson_live->getNewestLesson($value['id']);
                $list[$key]['zc_date'] = $res['zc_date'];
                $list[$key]['zc_start_time'] = $res['zc_start_time'];
                $list[$key]['zc_end_time'] = $res['zc_end_time'];
                // 获取二级分类名字
                $list[$key]['zn_cat_name'] = $lq_cat[$list[$key]['zn_cat_id']];
                // 处理无图片
                if ($value["zc_image"]) {
                    $list[$key]['zc_image'] = API_DOMAIN . $value["zc_image"];
                } else {
                    $list[$key]['zc_image'] = NO_PICTURE;
                }
                $cache->$sql = $list[$key];
            }else{
//                unset($cache->$sql);
                $list[$key] = $cache->$sql;
            }
        }
        return $list;
    }



    /**
     *    搜索 - 老师 - 获取数据
     * @param $list 二维数组 包含老师id
     */
    public function vod_search($list)
    {
        $cache = S(array('prefix'=>'search_vod_','expire'=>600));
        $cat = F('lesson_cat','',COMMON_ARRAY);
        foreach($cat as $key => $value)
        {// 获取分类相应的数据
            $lq_cat[$value['id']] = $value['zc_caption'];
        }
        foreach($list as $key => $value){
            $sql = 'vod_id='.$value['id'];
            if(!$cache->$sql){
                // 获取课时
                $list[$key]['lesson_num'] = $this->model_lesson_vod->getCount('zn_cat_id = '.$value['id']);
                // 获取二级分类名字
                $list[$key]['zn_cat_name'] = $lq_cat[$list[$key]['zn_cat_id']];
                // 处理无图片
                if ($value["zc_image"]) {
                    $list[$key]['zc_image'] = API_DOMAIN . $value["zc_image"];
                } else {
                    $list[$key]['zc_image'] = NO_PICTURE;
                }
                $cache->$sql = $list[$key];
            }else{
                $list[$key] = $cache->$sql;
            }
        }
        return $list;
    }

    /*
     * 获取老师录播资料
     * return 大课信息和小课id,title
     */
    public function get_vod($teacher_id,$firstRow = 0, $listRows = 20)
    {
        // 获取点击了最多的2节录播
        $vod = $this->model_vod
            ->where(array('zn_teacher_id' => $teacher_id , 'zl_status' => 6))
            ->order('zn_enroll_num desc,zn_fav_num desc')
            ->field('id,zn_fid,zn_cat_id,zc_image,zc_title,zc_summary')
            ->limit("$firstRow , $listRows")
            ->select();
        if(!$vod) return false;// 没有返回空
        $count = 0;
        $cat = get_lesson_cat();// 获取分类
        foreach($vod as $key => &$value){
            // 处理无图片
            if ($value["zc_image"]) {
                $vod[$key]['zc_image'] = API_DOMAIN . $value["zc_image"];
            } else {
                $vod[$key]['zc_image'] = NO_PICTURE;
            }
            $value['zn_fid_name'] = return_cat($value['zn_fid']);// 获取大课
            $value['zn_cat_name'] = return_cat($value['zn_cat_id']);// 获取小课
            $value['vod_lesson'] = $this->model_lesson_vod
                ->where(array('zn_cat_id' => $value['id'],'zl_visible' => 1))
                ->field('id,zc_title  as lesson_title')
                ->order('zn_cdate asc,id asc')
                ->select();
            $value['count'] = count($value['vod_lesson']);
            $value['pageviews'] = $this->model_vod_record->where(array('zn_vod_id' => $value['id']))->count();
        }
        return $vod;
    }

    /*
 * 获取老师直播资料
 */
    public function get_live($teacher_id,$firstRow = 0, $listRows = 20)
    {
        $where_sql = array(
            "zn_teacher_id" =>$teacher_id,
            "zl_status" => array('in','1,6'),
        );
        // 获取直播数据
        $live = $this->model_live
            ->field('zc_image,zc_title,zl_status,id,zn_cdate')
            ->where($where_sql)
            ->order('zl_status desc,zn_cdate desc')
            ->limit("$firstRow , $listRows")
            ->select();
        if(!$live) return false; // 没有数据返回false
        foreach($live as $key => &$value){
            // 处理无图片
            if ($value["zc_image"]) {
                $live[$key]['zc_image'] = API_DOMAIN . $value["zc_image"];
            } else {
                $live[$key]['zc_image'] = NO_PICTURE;
            }
            $value['time'] = date('Y-m-d H:i',$value['zn_cdate']);// 处理时间
            // 获取报名数
            if($value['zl_status'] == 6){
                $enroll = $this->model_enroll
                    ->where(array('zn_object_id' => $value['id']))
                    ->count();
                $value['enroll'] = $enroll;
            }
        }
        return $live;
    }


}

?>
