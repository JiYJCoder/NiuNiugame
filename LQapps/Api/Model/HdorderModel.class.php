<?php //家装订单 Hdorder 数据处理，数据回调
namespace Api\Model;
use LQLibs\Util\Category as Category;//树状分类
use Member\Api\MemberApi as MemberApi;

defined('in_lqweb') or exit('Access Invalid!');

class HdorderModel extends PublicModel
{
    protected $model_application, $model_designer, $model_progress, $model_member;
    // 模型名称 - 数据表名（不包含表前缀）
    protected $tableName = 'hd_order';

    /*咨询订单验证 一键报价*/
    protected $application_rules1 = array(
        //array('zc_name','1,10','申请人姓名在1~10个字符间',self::MUST_VALIDATE,'length'),
        array('zc_mobile', 'isMobile', '请输入正确的手机号码', self::MUST_VALIDATE, 'function'),
        array('zn_acreage', '30,1200', '报价面积30~1200之间', self::MUST_VALIDATE, 'between'),
        array('zn_room', '1,9', '房间个数1~9之间', self::MUST_VALIDATE, 'between'),
        array('zn_room', 'lqCheckRoom', "房间个数不合法", self::MUST_VALIDATE, 'callback'),
        array('zn_kitchen', '0,9', '厨房个数0~9之间', self::MUST_VALIDATE, 'between'),
        array('zn_kitchen', 'lqCheckKitchen', "厨房个数不合法", self::MUST_VALIDATE, 'callback'),
        array('zn_toilet', '0,9', '卫生间个数0~9之间', self::MUST_VALIDATE, 'between'),
        array('zn_toilet', 'lqCheckToilet', "卫生间个数不合法", self::MUST_VALIDATE, 'callback'),
        array('zn_hall', '0,9', '客厅个数0~9之间', self::MUST_VALIDATE, 'between'),
        array('zn_hall', 'lqCheckHall', "客厅个数不合法", self::MUST_VALIDATE, 'callback'),
        array('zn_balcony', '0,9', '阳台个数0~9之间', self::MUST_VALIDATE, 'between'),
        array('zn_balcony', 'lqCheckBalcony', "阳台个数不合法", self::MUST_VALIDATE, 'callback'),
    );
    /*咨询订单验证 申请设计*/
    protected $application_rules2 = array(
        array('zc_name', '1,20', '申请人姓名在1~20个字符间', self::MUST_VALIDATE, 'length'),
        array('zc_mobile', 'isMobile', '请输入正确的手机号码', self::MUST_VALIDATE, 'function'),
    );


    public function __construct()
    {
        parent::__construct();
        $this->model_application = M("hd_application");//咨询订单
        $this->model_designer = M("designer");//设计师
        $this->model_progress = M("hd_progress");
        $this->model_member = new MemberApi;
    }

    //检测房间
    protected function lqCheckRoom($val = 0)
    {
        if ($val == 0) return false;
        $acreage = $this->getSafeData('acreage');
        if (30 <= $acreage && $acreage < 60) {
            if ($val > 2) return false;
        } else if (60 <= $acreage && $acreage < 70) {
            if ($val > 3) return false;
        } else if (70 <= $acreage && $acreage < 120) {
            if ($val > 4) return false;
        } else if (120 <= $acreage && $acreage < 130) {
            if ($val > 5) return false;
        } else if (130 <= $acreage && $acreage < 150) {
            if ($val > 6) return false;
        } else if (150 <= $acreage) {
            if ($val > 7) return false;
        }
        return true;
    }

    //厨房个数
    protected function lqCheckKitchen($val = 0)
    {
        $acreage = $this->getSafeData('acreage');
        if (30 <= $acreage && $acreage < 110) {
            if ($val != 1) return false;
        } else if (110 <= $acreage && $acreage < 150) {
            if ($val > 2) return false;
        } else if (150 <= $acreage) {
            if ($val > 3) return false;
        }
        return true;
    }

    //卫生间个数
    protected function lqCheckToilet($val = 0)
    {
        $acreage = $this->getSafeData('acreage');
        if (30 <= $acreage && $acreage < 70) {
            if ($val != 1) return false;
        } else if (70 <= $acreage && $acreage < 110) {
            if ($val > 2) return false;
        } else if (110 <= $acreage && $acreage < 130) {
            if ($val > 3) return false;
        } else if (130 <= $acreage && $acreage < 150) {
            if ($val > 4) return false;
        } else if (150 <= $acreage) {
            if ($val > 5) return false;
        }
        return true;
    }

    //客厅个数
    protected function lqCheckHall($val = 0)
    {
        $acreage = $this->getSafeData('acreage');
        if ($acreage > 50 && $val == 0) return false;

        if (30 <= $acreage && $acreage < 50) {
            if ($val > 1) return false;
        } else if (50 <= $acreage && $acreage < 90) {
            if ($val > 2) return false;
        } else if (90 <= $acreage && $acreage < 150) {
            if ($val > 3) return false;
        } else if (150 <= $acreage) {
            if ($val > 4) return false;
        }
        return true;
    }

    //阳台个数
    protected function lqCheckBalcony($val = 0)
    {
        $acreage = $this->getSafeData('acreage');
        if ($acreage <= 40 && $val > 0) return false;
        if (30 <= $acreage && $acreage < 70) {
            if ($val > 1) return false;
        } else if (70 <= $acreage && $acreage < 90) {
            if ($val > 2) return false;
        } else if (90 <= $acreage && $acreage < 120) {
            if ($val > 3) return false;
        } else if (120 <= $acreage && $acreage < 150) {
            if ($val > 4) return false;
        } else if (150 <= $acreage) {
            if ($val > 5) return false;
        }
        return true;
    }

    //咨询订单-数据保存
    public function applicationSubmit($data)
    {
        if ($data["zl_type"] == 1||$data["zl_type"] == 3) {
            $rules = $this->application_rules1;
        } else {
            $rules = $this->application_rules2;
        }
        //数据验证s
        $data = $this->validate($rules)->create($data);
        if (!$data) {
            return $this->getError();
        }


        $type = 1;// 1、精品装 2、豪华装
        $acreage = $data["zn_acreage"];//报价面积
        $room = $data["zn_room"];//房间个数
        $kitchen = $data["zn_kitchen"];//厨房个数
        $toilet = $data["zn_toilet"];//卫生间个数
        $hall = $data["zn_hall"];//客厅个数
        $balcony = $data["zn_balcony"];//阳台个数
        //常规按1个卫生间，1个厨房计算；每增加1个卫生间或1个厨房，总价增加：6.25%；
        $additional = ($kitchen + $toilet - 2);//多
        $increase_rate = 0.0625;//总价增长率

        if ($type == 1) {
            $basic_price = 480;//精品装
        } else {
            $basic_price = 900;//豪华装
        }
        $labour_per = 0.42;//人工费-比例
        $material_per = 0.52;//材料费-比例
        $design_per = 0.04;//设计费-比例
        $qc_per = 0.02;//质检费-比例

        $labour_fee = 0;//人工费
        $material_fee = 0;//材料费
        $design_fee = 0;//设计费
        $qc_fee = 0;//质检费
        $total_fee = 0;//总费用

        if ($additional) {//多出计算
            $total_fee = ($acreage * $basic_price) * (1 + $increase_rate * $additional);
            //人工费占比增加1%，材料费减少1%
            $labour_fee = $total_fee * ($labour_per + $additional * 0.01);//人工费
            $material_fee = $total_fee * ($material_per - $additional * 0.01);//材料费
            $design_fee = $total_fee * $design_per;
            $qc_fee = $total_fee * $qc_per;

        } else {//刚好计算
            $total_fee = $acreage * $basic_price;
            $labour_fee = $total_fee * $labour_per;
            $material_fee = $total_fee * $material_per;
            $design_fee = $total_fee * $design_per;
            $qc_fee = $total_fee * $qc_per;
        }
        $data["zf_total_fee"] = $total_fee;
        $data["zf_labour_fee"] = $labour_fee;
        $data["zf_material_fee"] = $material_fee;
        $data["zf_design_fee"] = $design_fee;
        $data["zf_qc_fee"] = $qc_fee;

        $saveStatus = $this->model_application->add($data);
        return $this->getLastInsID();//返回刚插入的记录ID

    }

    //记录总数
    public function lqCount($sqlwhere = '1')
    {
        return $count = $this->where($sqlwhere)->count();
    }


    //列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' zl_visible=1 ', 'order' => '`zd_send_time` DESC'))
    {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();

        foreach ($list as $lnKey => $laValue) {
            if ($laValue["effect_image"]) {
                $list[$lnKey]['effect_image'] = API_DOMAIN . $laValue["effect_image"];
            } else {
                $list[$lnKey]['effect_image'] = NO_PICTURE;
            }
            $list[$lnKey]["order_date"] = date("Y-m-d H:i", $laValue["order_date"]);
            $order_no_arr[] = $laValue["order_no"];
            $list[$lnKey]["short_address"] = lq_kill_html($list[$lnKey]["address"], 16);
            $list[$lnKey]["is_order"] = 1;
            $list[$lnKey]["acreage"] = $list[$lnKey]["acreage"] . "㎡";
            $capn = C("CAPITAL_NUMBER");
            $list[$lnKey]["house_type"] = $capn[$laValue["hall"]] . "居";//.$capn[$laValue["hall"]]."厅";

            $list[$lnKey]['decoration_else'] = str_replace(",", " ", $laValue["decoration_else"]);


            if ($laValue["status"] == 3 || $laValue["status"] == 4) {
                $status = '已取消';
            } elseif ($laValue["status"] == 2) {
                $status = get_order_status($laValue['progress']);
            } else {
                $status = C("HD_ORDER_STATUS")[$laValue["status"]];
            }

            $list[$lnKey]['status'] = $status;
        }
        $list["odrer_no_arr"] = $order_no_arr;

        return $list;
    }

    //咨询订单列表
    public function lqListApplication($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' zl_visible=1 ', 'order' => '`zd_send_time` DESC'))
    {
        $list = $this->model_application->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();

        foreach ($list as $lnKey => $laValue) {
            if ($laValue["effect_image"]) {
                $list[$lnKey]['effect_image'] = API_DOMAIN . $laValue["effect_image"];
            } else {
                $list[$lnKey]['effect_image'] = NO_BANNER;
            }

            $designer_name = $this->model_designer->where("id=" . $laValue["designer_id"])->getField("zc_nickname");
            $list[$lnKey]["designer_name"] = !$designer_name ? "未指定设计师" : $designer_name;
            $list[$lnKey]["order_date"] = date("Y-m-d H:i", $laValue["order_date"]);
            $list[$lnKey]["acreage"] = $list[$lnKey]["acreage"] . "㎡";
            $list[$lnKey]["is_order"] = 0;
            $capn = C("CAPITAL_NUMBER");
            $list[$lnKey]["house_type"] = $capn[$laValue["hall"]] . "居";//.$capn[$laValue["hall"]]."厅";

            $list[$lnKey]['decoration_type'] = C("DECORATION_TYPE")[$laValue["decoration_type"]];
            $decoration_else = explode("|", $laValue["decoration_else"]);

            foreach (C("DECORATION_ELSE") as $k => $v) {
                if ($decoration_else[$k]) $de[] = $v;
            }
            $list[$lnKey]['decoration_else'] = implode(" ", $de);
            $list[$lnKey]['status'] = C("HD_APPLICATION_STATUS")[$laValue["status"]];
        }

        return $list;
    }

    //通过ID获取家装订单数据 $id 家装订单ID ,$mustCache后台控制必须缓存
    public function getOrderById($id, $mustCache = 0)
    {
        if ($mustCache == 0) {
            $info = PAGE_S("page_hd_order_" . $id, '', $this->cache_options); //读取缓存数据
            if ($info) return $info;
        }
        $data = $this->model_application->where("id=" . $id)->find();
        if (!$data) return 0;

        $info = array();
        $info['id'] = $data["id"];


        PAGE_S("page_hd_order_" . $id, $info, $this->cache_options); //缓存数据
        return $info;
    }


    //通过ID获取咨询订单数据 $id 咨询订单ID ,$mustCache后台控制必须缓存
    public function getApplicationById($id, $mustCache = 0)
    {
        if ($mustCache == 0) {
            $info = PAGE_S("page_article_" . $id, '', $this->cache_options); //读取缓存数据
            if ($info) return $info;
        }
        $data = $this->model_application->where("id=" . $id)->find();

        if (!$data) return 0;

        $info = array();
        $info['id'] = $data["id"];


        PAGE_S("page_application_" . $id, $info, $this->cache_options); //缓存数据
        return $info;
    }

    public function getApplicationDetailById($id)
    {

        $field = array("id", "zn_cdate");
        $data = $this->model_application->field($field)->where("id=" . $id)->find();
        if (!$data) return 0;

        $info = $detail = $progress = array();

        $progress_detail = array();
        $app_date = $data["zn_cdate"];

        /////21步合成12步
        $progress_step_12 = change_step($progress);

        $progress_step = C(PROJECT_STEP_TITLE);
        $progress_step_text = C(PROJECT_STEP);

        $info["is_service"] = '0';
        $info["detail"] = '';
        $step_key = 1;
        foreach ($progress_step as $lnKey => $laValue) {
            $step_now = $progress_step_12[$step_key - 1]['step_now'] ? 1 : 2;

            if ($lnKey == 1) {
                $progress_detail[] = array(
                    'no' => 1,
                    'step_now' => 2,
                    'icon' => API_DOMAIN . '/Public/Static/images/project-step/1.png',
                    'title' => $progress_step[1],
                    'step_title' => $progress_step_text[1],
                    'time' => date("Y.m.d", $app_date)
                );
            } else {
                $progress_detail[] = array(
                    'no' => $step_key,
                    'step_now' => 0,
                    'icon' => API_DOMAIN . '/Public/Static/images/project-step/' . $step_key . '.png',
                    'title' => $progress_step[$step_key],
                    'step_title' => $progress_step_text[$step_key],
                    'time' => ''
                );
            }
            $step_key++;
        }

        $info["progress_detail"] = $progress_detail;

        return $info;
    }

    //通过ID获取正式订单详情和进度
    public function getOrderDetailById($id, $mustCache = 0)
    {

        $field = array("id", "zc_order_no" => "order_no", "zn_designer_id" => "designer_id", "zc_dc_name" => "decoration_consultant", "zc_pm_name" => "project_manager", "zc_qc_name" => "qc", "zl_progress","zc_dc_mobile"=>"dc_moblie","zc_dc_mobile"=>"dc_moblie","zc_dc_mobile"=>"dc_moblie");
        $data = $this->field($field)->where("id=" . $id)->find();
        if (!$data) return 0;

        $info = $detail = $progress = array();
        $designer = $this->model_designer->where("id=" . $data["designer_id"])->field("zn_member_id,zc_nickname")->find();

        /*$headimg = $this->model_member->apiGetField("id=".$designer["zn_member_id"],'zc_headimg,zc_headimg_thumb');

        if($headimg){
            if(substr($headimg['zc_headimg'],0,4)=='http'){
                $data['designer_headimg'] = $headimg['zc_headimg'];
            }else{
                $img = $headimg['zc_headimg_thumb'] ? $headimg['zc_headimg_thumb'] : $headimg['zc_headimg'];

                $data['designer_headimg'] = API_DOMAIN.$img;
            }
        }else{
            $data['designer_headimg'] = NO_PICTURE;
        }*/
        $info["is_service"] = '1';
        $data["designer_name"] = !$designer["zc_nickname"] ? "未指定设计师" : $designer["zc_nickname"];
        $data['designer_headimg'] = API_DOMAIN . "/Public/Static/images/service-team/d.png";

        $data["qc"] = $data['qc'];
        $data["qc_img"] = API_DOMAIN . "/Public/Static/images/service-team/qc.png";

        $data["project_manager"] = $data["project_manager"];
        $data["project_manager_img"] = API_DOMAIN . "/Public/Static/images/service-team/pm.png";;
        //$decoration_consultant = explode(",", $data["decoration_consultant"]);
        $data["decoration_consultant"] = $data["decoration_consultant"];
        $data["decoration_consultant_tel"] = $data["dc_moblie"];
        $data["decoration_consultant_img"] = API_DOMAIN . "/Public/Static/images/service-team/dc.png";;

        $ord_no = $data["order_no"];
        unset($data["order_no"]);
        $info["detail"] = $data;
        ////工程进度
        $progress_detail = array();
        $app_date = $this->model_application->where("zc_order_no = '" . $ord_no . "'")->getField("zn_cdate");

        $field = array("zl_order_progress" => "order_progress", "zl_status" => "status", "zn_cdate" => "cdate");
        $progress = $this->model_progress->field($field)->where(array("zn_hd_order_id" => $data["id"]))->order("zl_order_progress ASC")->select();
        $progress_now_step = $data['zl_progress'];

        /////21步合成12步
        $progress_step_12 = change_step($progress, $progress_now_step);

        $progress_step = C(PROJECT_STEP_TITLE);
        $progress_step_text = C(PROJECT_STEP);

        $step_key = 1;
        $is_complete = 0;
        foreach ($progress_step as $lnKey => $laValue) {
            $step_now = $progress_step_12[$step_key - 1]['step_now'] ? 1 : 0;

            if ($step_now == 1) $last_step = $step_key;

            if ($lnKey == 1) {
                $progress_detail[] = array(
                    'no' => 1,
                    'step_now' => 1,
                    'icon' => API_DOMAIN . '/Public/Static/images/project-step/1.png',
                    'title' => $progress_step[1],
                    'step_title' => $progress_step_text[1],
                    'time' => date("Y.m.d", $app_date)
                );
            } else {
                $progress_detail[] = array(
                    'no' => $step_key,
                    'step_now' => $step_now,
                    'icon' => API_DOMAIN . '/Public/Static/images/project-step/' . $step_key . '.png',
                    'title' => $progress_step[$step_key],
                    'step_title' => $progress_step_text[$step_key],
                    'time' => $progress_step_12[$step_key - 1]['time']
                );
            }
            $step_key++;
        }
        if ($last_step == 13) $is_complete = 1;
        $progress_detail[$last_step - 1]['step_now'] = 2;

        $info["progress_detail"] = $progress_detail;
        $info["is_complete"] = $is_complete;
        PAGE_S("order_detail_" . $id, $info, $this->cache_options); //缓存数据
        return $info;
    }

    /////用户取消咨询单
    public function cansel_application($id)
    {
        return $this->model_application->where("id=" . $id)->setField("zl_status", 10);
    }

    /////支付成功更新支付状态
    public function set_pay_status($order_no)
    {
        return $this->model_application->where("zc_order_no=" . $order_no)->setField("zl_deposit_pay", 1) ? 1 : 0;
    }

    /////检测当前获取优惠的人数  优惠结束 1 未结束 0
    public function is_discount_finish()
    {
        $total = $this->model_application->where("zl_deposit_pay = 1 and zl_type = 3")->count();

        return $total >= 30 ? 1 : 0;
    }
}
?>
