<?php //短信系统 Article 数据处理，数据回调
namespace Home\Model;

use Think\Model;
use Home\ORG\CacheTalk;
use Video\Api\liveApi;
use Member\Api\MemberApi as MemberApi;


defined('in_lqweb') or exit('Access Invalid!');

class LiveModel extends PublicModel
{
    private $model_lesson, $model_favorite, $model_enroll, $model_member;
    /* 会员模型自动验证 */
    protected $_validate = array(
        array('zn_fid', 'lqrequire', '请选择一级分类！', self::MUST_VALIDATE),
        array('zn_cat_id', 'lqrequire', '请选择二级分类！', self::MUST_VALIDATE),
        array('zn_teacher_id', 'require', '参数不能为空！', self::EXISTS_VALIDATE),
        array('zc_title', 'require', '课程名称必须填写', self::MUST_VALIDATE),
        array('zc_title', '1,100', '课程名称在1~100个字符', self::MUST_VALIDATE, 'length'),
        array('zc_teacher_name', '1,10', '操作名称在1~10个字符间', self::MUST_VALIDATE, 'length'),
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
        $this->model_lesson = D("LessonLive");
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
     * 获取直播数据
     */
    //列表页 //模板引用{$data.zc_title|lq_cutstr=30,0,'UTF-8','...'}
    public function lqList($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' id>1 ', 'order' => '`id` DESC'))
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
                'zn_cat_id' => $laValue['id'],
                "zc_date" => array('lt', date("Y-m-d")),// 条件:已过去的时间
            );
            $list[$lnKey]['finish_class'] = $this->model_lesson->where($complete_sql)->count();
            // 统计全部的课程
            $list[$lnKey]['all_class'] = $this->model_lesson->getCount("zn_cat_id = '" . $laValue['id'] . "'");
            // 判断是否还有下节课
            if( $list[$lnKey]['finish_class'] ==  $list[$lnKey]['all_class']){
                $list[$lnKey]['living'] = 2;// 2表示没有课了
            } else {
                // 获取下一节课的时间和推流地址
                $next_class = $this->model_lesson->where("zn_cat_id = '" . $laValue['id'] . "' and zl_status=0")->order("zc_date asc,zc_start_time asc")->field('zc_date,zc_start_time,zc_end_time,zc_push_url')->find();

                // 计算时间
                unset($last_time);
                unset($next_time);
                unset($next_end_time);
                unset($last_end_time);
                $next_time = strtotime($next_class['zc_date'] . $next_class['zc_start_time']);
                $next_end_time = strtotime($next_class['zc_date'] . $next_class['zc_end_time']);

                $last_time = $next_time - time();
                $last_end_time = $next_end_time - time();
//             计算距离下节课还有多久/判断时间
                if ($last_time > 0) {
                    $list[$lnKey]['next_class_time'] = date('Y,m,d,h,i,s', $next_time);
                    $temp_time = lq_surplus_second($last_time);

                    // 分割时间
//                    $day = date('d', $last_time);
                    $list[$lnKey]['next_d1'] = substr($temp_time[0], 0, 1);
                    $list[$lnKey]['next_d2'] = substr($temp_time[0], 1, 1);

//                    $hour = date('H', $last_time);
                    $list[$lnKey]['next_h1'] = substr($temp_time[1], 0, 1);
                    $list[$lnKey]['next_h2'] = substr($temp_time[1], 1, 1);

//                    $minute = date('i', $last_time);
                    $list[$lnKey]['next_i1'] = substr($temp_time[2], 0, 1);
                    $list[$lnKey]['next_i2'] = substr($temp_time[2], 1, 1);
                } else {
                    if($last_end_time > 0){
                        $list[$lnKey]['living'] = 1;
                    }else{
                        $list[$lnKey]['living'] = 2;
                        $list[$lnKey]['finish_class'] = $list[$lnKey]['all_class'];
                    }
                }
                // 获取下一节课的url推流地址
                $list[$lnKey]['zc_push_url_first'] = C('ALI_API')['push_url_first']; // 直播地址前缀
                $list[$lnKey]['zc_push_url_second'] = substr($next_class['zc_push_url'],strlen($list[$lnKey]['zc_push_url_first'])+1); // 直播地址后缀
            }
            // 获取直播收藏数量
            $list[$lnKey]['favorite'] = $this->model_favorite->getCount("zn_object_id = '" . $laValue['id'] . "' and zn_type = 1");
            // 获取直播关注数量
            $list[$lnKey]['enroll'] = $this->model_enroll->getCount("zn_object_id = '" . $laValue['id'] . "' and zn_type = 1");
            // 获取相应状态的相应按钮
            $list[$lnKey]['btn'] = lq_button($list[$lnKey]['zl_status'], $list[$lnKey]['id'], $list[$lnKey]['zc_reason']);
            // 获取相应图片信息
            $list[$lnKey]['msg'] = lq_picture($laValue['zl_status'])['message'];
            $list[$lnKey]['pic'] = lq_picture($laValue['zl_status'])['status'];
            $list[$lnKey]['rank'] = get_ranking($laValue['id'], 1);
        }
        return $list;
    }

    /*
 * 获取直播数据
 */
    //列表页 //模板引用{$data.zc_title|lq_cutstr=30,0,'UTF-8','...'}
    public function lqList_live($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' id>1 ', 'order' => '`id` DESC'))
    {
        // 选择字段,设置条件,进行排序,分页限制,查询
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        // 遍历处理而外地信息
        foreach ($list as $lnKey => $laValue) {
            // 获取老师的学校
            $list[$lnKey]['school'] = $this->model_member->where('id=' . $laValue['zn_teacher_id'])->getField('`zc_school`');
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
    public function lq_getmessage($sql = array('field' => '*', 'where' => ' id>1 ', 'order' => '`id` DESC'))
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
     * 生成streamName (规则：老师id + 课程id + 课节id)
     * 传入 课节id 课程id
     * 返回 streamName
     */
    public function getStreamName($lesson_id, $live_id)
    {
        if (!$live_id || !$lesson_id) return false;
        $teacher_id = $this->where("id=" . $live_id)->getField("zn_teacher_id");

        if ($teacher_id) return $teacher_id . "_" . $live_id . "_" . $lesson_id;
        else return false;
    }

    /*
     * 课程页 详细信息
     */
    public function getLessonInfo($id)
    {
        $field = array("id", "zn_fid", "zn_cat_id","zc_image", "zn_teacher_id", "zc_title", "zc_summary", "zc_content", "zc_file", "zn_fav_num", "zn_enroll_num");
        $where = array(
            "zl_status" => array('neq',2),
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
        $rs['is_fav'] = $this->model_favorite->is_member_fav(session('student_auth')['id'], $rs['id'], 1);
        $rs['is_enroll'] = $this->model_enroll->is_member_enroll(session('student_auth')['id'], $rs['id'], 1);
        ////剩余课节
        $rs['lesson_left'] = $this->model_lesson->unLiveLession($rs['id']);
        ///上次直播时间
        $lastLive = $this->model_lesson->getLastLive($rs['id']);
        if (!$lastLive) {
            $rs['newest_time_date'] = '这是第一节课哦';
        } else {
            $rs['newest_time_date'] = date("Y-m-d", $lastLive['zc_live_start']);
            $rs['newest_time_hour'] = date("H:i", $lastLive['zc_live_start']) . "-" . date("H:i", end(explode("||", $lastLive['zc_live_end'])));
        }
        //////课节明细
        $lesson = $this->model_lesson->field(array("id" => "lesson_id", "zc_vod_url", "zc_title" => "lesson_title", "zc_date", "zc_start_time", "zc_end_time"))->where(array("zl_visible" => 1, "zn_cat_id" => $rs['id']))->order("zc_date asc,zc_start_time asc,id asc")->select();
        if (lq_is_login("student")) {
            $db_record = M("LiveRecord");
            $member_id = session("student_auth")['id'];
            foreach ($lesson as $lk => $lv) {
                $lesson[$lk]['is_study'] = $db_record->where(array("zn_lesson_id" => $lv['lesson_id'], "zn_member_id" => $member_id))->getField('id') ? 1 : 0;
                if ($lv['zc_vod_url']) {
                    $urlParams = "replay_live_id=" . $lv['lesson_id'];
                    $url = U("/Live/replay", array("s" => think_ucenter_encrypt($urlParams, C('SYS_ENCRYPT_PWD'))));
                    $lesson[$lk]['url'] = $url;
                }
            }
        }
        /////课程附件
        if ($rs['zc_file']) {
            $oss_where = array(
                "id" => array("IN", explode(",", $rs['zc_file']))
            );
            $oss_file = M("OssFile")->field(array("id" => "file_id", "zc_file_name", "zc_file_path", "zc_suffix", "zn_cdate"))->where($oss_where)->select();
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

    /////直播界面课程信息
    public function getLiveInfo($id)
    {
        $field = array("id", "zn_fid", "zn_cat_id", "zn_teacher_id");
        $where = array(
            "zl_status" => 6,
            "id" => $id
        );
        ////获取记录
        $rs = $this->field($field)->where($where)->find();
//        echo $this->getLastSql();
        if (!$rs) return false;
        $rs['fid_label'] = return_cat($rs['zn_fid']);
        $rs['cat_id_label'] = return_cat($rs['zn_cat_id']);
        return $rs;
    }

    // 处理聊天信息
    public function Chat_data($lesson_id, $uid)
    {
        $talk_cache = new cacheTalk($lesson_id);
        $msg = $talk_cache->get_talk();

        $str = '';
        foreach ($msg as $key => $value) {
            if ($uid == $value['uid']) {
                $str .= "<div class='row my-row clearfix'>
                            <div class='info''>
                                <img class='user-header-img' src='" . $value['img'] . "' >
                                <span class='user-name''>我</span>
                            </div>
                            <div class='chat-text'>
                               " . $value['value'] . "
                                <i class='bottomLevel'></i>
                            </div>
                        </div>";
            } else {
                $str .= "<div class='row clearfix'>
                            <div class='info''>
                                <img class='user-header-img' src='" . $value['img'] . "' >
                                <span class='user-name''>" . $value['name'] . "</span>
                            </div>
                            <div class='chat-text'>
                               " . $value['value'] . "
                                <i class='bottomLevel'></i>
                            </div>
                        </div>";


            }
        }
        return $str;
    }

    // 获取今天3次直播课程
    public function get_today_live($live_msg = [])
    {

        $data['MemberEnroll.zn_member_id'] = session('student_auth')['id'];// 学生id
        $data['MemberEnroll.zn_type'] = 1;//1是直播
//        $data['LessonLive.zl_status'] = 0;//0-未直播
        $data['Live.zl_status'] = 6; // 6直播
        $data['LessonLive.zl_visible'] = 1; // 1可以观看
        $data['LessonLive.zc_date'] = array('eq', date('Y-m-d')); // 等于今天
        $data['LessonLive.zc_end_time'] = array('gt', date('H:i')); // 未开始
        $api = new liveApi();
        date_default_timezone_set("PRC");

        $db = D('EnrollLiveView');
        $msg = $db->where($data)
            ->order('LessonLive.zc_start_time asc')
            ->limit(3)
            ->select();

        foreach ($msg as $key => &$value) {

            if ($value['zc_start_time'] > date('H:i')) {
                $time = strtotime($value['zc_date'] . ' ' . $value['zc_start_time']) - time();
                $value['next_time'] = lq_format_sec($time);
            }

            // 判断是否有图片 (处理图片)
            if ($value["zc_image"]) {
                $value['zc_image'] = API_DOMAIN . $value["zc_image"];
            } else {
                $value['zc_image'] = NO_PICTURE;
            }

            $value['fid_name'] = return_cat($value['zn_fid']);// 课程
            $value['cat_name'] = return_cat($value['zn_cat_id']);// 课节

            $value['streamname'] = $value['teacher_id'] . '_' . $value['zn_object_id'] . '_' . $value['live_lesson_id']; // 拼接出streamname
            if (in_array($msg[$key]['streamname'], $live_msg)) {
                $msg[$key]['living'] = 1; // 1是正在直播
                //////在线人数

                $online_num = $api->DescribeLiveStreamOnlineUserNum($msg[$key]['streamname']);
                $temp = object2array(json_decode($online_num));
                $msg[$key]['online_num'] = intval($temp['TotalUserNumber']);
            }

        }

        return $msg;
    }
}

?>