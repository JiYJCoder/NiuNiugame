<?php //文章系统 Article 数据处理，数据回调
namespace Api\Model;

use LQLibs\Util\Category as Category;//树状分类
defined('in_lqweb') or exit('Access Invalid!');

class HddiaryModel extends PublicModel
{
    protected $model_diary_detail, $hd_attribute_1, $hd_attribute_2;
    protected $tableName = 'hd_diary';

    protected $rules_detail = array(
        array('zc_title', '1,100', '标题在1~100个字符之间', self::MUST_VALIDATE, 'length'),
        array('zc_content', '0,65000', '内容在1~65000个字符之间', self::MUST_VALIDATE, 'length'),

    );

    public function __construct()
    {
        parent::__construct();
        $this->hd_attribute_1 = F('hd_attribute_1', '', COMMON_ARRAY);
        $this->hd_attribute_2 = F('hd_attribute_2', '', COMMON_ARRAY);
        $this->model_diary_detail = M("hd_diary_detail");
    }

    //记录总数
    public function lqCount($sqlwhere = '1')
    {
        return $count = $this->where($sqlwhere)->count();
    }


    //列表页 //模板引用{$data.zc_title|lq_cutstr=30,0,'UTF-8','...'}
    public function lqList($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' zl_visible=1 ', 'order' => '`zd_send_time` DESC'))
    {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
            if ($laValue["image"]) {
                $list[$lnKey]['image'] = API_DOMAIN . $laValue["image"];
            } else {
                $list[$lnKey]['image'] = NO_BANNER;
            }
            if ($laValue["headimg"]) {
                $list[$lnKey]['headimg'] = API_DOMAIN . $laValue["headimg"];
            } else {
                $list[$lnKey]['headimg'] = NO_PICTURE;
            }
            $list[$lnKey]["area"] = $laValue["area"] . "平方";
            $list[$lnKey]["household"] = $this->hd_attribute_2[$laValue["household"]];

            $style = explode(",", $laValue["style"]);
            foreach ($style as $k => $v) {
                $style_tag[] = $this->hd_attribute_1[$v];
            }
            $list[$lnKey]["style"] = implode(" ", $style_tag);
            $time = date("Y-m-d H:i", $laValue["cdate"]);;
            $list[$lnKey]["cdate"] = $time;
            $list[$lnKey]["time"] = $time;
            $list[$lnKey]["share_url"] = API_DOMAIN.'/wx/views/diary/details.html?tnid='.$laValue["id"];
        }
        return $list;
    }

    //通过ID获取日志列表
    public function getInfoById($id, $mustCache = 0,$is_index=0)
    {
        if ($mustCache == 0) {
            $info = PAGE_S("page_diary_" . $id, '', $this->cache_options); //读取缓存数据
            if ($info) return $info;
        }
        $info = array();
        $sqlwhere_info_parameter = array(
            "zl_visible" => 1,
            "id" => $id
        );
        $info_config = array(
            'field' => "`id`,`zc_headimg` as headimg ,`zc_nickname` as nickname,`zc_image` as image,`zc_title` as title,`zn_area` as area,`zn_page_view` as page_view,`zn_agrees` as agrees,`zn_household` as household,`zc_style` as style,`zn_designer_id` as designer_id,`zn_works_id` as works_id",
            'where' => $sqlwhere_info_parameter
        );

        $data = $this->where($info_config["where"])->field($info_config["field"])->find();

        if (!$data) return 0;

        if ($data["image"]) {
            $data['image'] = API_DOMAIN . $data["image"];
        } else {
            $data['image'] = NO_BANNER;
        }

        if (substr($data["headimg"], 0, 4) == 'http') {
            $head_img = $data['headimg'];
        } else {
            $head_img = API_DOMAIN . $data['headimg'];
        }
        if($head_img) $data['headimg'] = $head_img;
        else $data['headimg'] = NO_PICTURE;

        $data["area"] = $data["area"] . "平方";

        $data["household"] = $this->hd_attribute_2[$data["household"]];

        $style = explode(",", $data["style"]);
        foreach ($style as $k => $v) {
            $style_tag[] = $this->hd_attribute_1[$v];
        }
        $data["style"] = implode(" ", $style_tag);
        $data["works_id"] = $data["works_id"] ? intval($data["works_id"]) : 0;
        $data["designer_id"] = $data["designer_id"] ? intval($data["designer_id"]) : 0;
        $info["detail"] = $data;

        $progress = array();

        $page_config = array(
            'field' => "`id`,`zc_title` as title,`zn_hd_diary_id` as hd_diary_id,`zc_content` as content,`zc_album` as album,`zl_order_progress` as progress,`zd_send_time` as cdate",
            'where' => " zl_visible=1 and zn_hd_diary_id=" . $id,
            'order' => 'zd_send_time ASC',
        );
        $diary = array();

        $progress_step = $this->model_diary_detail->where($page_config['where'])->field($page_config["field"])->order("zl_order_progress ASC")->group("zl_order_progress")->select();
        if ($progress_step) {
            $progress_list = array();
            foreach ($progress_step as $lnKey => $laValue) {
                $progress[$lnKey]["step"] = C("DIARY_STEP")[$laValue['progress']];
                $progress[$lnKey]["icon"] = API_DOMAIN . "/Public/Static/images/diary/" . $laValue["progress"] . ".png";

                $progress_list = $this->model_diary_detail->where("zl_visible=1 and zn_hd_diary_id=" . $laValue['hd_diary_id'] . " and zl_order_progress=" . $laValue['progress'])->field($page_config['field'])->order("zd_send_time ASC")->select();

                if ($progress_list) {
                    $lastTime = 0;
                    $diary = array();
                    foreach ($progress_list as $key => $value) {
                        if ($lastTime < $value['cdate']) $lastTime = $value['cdate'];

                        $diary[$key]["detail_id"] = $value["id"];
                        $diary[$key]["title"] = "#" . $value["title"] . "#";
                        $diary[$key]["content"] = $value["content"];
                        $diary[$key]['progress_label'] = C("DIARY_STEP")[$value['progress']];
                        $diary[$key]['progress'] = $value['progress'];
                        $diary[$key]['send_time'] = date("Y-m-d", $value['cdate']);
                        //图册
                        if ($value["album"]) {
                            $album = explode(",", $value["album"]);

                            foreach ($album as $k => $v) {
                                $album[$k] = API_DOMAIN . $v;
                            }
                            $diary[$key]["album"] = $album;
                            $diary[$key]["album_num"] = ($k + 1);
                        } else {
                            $diary[$key]["album"] = array();
                            $diary[$key]["album_num"] = 0;
                        }
                    }
                }
                $progress[$lnKey]['cdate'] = date("Y-m-d", $lastTime);
                $progress[$lnKey]['list'] = $diary;
            }
        }

        $info["diary_list"] = $progress;

        PAGE_S("page_diary_" . $id, $info, $this->cache_options); //缓存数据
        return $info;
    }

    //访问统计
    public function setViewCount($id)
    {
        $this->where('zl_visible=1 and id=' . $id)->setInc('zn_page_view', 1);
        $page_view = $this->where('zl_visible=1 and id=' . $id)->getField('zn_page_view');
        return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' => $page_view);
    }

    //点赞数量统计
    public function setAgreeCount($id)
    {
        $this->where('zl_visible=1 and id=' . $id)->setInc('zn_agrees', 1);
        return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' => $this->where('zl_visible=1 and id=' . $id)->getField('zn_agrees'));
    }

    /*
     * 获取日记详细
     */
    public function getDiaryDetailById($id)
    {
        if (!$id) return false;

        $data = $this->model_diary_detail->find($id);
        if (!$data) return false;

        $info['id'] = $id;
        $info['diary_id'] = $data['zn_hd_diary_id'];
        $info['title'] = $data['zc_title'];
        $info['content'] = $data['zc_content'];
        $info["share_url"] = API_DOMAIN.'/views/diary/details.html?tnid='.$id;

        if ($data["zc_album"]) {
            $album = explode(",", $data["zc_album"]);

            foreach ($album as $k => $v) {
                $album[$k] = $v;
            }
            $info["album"] = $album;
            $info["album_num"] = ($k + 1);
        } else {
            $info["album"] = array();
            $info["album_num"] = 0;
        }

        $info['progress_label'] = C("DIARY_STEP")[$data['zl_order_progress']];
        $info['progress'] = $data['zl_order_progress'];
        $info['send_time'] = date("Y-m-d", $data['zd_send_time']);

        return $info;
    }

    /*
     * 删除日记详细
     */
    public function delDiaryDetail($id)
    {
        if (!$id) return false;

        $data = $this->model_diary_detail->find($id);
        if (!$data) return false;

        return $this->model_diary_detail->where("id=" . $id)->setField('zl_visible',0) ? 1 : 0;
    }

    /*
     * 获取日记详细最后一步
     */
    public function getLastStep($diary_id)
    {
        $info = array("progress" => "", "detail_id" => "");
        if (!$diary_id) return $info;

        $data = $this->model_diary_detail->field("id,zl_order_progress")->where("zl_visible = 1 and zn_hd_diary_id=" . $diary_id)->order("zl_order_progress desc")->find();
        if (!$data) return $info;

        $info["progress"] = C("DIARY_STEP")[$data['zl_order_progress']];
        $info["detail_id"] = $data['id'];

        return $info;
    }

    /*
     * 检测日记 是否属于自己的
     */
    public function chk_diary($memeber_id, $diary_id)
    {
        $diary_member_id = $this->where("id=" . $diary_id)->getField("zn_member_id");
        return $diary_member_id == $memeber_id ? 1 : 0;
    }

    /*
     * 检测日记详细  是否属于自己
     */
    public function chk_diary_detail($memeber_id, $diary_detail_id)
    {
        $diary_id = $this->model_diary_detail->where("id=" . $diary_detail_id)->getField("zn_hd_diary_id");
        return $this->chk_diary($memeber_id, $diary_id);
    }


    /*
     * 添加日记详细
     */
    public function add_diary_detail($data)
    {
        $data = $this->model_diary_detail->validate($this->rules_detail)->create($data);
        if (!$data) {
            return $this->model_diary_detail->getError();
        }

        return $this->model_diary_detail->add($data);
    }

    /*
     * 编辑日记详细
     */
    public function save_diary_detail($data)
    {
        $data = $this->model_diary_detail->validate($this->rules_detail)->create($data);
        if (!$data) {
            return $this->model_diary_detail->getError();
        }

        return $this->model_diary_detail->save($data);
    }

}