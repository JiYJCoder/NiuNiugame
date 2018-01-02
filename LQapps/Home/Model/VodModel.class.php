<?php //短信系统 Article 数据处理，数据回调
namespace Home\Model;
use Think\Model;
use Member\Api\MemberApi;
use Home\ORG\CacheVodTalk;

defined('in_lqweb') or exit('Access Invalid!');

class VodModel extends PublicModel
{
    private $model_lesson, $model_favorite, $model_enroll;
    /* 会员模型自动验证 */
    protected $_validate = array(
        array('zn_fid', 'lqrequire', '请选择一级分类！', self::MUST_VALIDATE),
        array('zn_cat_id', 'lqrequire', '请选择二级分类！', self::MUST_VALIDATE),
        array('zn_teacher_id', 'require', '参数不能为空！', self::EXISTS_VALIDATE),
        array('zc_title', 'require', '课程名称必须填写', self::MUST_VALIDATE),
        array('zc_title', '1,100', '课程名称在1~100个字符', self::MUST_VALIDATE, 'length'),
    );

    /* 会员模型自动完成 */
    protected $_auto = array(
        array('zl_status', 4, self::MODEL_INSERT),
        array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
        array('zn_mdate', NOW_TIME, self::MODEL_BOTH)
    );

    public function __construct()
    {
        parent::__construct();
        $this->model_lesson = D("LessonVod");
        $this->model_favorite = D("MemberFavorite");
        $this->model_enroll = D("MemberEnroll");
        $this->model_member = M("Member");


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

    // 获取录播数据
    //列表页 //模板引用{$data.zc_title|lq_cutstr=30,0,'UTF-8','...'}
    public function recordedList($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' zl_visible=1 ', 'order' => '`id` DESC'))
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
            // 统计预计多少节课
            $list[$lnKey]['upload_class'] = $this->model_lesson->getCount("zn_cat_id = '" . $laValue['id'] . "'");
            // 统计已经完成的课程
            $list[$lnKey]['finish_class'] = $this->model_lesson->getCount("zn_cat_id = '" . $laValue['id'] . "' and zl_visible=1");
            // 统计全部的课程
            $list[$lnKey]['all_class'] = $this->model_lesson->getCount("zn_cat_id = '" . $laValue['id'] . "'");
            // 获取直播收藏数量
            $list[$lnKey]['favorite'] = $this->model_favorite->getCount("zn_object_id = '" . $laValue['id'] . "' and zn_type = 2");
            // 获取直播关注数量
            $list[$lnKey]['enroll'] = $this->model_enroll->getCount("zn_object_id = '" . $laValue['id'] . "' and zn_type = 2");
            // 按钮
            $list[$lnKey]['btn'] = vod_button($list[$lnKey]['zl_status'], $list[$lnKey]['id'], $list[$lnKey]['zc_reason']);
            // 获取相应图片信息
            $list[$lnKey]['msg'] = vod_picture($laValue['zl_status'])['message'];
            $list[$lnKey]['pic'] = vod_picture($laValue['zl_status'])['status'];
            $list[$lnKey]['rank'] = get_ranking($laValue['id'], 2);

        }
        // pr($list);
        return $list;
    }

    // 获取录播课程数据
    public function vod_getmessage($sql = array('field' => '*', 'where' => ' zl_visible=1 ', 'order' => '`id` DESC'))
    {
        // 选择字段,设置条件,进行排序,分页限制,查询
        $list = $this->field($sql["field"])->where($sql["where"])->order($sql["order"])->find();

        // 判断查询是否成功
        if (!$list) {
            return '查询失败';
        }
        // 有公共缓存就从缓存中读取数据
        $cat = F('lesson_cat', '', COMMON_ARRAY);

        // 从缓存中拿出课程信息
        $list['cat_id_label'] = $cat[$list["zn_cat_id"]]['zc_caption'];
        $list['zc_caption'] = $cat[$list['zn_fid']]['zc_caption'];

        // 判断是否有图片 (处理图片)
        if ($list["zc_image"]) {
            $list['zc_image'] = API_DOMAIN . $list["zc_image"];
        } else {
            $list['zc_image'] = NO_PICTURE;
        }

        return $list;

    }

    /**
     * 删除其中一个file
     * @param int 课程id
     * @param int 要删除的文件id
     * return string 被删除的文件名
     */
    public function deleteFile($live_id, $file_id)
    {
        if (!$live_id || !$file_id) return false;

        $files = explode(",", $this->lqGetField("id=" . $live_id, "zc_file"));
        $key = array_search($file_id, $files);
        if ($key) unset($files[$key]);
        $saveData = array(
            "id" => $live_id,
            "zc_file" => implode(",", $files)
        );
        $this->saveData($saveData);
        ////删除oss
        return D("OssFile")->getAndDelObj($file_id);
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

    // 获取数据
    public function lq_getmessage($sql = array('field' => '*', 'where' => ' zl_visible=1 ', 'order' => '`id` DESC'))
    {
        // 选择字段,设置条件,进行排序,分页限制,查询
        $list = $this->field($sql["field"])->where($sql["where"])->order($sql["order"])->select();

        // 判断查询是否成功
        if (!$list) {
            return '查询失败';
        }
        return $list;
    }

    /*
     * 首页 - 第三级 - 获取/处理数据
     */
    public function vodList($page_config = array('field' => '*', 'where' => ' zl_visible=1 ', 'order' => '`id` DESC'), $limit = 10)
    {
        // 选择字段,设置条件,进行排序,分页限制,查询
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit($limit)->select();

        // 遍历处理而外地信息
        foreach ($list as $lnKey => $laValue) {

            // 判断是否有图片 (处理图片)
            // API_DOMAIN =>  网站地址
            if ($laValue["zc_image"]) {
                $list[$lnKey]['zc_image'] = API_DOMAIN . $laValue["zc_image"];
            } else {
                $list[$lnKey]['zc_image'] = NO_PICTURE;
            }
            // 获取老师的学校
            $list[$lnKey]['school'] = $this->model_member->where('id=' . $laValue['zn_teacher_id'])->getField('`zc_school`');

            // 统计全部的课程
            $list[$lnKey]['all_class'] = $this->model_lesson->getCount("zn_cat_id = '" . $laValue['id'] . "'");

            // 获取收藏数量
            $list[$lnKey]['favorite'] = $this->model_favorite->getCount("zn_object_id = '" . $laValue['id'] . "' and zn_type = 2");

            // 获取关注数量
            $list[$lnKey]['enroll'] = $this->model_enroll->getCount("zn_object_id = '" . $laValue['id'] . "' and zn_type = 2");
            // 排名
//            $list[$lnKey]['rank'] = get_ranking($laValue['id'],1);

        }
        // pr($list);
        return $list;
    }


    /*
* 通过关键字，获取直播数据
* return array()
*/
    public function getIdsByKeyword($kw)
    {
        $map['zl_status'] = 6;
        $map['zc_title'] = array("LIKE", "%" . $kw . "%");
        $reArr = array();
        $member = $this->field("id")->where($map)->select();
        foreach ($member as $val) {
            $reArr[] = $val['id'];
        }
        return $reArr;
    }


    /*
    * 课程页 详细信息
    */
    public function getLessonInfo($id)
    {
        $field = array("id", "zn_fid", "zn_cat_id","zc_image", "zn_teacher_id", "zc_title", "zc_summary", "zc_content", "zc_file", "zn_fav_num", "zn_enroll_num");
        $where = array(
            "zl_status" => array("IN","1,6"),
            "id" => $id
        );
        ////获取记录
        $rs = $this->field($field)->where($where)->find();
        if (!$rs) return false;
        /////整合其它信息
        ///课程分类

        $rs['fid_label'] = return_cat($rs['zn_fid']);
        $rs['cat_id_label'] = return_cat($rs['zn_cat_id']);
        ///是否已收藏，已报名
        $rs['is_fav'] = $this->model_favorite->is_member_fav(session('student_auth')['id'], $rs['id'], 2);
        $rs['is_enroll'] = $this->model_enroll->is_member_enroll(session('student_auth')['id'], $rs['id'], 2);



        //////课节明细
        $lesson = $this->model_lesson->field(array("id" => "lesson_id", "zc_title" => "lesson_title", ))->where(array("zl_visible" => 1, "zn_cat_id" => $rs['id']))->order("zn_cdate asc,id asc")->select();
        if (lq_is_login("student")) {
            $db_record = M("VodRecord");// 实例化
            $member_id = session("student_auth")['id']; // 用户id
            $is_new = $db_record->where('zn_vod_id = '.$rs['id'].' and zn_member_id = ' .$member_id)->order('zn_cdate desc')->field('zn_lesson_id')->find();// 查询最近观看的录播

            // 判断是否有最近观看记录
            if($is_new){
                $rs['new_lesson_id'] = $is_new['zn_lesson_id'];
            }else {
                $rs['new_lesson_id'] = $lesson[0]['lesson_id'];
                $lesson[0]['is_new'] = 1;
            }
            foreach ($lesson as $lk => $lv) {
                if($lv['lesson_id'] == $is_new['zn_lesson_id']){
                    $lesson[$lk]['is_new'] = 1; // 是最新就赋值为1
                }
                $lesson[$lk]['is_study'] = $db_record->where(array("zn_lesson_id" => $lv['lesson_id'], "zn_member_id" => $member_id))->getField('id') ? 1 : 0;
                $lesson[$lk]['zc_vod_time'] = $db_record->where(array("zn_lesson_id" => $lv['lesson_id'], "zn_member_id" => $member_id))->getField('zc_vod_time');
                if(!$lesson[$lk]['zc_vod_time']) $lesson[$lk]['zc_vod_time'] = 0;
            }
        }
        /////课程附件
        if ($rs['zc_file']) {
            $oss_where = array(
                "id" => array("IN", explode(",", $rs['zc_file']))
            );
            $oss_file = M("OssFile")->field(array("id" => "file_id", "zc_file_name","zc_file_path", "zc_suffix", "zn_cdate"))->where($oss_where)->select();
            foreach ($oss_file as $ok => $ov) {
                $style = "default";
                $file_type = search_from_array($ov['zc_suffix'], C("OSS_FILE"));
                $oss_file[$ok]['style'] = $file_type ? $file_type : $style;
            }
            $rs['oss_file'] = $oss_file;
        }
        $rs['lessonInfo'] = $lesson;

        return $rs;
    }


    /////录播界面课程信息
    public function  getVodInfo($id)
    {
        $field = array("id", "zn_fid", "zn_cat_id", "zn_teacher_id");
        $where = array(
            "zl_status" => 6,
            "id" => $id
        );
        ////获取记录
        $rs = $this->field($field)->where($where)->find();
        if (!$rs) return false;
        $rs['fid_label'] = return_cat($rs['zn_fid']);
        $rs['cat_id_label'] = return_cat($rs['zn_cat_id']);
        return $rs;
    }


    // 处理聊天信息
    public function  Chat_data($lesson_id,$uid)
    {
        $talk_cache = new cacheVodTalk($lesson_id);
        $msg = $talk_cache->get_talk();

        $str = '';
        foreach($msg as $key => $value)
        {
            if($uid == $value['uid']){
                $str .= "<div class='row my-row clearfix'>
                            <div class='info''>
                                <img class='user-header-img' src='" . $value['img'] . "' >
                                <span class='user-name''>我</span>
                            </div>
                            <div class='chat-text'>
                               ". $value['value'] ."
                                <i class='bottomLevel'></i>
                            </div>
                        </div>";
            }else{
                $str .= "<div class='row clearfix'>
                            <div class='info''>
                                <img class='user-header-img' src='" . $value['img'] . "' >
                                <span class='user-name''>" .$value['name'] ."</span>
                            </div>
                            <div class='chat-text'>
                               ". $value['value'] ."
                                <i class='bottomLevel'></i>
                            </div>
                        </div>";
            }
        }
        return $str;
    }


}

?>
