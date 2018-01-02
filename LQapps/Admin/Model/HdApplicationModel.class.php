<?php // HdApplication 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class HdApplicationModel extends PublicModel
{
    /* 用户模型自动验证 */
    protected $validate1 = array(
        array('zc_name', '1,20', '申请人姓名在1~20个字符间', self::MUST_VALIDATE, 'length'),
        array('zc_mobile', 'isMobile', '请输入正确的手机号码', self::MUST_VALIDATE, 'function'),
    );
    protected $validate2 = array(
        array('zc_name', '1,20', '申请人姓名在1~20个字符间', self::MUST_VALIDATE, 'length'),
        array('zc_mobile', 'isMobile', '请输入正确的手机号码', self::MUST_VALIDATE, 'function'),
        array('zc_follow_contact', '1,10', '装修顾问姓名在1~10个字符间', self::MUST_VALIDATE, 'length'),
        array('zc_follow_mobile', 'isMobile', '请输入正确的装修顾问手机号码', self::MUST_VALIDATE, 'function'),
        array('zc_address','1,100','申请人详细地址1~100个字符间',self::MUST_VALIDATE,'length'),
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
        array('status', 'lqCheckStatus', "订单操作数据不能为空(备注或者时间)", self::MUST_VALIDATE, 'callback'),
        array('status', 'lqCheckComplete', "订单联系信息不完善，不能保存操作", self::MUST_VALIDATE, 'callback'),
        array('status', 'lqCheckModify', "订单已处理完成，不能修改了", self::MUST_VALIDATE, 'callback'),
    );	
	protected $check_validate_array=array(0,1,3,4,5);	
	

    /* 用户模型自动完成 */
    protected $_auto = array(
        array('zn_designer_id', 'lqNumber', self::MODEL_BOTH, 'function'),
        array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
        array('zn_mdate', NOW_TIME, self::MODEL_BOTH),
    );

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->pc_os_label = "zc_name";//数据表显示标题字段
        $this->pc_index_list = "HdApplication/index";//列表首页
    }

    //跟进人与备注不能为空
    protected function lqCheckStatus()
    {
        $status = I("post.status", '0', 'int');
        $progress = I("post.progress", '0', 'int');
        $remark = I("post.remark", '');
		$visit_time = I("post.visit_time", '');
        if ($status && $progress) {
			if($status==2 && $progress==2){
				if(strtotime($visit_time) == false) return false;
			}else{
            	if (!$remark) return false;
			}
			
        }
        return true;
    }

    //订单联系信息不完善，不能保存操作
    protected function lqCheckComplete()
    {
        $data = I("post.LQF", '');
        $status = I("post.status", '0', 'int');
        if ($status >= 1) {
            if (intval($data["zn_designer_id"]) == 0 || intval($data["zn_province"]) == 0 || intval($data["zn_city"]) == 0 || intval($data["zn_district"]) == 0 || $data["zn_province"] == '') return false;
        }
        return true;
    }

    //订单已处理完成，不能修改了
    protected function lqCheckModify()
    {
        $data = I("post.LQF", '');
        $status = intval($this->where("id=" . (int)$data["id"])->getField("zl_status"));
        if ($status == 6 || $status == 7 || $status == 10) {
            return false;
        }
        return true;
    }

    //检测房间
    protected function lqCheckRoom($val = 0)
    {
        if ($val == 0) return false;
        $acreage = $this->getSafeData('LQF', 'p')["zn_acreage"];
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
        $acreage = $this->getSafeData('LQF', 'p')["zn_acreage"];
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
        $acreage = $this->getSafeData('LQF', 'p')["zn_acreage"];
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
        $acreage = $this->getSafeData('LQF', 'p')["zn_acreage"];
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
        $acreage = $this->getSafeData('LQF', 'p')["zn_acreage"];
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


    //列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' 1 ', 'order' => '`id` DESC'))
    {
        $model_designer = M("designer");
        $capn = C("CAPITAL_NUMBER");
        $application_progress = C("HD_APPLICATION_PROGRESS");
        $application_status = C("HD_APPLICATION_STATUS");
        $application_type = C("HD_APPLICATION_TYPE");
		if($firstRow==0&&$listRows==0){
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->select();
		}else{
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
		}
        foreach ($list as $lnKey => $laValue) {
            $member_account = !$laValue["zc_member_account"] ? '未邦定' : $laValue["zc_member_account"];
            $list[$lnKey]["deposit"] = $laValue["zl_deposit_pay"] ? $laValue["zf_deposit"].'已支付': $laValue["zf_deposit"].'未支付';
            $list[$lnKey]['zc_member_account'] = $member_account;
            $list[$lnKey]['zl_type_label'] = $application_type[$laValue["zl_type"]];
            if ($laValue["zl_status"] == 5 || $laValue["zl_status"] == 6) {
                $list[$lnKey]['time_diff'] = lq_time_diff($laValue["zn_cdate"], $laValue["zn_mdate"], '');
            } else {
                $list[$lnKey]['time_diff'] = lq_time_diff($laValue["zn_cdate"], NOW_TIME, '');
            }
            if ($laValue["zc_status_log"]) {
                $log_arr = unserialize($laValue["zc_status_log"]);
                foreach ($log_arr as $k => $v) {
                    $log .= "<br>第" . ($k) . "步：" . $v["progress"] . ",状态、" . $v["status"] . ",备注、" . $v["remark"] . ",跟进、" . $v["admin_account"] . ",操作时间、" . lq_cdate($v["cdate"], 1);
                }
            }
            $log_data = unserialize($laValue["zc_status_log"]);
            if ($laValue["zl_progress"] == 0) {
                $list[$lnKey]['zl_status_label'] = $application_status[$laValue["zl_status"]];
            } else {
                $list[$lnKey]['zl_status_label'] = $application_progress[$laValue["zl_progress"]] . " - " . $application_status[$laValue["zl_status"]];
            }
            $list[$lnKey]['house_type'] = "面积:" . $laValue["zn_acreage"] . "㎡/" . $capn[$laValue["zn_room"]] . "房/" . $capn[$laValue["zn_hall"]] . "客/" . $capn[$laValue["zn_kitchen"]] . "厨/" . $capn[$laValue["zn_toilet"]] . "卫/" . $capn[$laValue["zn_balcony"]] . "阳台";
            $list[$lnKey]['zc_msg'] = "<p>会员:" . $member_account . "</p><p>申请人:" . $laValue["zc_name"] . "</p><p>手机号码:" . $laValue["zc_mobile"] . "</p><p>申请设计师:" . $nickname . "</p><p>日志:" . $log . "</p><p>状态:" . $list[$lnKey]['zl_status_label'] . "</p><p>更新时间:" . lq_cdate_format($laValue["zn_mdate"], "Y-m-d H:i:s") . "</p><p>创建时间:" . lq_cdate_format($laValue["zn_cdate"], "Y-m-d H:i:s") . "<p>进跟时间:" . lq_time_diff($laValue["zn_cdate"], $laValue["zn_mdate"], '', 'dhms') . "</p>";
            $list[$lnKey]['no'] = $firstRow + $lnKey + 1;
        }
        return $list;
    }

    //数据保存
    public function lqSubmit()
    {
		$check_validate=$this->where("id=".intval($_POST["LQF"]["id"]))->getField("zl_status");
        $status = I("post.status", '0', 'int');//进度状态

		//表单数据构建
		if(in_array($check_validate,$this->check_validate_array) ){
			if($status==2){
				$data = $this->lqPostData($this->validate2);
			}else{			
				$data = $this->lqPostData($this->validate1);
			}
		}else{
			$data = $this->lqPostData($this->validate2);
		}
		if(!is_array($data)) return array('status' => 0, 'msg' => C('ALERT_ARRAY')["saveError"].":".$data);
			
			$saveStatus=$this->save($data);
			$lnid=$data["id"];
			if($saveStatus){
					$this->lqAdminLog($lnid);//写入日志
					return array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"], 'url' =>  $back_url!=''?$back_url:U(CONTROLLER_NAME.'/edit/tnid/'.$data["id"]));
			}else{
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["error"]);
			}		

    }

    // 更新成功后的回调方法
    protected function _after_update($data, $options)
    {
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

        $sql = $log = '';
        $log_data = array();
        $application_progress = C("HD_APPLICATION_PROGRESS");
        $application_status = C("HD_APPLICATION_STATUS");
        $progress = I("post.progress", '0', 'int');//订单进度
        $status = I("post.status", '0', 'int');//进度状态
        $visit_time = I("post.visit_time",'');//上门时间
        $remark = I("post.remark", '');//备注
        if($status==2)  $remark = $remark." <br>预约上门时间:".$visit_time."并通知装修顾问 ".$_POST["LQF"]["zc_follow_contact"];

        if ($progress && $status) {
            $hd_application = M("hd_application")->where("id=" . $data["id"])->find();//订单信息
            $status_log = $hd_application["zc_status_log"];//订单备注
            if ($status_log) {
                $log_data = unserialize($status_log);
                $log_data[] = array('progress' => $application_progress[$progress], 'status' => $application_status[$status], 'remark' => $remark, 'admin_id' => session('admin_auth')["id"], 'admin_account' => session('admin_auth')["zc_account"], 'cdate' => NOW_TIME);
            } else {
                $log_data[0] = array('progress' => '下咨询订单', 'status' => $application_status[0], 'remark' => "下单成功", 'admin_id' => session('admin_auth')["id"], 'admin_account' => $hd_application["zc_member_account"], 'cdate' => $hd_application["zn_cdate"]);
                $log_data[1] = array('progress' => $application_progress[$progress], 'status' => $application_status[$status], 'remark' => $remark, 'admin_id' => session('admin_auth')["id"], 'admin_account' => session('admin_auth')["zc_account"], 'cdate' => NOW_TIME);
            }
            if ($status == 1) $progress = 1;
            $sql = ",zl_progress='$progress',zl_status='$status',zc_status_log='" . serialize($log_data) . "'";
		
            /*预约上门（通知装修顾问/运营/业主）
            您好，客户预约设计师于{1}上门测量，请尽快安排。咨询编号：{2}，点击查看详情：{3}，申请人：{4}，手机号：{5}，客户地址：{6}
            */
            if($progress==2&&$status==2&&$visit_time){
                lqSendSms($hd_application["zc_follow_mobile"].",13560444215,13249131367",array($visit_time,$hd_application["zc_order_no"],'',$hd_application["zc_name"],$hd_application["zc_mobile"],$hd_application["zc_area"].$hd_application["zc_address"]),166351);
                lqSendSms($hd_application["zc_mobile"],array($visit_time,$hd_application["zc_follow_contact"],$hd_application["zc_follow_mobile"]),166349);
            }
			


            if ($status == 7) {
                //插入订单
                $application_progress = array();
                foreach ($log_data as $k => $v) {
                    if ($v["progress"] == '上门测量') {
                        $application_progress[1] = array("remark" => $v["remark"], "admin_id" => $v["admin_id"], "admin_account" => $v["admin_account"], "cdate" => $v["cdate"]);
                    } elseif ($v["progress"] == '出平面图') {
                        $application_progress[2] = array("remark" => $v["remark"], "admin_id" => $v["admin_id"], "admin_account" => $v["admin_account"], "cdate" => $v["cdate"]);
                    } elseif ($v["progress"] == '选材') {
                        $application_progress[3] = array("remark" => $v["remark"], "admin_id" => $v["admin_id"], "admin_account" => $v["admin_account"], "cdate" => $v["cdate"]);
                    } elseif ($v["progress"] == '交底报价') {
                        $application_progress[4] = array("remark" => $v["remark"], "admin_id" => $v["admin_id"], "admin_account" => $v["admin_account"], "cdate" => $v["cdate"]);
                    } elseif ($v["progress"] == '执行签约') {
                        $application_progress[5] = array("remark" => $v["remark"], "admin_id" => $v["admin_id"], "admin_account" => $v["admin_account"], "cdate" => $v["cdate"]);
                    }
                }

                $decoration_else_str = '';
                if ($hd_application["zc_decoration_else"]) {
                    $decoration_else_arr = explode("|", $hd_application["zc_decoration_else"]);
                    foreach (C("DECORATION_ELSE") as $k => $v) {
                        if ($decoration_else_arr[$k]) {
                            $index++;
                            if ($index == 1) {
                                $decoration_else_str = $v;
                            } else {
                                $decoration_else_str .= "," . $v;
                            }
                        }
                    }
                }
                $order = array();
                $order["zn_hd_application_id"] = $hd_application["id"];
                $order["zc_order_no"] = $hd_application["zc_order_no"];
                $order["zn_designer_id"] = $hd_application["zn_designer_id"];
                $order["zn_member_id"] = $hd_application["zn_member_id"];
                $order["zc_member_account"] = $hd_application["zc_member_account"];
                $order["zc_mobile"] = $hd_application["zc_mobile"];
                $order["zc_name"] = $hd_application["zc_name"];
                $order["zn_province"] = $hd_application["zn_province"];
                $order["zn_city"] = $hd_application["zn_city"];
                $order["zn_district"] = $hd_application["zn_district"];
                $order["zc_area"] = $hd_application["zc_area"];
                $order["zc_address"] = $order["zc_contact_address"] = $hd_application["zc_address"];
                $order["zc_area"] = $hd_application["zc_area"];
                $order["zc_decoration_type"] = C("DECORATION_TYPE")[$hd_application["zl_decoration_type"]];
                $order["zc_decoration_else"] = $decoration_else_str;
                $order["zn_acreage"] = $acreage;
                $order["zn_room"] = $room;
                $order["zn_hall"] = $hall;
                $order["zn_kitchen"] = $kitchen;
                $order["zn_toilet"] = $toilet;
                $order["zn_balcony"] = $balcony;
                $order["zf_labour_fee"] = $labour_fee;
                $order["zf_material_fee"] = $material_fee;
                $order["zf_design_fee"] = $design_fee;
                $order["zf_qc_fee"] = $qc_fee;
                $order["zf_total_fee"] = $total_fee;
                $order["zl_status"] = 2;
                $order["zl_progress"] = 7;
                $order["zc_decoration_content"] = $order["zc_remarks"] = '';
                $order["zl_status"] = $order["zl_payment_status"] = $order["zl_transfer_status"] = 0;
                //装修顾问
                $order["zc_dc_name"] = $hd_application["zc_follow_contact"];
                $order["zc_dc_mobile"] = $hd_application["zc_follow_mobile"];
                //项目经理
                $order["zc_pm_name"] = $order["zc_pm_mobile"] = '';
                //监理
                $order["zc_cs_name"] = $order["zc_cs_mobile"] = '';
                //客服
                $order["zc_qc_name"] = $order["zc_qc_mobile"] = '';
                $order["zn_cdate"] = $order["zn_mdate"] = NOW_TIME;
                M("hd_order")->add($order);
                $order_id = $this->getLastInsID();//返回刚插入的记录ID
                if ($order_id) {
                    $sql .= ",zn_hd_order_id='$order_id'";//将订单ID邦定到咨询订单里
                    //初始化进度数据
                    $model_progress = M("hd_progress");
                    $progress = array();
                    $progress["zn_hd_order_id"] = $order_id;
                    foreach (C("PROJECT_PROGRESS") as $k => $v) {
                        $progress["zl_order_progress"] = $k;
                        if ($k <= 5) {
                            $progress["zl_status"] = 1;
                            $progress["zf_payment"] = 0;
                            $progress["zc_follow_contact"] = $data["zc_follow_contact"];
                            $progress["zc_follow_mobile"] = $data["zc_follow_mobile"];
                            $progress["zc_remarks"] = $application_progress[$k]["remark"];
                            $progress["zn_admin_id"] = $application_progress[$k]["admin_id"];
                            $progress["zn_admin_account"] = $application_progress[$k]["admin_account"];
                            $progress["zn_cdate"] = $application_progress[$k]["cdate"];
                        } else {
                            $progress["zl_status"] = $progress["zf_payment"] = 0;
                            $progress["zc_remarks"] = '';
                            $progress["zc_follow_contact"] = '';
                            $progress["zc_follow_mobile"] = '';
                            $progress["zn_admin_id"] = 0;
                            $progress["zn_admin_account"] = '';
                            $progress["zn_cdate"] = 0;
                        }
                        $model_progress->add($progress);
                    }
                }
            }

			if(in_array($hd_application["zl_status"],$this->check_validate_array) ){
			$update = M()->execute("update __PREFIX__hd_application set zl_progress='$progress',zl_status='$status',zc_status_log='" . serialize($log_data) . "' where id=" . $data["id"]);
			}else{
			$update = M()->execute("update __PREFIX__hd_application set zf_labour_fee='$labour_fee',zf_material_fee='$material_fee',zf_design_fee='$design_fee',zf_qc_fee='$qc_fee',zf_total_fee='$total_fee'" . $sql . " where id=" . $data["id"]);
			}
					
			
        }
		

    }

    //单记录删除
    public function lqDelete($isTree = 0)
    {
        $data["id"] = I("get.tnid", '0', 'int');
        if ($this->where($data)->delete()) {
            return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list . "?clearcache=1"));
        } else {
            return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
        }
    }

    //多记录删除
    public function lqDeleteCheckbox()
    {
        $data["id"] = array('in', I("get.tcid", '', 'lqSafeExplode'));
        if ($this->where($data)->delete()) {
            return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list));
        } else {
            return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
        }
    }


}

?>
