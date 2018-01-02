<?php // HdApplication 页面操作 
namespace Admin\Controller;

use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class HdApplicationController extends PublicController
{
    public $myTable;
    protected $myForm = array(
        //标题
        'tab_title' => array(1 => '装修信息', 2 => '联系方式', 3 => '订单信息', 4 => '订单操作', 5 => '操作日志'),
        //通用信息
        '1' => array(
            array('buttonDialog', 'zn_designer_id', "邦定设计师", 1, '{"required":"0","dataLength":"","readonly":1,"disabled":0,"controller":"Designer","type":"window","checkbox":"0"}'),
            array('text', 'zn_acreage', "报价面积", 1, '{"required":"1","dataType":"integer","dataLength":"","readonly":0,"disabled":0,"maxl":4}'),
            array('select', 'zn_room', "房间个数", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":""}'),
            array('select', 'zn_kitchen', "厨房个数", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":""}'),
            array('select', 'zn_toilet', "卫生间个数", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":""}'),
            array('select', 'zn_hall', "客厅个数", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":""}'),
            array('select', 'zn_balcony', "阳台个数", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":""}'),
            array('select', 'zl_decoration_type', "装修内容-装修类型", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
            array('checkbox', 'zc_decoration_else', "装修内容-其它内容", 1, '{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"menu":1}'),
        ),
        //联系方式
        '2' => array(
            array('selectRegion', 'zn_province|zn_city|zn_district', "地区", 1, '{"label":"zc_area","required":"0","please":"请选择"}'),
            array('text', 'zc_address', "申请人详细地址", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":100}'),
            array('text', 'zc_mobile', "申请人手机号码", 1, '{"required":"1","dataType":"mobile","dataLength":"","readonly":0,"disabled":0,"maxl":11}'),
            array('text', 'zc_name', "申请人姓名", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
            array('text', 'zc_follow_contact', "装修顾问", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":10,"fast-fill":"赵国杰"}'),
            array('text', 'zc_follow_mobile', "装修顾问手机号码", 1, '{"required":"1","dataType":"mobile","dataLength":"","readonly":0,"disabled":0,"maxl":11,"fast-fill":"15920390017"}'),
        ),
        //订单信息
        '3' => array(
            array('textShow', 'zc_order_no', "订单的系统编码", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('textShow', 'status_label', "当前状态", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('textShow', 'zc_member_account', "会员帐号", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('textShow', 'zf_labour_fee', "人工费", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('textShow', 'zf_material_fee', "材料费", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('textShow', 'zf_design_fee', "设计费", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('textShow', 'zf_qc_fee', "质检费", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('textShow', 'zf_total_fee', "咨询报价", 1, '{"is_data":"0","creat_hidden":"0"}'),
        ),
        //订单操作
        '4' => array(
            array('select', 'progress', "订单进度", 0, '{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
            array('select', 'status', "进度状态", 0, '{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
            array('textarea', 'remark', "操作备注", 0, '{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":65000,"rows":4}'),
        ),
        //操作日志
        '5' => array(
            array('timeline', 'timeline', "操作日志", 1, '{}'),
        ),

    );

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->myTable = M($this->pcTable);//主表实例化
    }


    //列表页
    public function index()
    {
        //列表表单初始化****start
        $page_parameter["s"] = $this->getSafeData('s');
        $this->reSearchPara($page_parameter["s"]);//反回搜索数
        $search_content_array = array(
            'pagesize' => urldecode(I('get.pagesize', '0', 'int')),
            'fkeyword' => trim(urldecode(I('get.fkeyword', $this->keywordDefault))),
            'keymode' => urldecode(I('get.keymode', '1', 'int')),
            'open_time' => urldecode(I('get.open_time', '0', 'int')),
            'time_start' => I('get.time_start', lq_cdate(0, 0, (-604800))),
            'time_end' => I('get.time_end', lq_cdate(0, 0)),
            'type' => I('get.type', '', 'int'),
            'progress' => I('get.progress', ''),
            'status' => I('get.status', ''),
			'pay' => I('get.pay', ''),
        );
        $this->assign("search_content", $search_content_array);//搜索表单赋值
        $this->assign("zl_type_str", lqCreatOption(array(1 => '一键报价', 2 => '申请设计', 3 => '活动订单'), $search_content_array["type"], "请选择类别"));
        $this->assign("progress_str", lqCreatOption(C("HD_APPLICATION_PROGRESS"), $search_content_array["progress"], "选择进度"));//进度
        $this->assign("status_str", lqCreatOption(C("HD_APPLICATION_STATUS"), $search_content_array["status"], "选择状态"));//状态
        $this->assign("pay_str", lqCreatOption(array(0 => '未支付', 1 => '已支付'), $search_content_array["pay"], "请选择支付状态"));
		
        //sql合并
        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["fkeyword"] && $search_content_array["fkeyword"] != $this->keywordDefault) {
            if ($search_content_array["keymode"] == 1) {
                $sqlwhere_parameter .= " and zc_mobile ='" . $search_content_array["fkeyword"] . "' ";
            } else {
                $sqlwhere_parameter .= " and (zc_name like'" . $search_content_array["fkeyword"] . "%' or zc_order_no like'" . $search_content_array["fkeyword"] . "%' or zc_mobile like'" . $search_content_array["fkeyword"] . "%') ";
            }
        }
        if ($search_content_array["type"]) $sqlwhere_parameter .= " and zl_type = " . $search_content_array["type"];
        if ($search_content_array["pay"]) $sqlwhere_parameter .= " and zl_deposit_pay = " .intval($search_content_array["pay"]);
        if ($search_content_array["progress"] != '') $sqlwhere_parameter .= " and zl_progress = " . intval($search_content_array["progress"]);
        if ($search_content_array["status"] != '') $sqlwhere_parameter .= " and zl_status = " . intval($search_content_array["status"]);
        if ($search_content_array["open_time"] == 1 && $search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
        }

        //首页设置
        $page_title = array('checkbox' => 'checkbox', 'no' => L("LIST_NO"), 'zc_order_no' => '编号', 'zc_type' => '类别', 'time_diff' => '咨询时间', 'zc_member_account' => '会员', 'zc_name' => '申请人', 'zc_mobile' => '手机', 'designer' => '设计师', 'house_type' => '房屋户型', 'zf_total_fee' => '报价', 'deposit' => '订金', 'zl_status' => '状态', 'os' => L("LIST_OS"));
        $page_config = array(
            'field' => "`id`,`zc_order_no`,`zl_type`,`zn_designer_id`,`zn_hd_order_id`,`zc_member_account`,`zc_name`,`zf_total_fee`,`zn_acreage`,`zn_room`,`zn_hall`,`zn_kitchen`,`zn_toilet`,`zn_balcony`,`zc_mobile`,`zc_status_log`,`zl_progress`,`zl_status`,`zf_deposit`,`zl_deposit_pay`,`zn_mdate`,`zn_cdate`",
            'where' => $sqlwhere_parameter,
            'order' => "id DESC",
            'title' => $page_title,
            'thinkphpurl' => __CONTROLLER__ . "/",
        );
        if ($search_content_array["pagesize"]) C("PAGESIZE", $search_content_array["pagesize"]);
        //列表表单初始化****end

        $count = $this->myTable->where($sqlwhere_parameter)->count();
        $page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->C_D->lqList($page->firstRow, $page->listRows, $page_config));
        $this->assign('empty_msg', $this->tableEmptyMsg(count($page_title)));
        $this->assign("page_config", $page_config);//列表设置赋值模板
        $this->display();
    }

    // 更新/编辑
    public function edit()
    {
        if (IS_POST) {
            $this->ajaxReturn($this->C_D->lqSubmit());
        } else {
            $lcdisplay = 'edit';//引用模板

            //读取数据
            $data = $this->myTable->where("id=" . $this->lqgetid)->find();
            if (!$data) {
                $this->error(C("ALERT_ARRAY")["recordNull"]);
            }//无记录
            $this->pagePrevNext($this->myTable, "id", "zc_order_no");//上下页


            //表单数据初始化s
            $form_array = array();
            $form_array["os_record_time"] = $this->osRecordTime($data);//操作时间
            foreach ($data as $lnKey => $laValue) {
                $form_array[$lnKey] = $laValue;
            }
            $form_array["zn_province_data"] = F('province', '', COMMON_ARRAY);//省
            $form_array["zn_city_data"] = $this->returnRegionList($form_array["zn_province"]);//市
            $form_array["zn_district_data"] = $this->returnRegionList($form_array["zn_city"]);//区
            $form_array["zn_room_data"] = C("CAPITAL_NUMBER");
            $form_array["zn_hall_data"] = C("CAPITAL_NUMBER");
            $form_array["zn_kitchen_data"] = C("CAPITAL_NUMBER");
            $form_array["zn_toilet_data"] = C("CAPITAL_NUMBER");
            $form_array["zn_balcony_data"] = C("CAPITAL_NUMBER");
            $form_array["zn_designer_id_label"] = M("designer")->where("id=" . $data["zn_designer_id"])->getField("zc_nickname");
            $form_array["status_label"] = C("HD_APPLICATION_STATUS")[$data["zl_status"]];
            $form_array["zl_decoration_type_data"] = C("DECORATION_TYPE");
            $form_array["zc_decoration_else_data"] = C("DECORATION_ELSE");
            $form_array["remark"] = '';

            $progress_key = $data["zl_progress"] + 1;
            $application_progress = C("HD_APPLICATION_PROGRESS");
            $application_status = C("HD_APPLICATION_STATUS");

            $this->assign("close_submit", 0);
            if ($data["zl_status"] == 6 || $data["zl_status"] == 7 || $data["zl_status"] == 10) {//完成
                $this->assign("close_submit", 1);
                unset($this->myForm["tab_title"][4]);
                unset($this->myForm[4]);
            } elseif ($data["zl_status"] == 0) {//待处理
                $form_array["status_data"] = array(1 => '已受理');
				foreach($this->myForm[1] as $k=>$v){$this->myForm[1][$k][4] = str_replace('"required":"1"','"required":"0"',$v[4]);}
				foreach($this->myForm[2] as $k=>$v){$this->myForm[2][$k][4] = str_replace('"required":"1"','"required":"0"',$v[4]);}
            } elseif ($data["zl_status"] == 1) {//已受理
				foreach($this->myForm[1] as $k=>$v){$this->myForm[1][$k][4] = str_replace('"required":"1"','"required":"0"',$v[4]);}
				foreach($this->myForm[2] as $k=>$v){$this->myForm[2][$k][4] = str_replace('"required":"1"','"required":"0"',$v[4]);}			
                $form_array["status_data"] = array(2 => '成功预约', 3 => '到访不遇', 4 => '电话不通', 5 => '不确定时间', 6 => '客户回绝');
                $this->myForm[4]["visit_time"]=array('date', 'visit_time', "上门时间",0,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"format":"Y-m-d H:i"}');
            } elseif ($data["zl_status"] == 3 || $data["zl_status"] == 4 || $data["zl_status"] == 5) {//已受理-成功预约前
				foreach($this->myForm[1] as $k=>$v){$this->myForm[1][$k][4] = str_replace('"required":"1"','"required":"0"',$v[4]);}
				foreach($this->myForm[2] as $k=>$v){$this->myForm[2][$k][4] = str_replace('"required":"1"','"required":"0"',$v[4]);}	               
			    $progress_key = $data["zl_progress"];
                $form_array["status_data"] = array(2 => '成功预约', 3 => '到访不遇', 4 => '电话不通', 5 => '不确定时间', 6 => '客户回绝');
                $this->myForm[4]["visit_time"]=array('date', 'visit_time', "上门时间",0,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"format":"Y-m-d H:i"}');
            } elseif ($data["zl_status"] == 9) {//客户调整
                $progress_key = $data["zl_progress"];
                $form_array["status_data"] = array(
                    8 => $application_status[8],
                    9 => $application_status[9],
                    6 => $application_status[6],
                );
            } else {

                if ($data["zl_progress"] == 0) {//预约上门
                    unset($application_status[0]);
                    unset($application_status[1]);
                    unset($application_status[6]);
                    unset($application_status[7]);
                    unset($application_status[8]);
                    unset($application_status[10]);
                    $form_array["status_data"] = $application_status;
					
                } elseif ($data["zl_progress"] == 6) {//执行签约
                    $form_array["status_data"] = array(
                        7 => $application_status[7],
                        6 => $application_status[6],
                    );
                } else {//其他情况
                    $form_array["status_data"] = array(
                        8 => $application_status[8],
                        9 => $application_status[9],
                        6 => $application_status[6],
                    );
                }

            }
            $form_array["progress_data"] = array($progress_key => $application_progress[$progress_key]);

            //时间轴数据构建
            $timeline_data = array();
            if ($data["zc_status_log"]) {
                $log_arr = unserialize($data["zc_status_log"]);
                foreach ($log_arr as $k => $v) {
                    if ($k == 0) {
                        $operater = " ,  客户：" . $v["admin_account"];
                    } else {
                        $operater = " ,  操作人：" . $v["admin_account"];
                    }
                    $timeline_data[] = array(
                        'no' => ($k + 1),
                        'status' => 1,
                        'time' => lq_cdate($v["cdate"], 1) . $operater,
                        'title' => $v["progress"] . " - " . $v["status"],
                        'msg' => $v["remark"],
                    );
                }
            } else {
                $timeline_data[] = array(
                    'no' => 1,
                    'status' => 1,
                    'time' => lq_cdate($data["zn_cdate"], 1) . " ,  客户：" . $data["zc_name"],
                    'title' => "下咨询订单 - " . C("HD_APPLICATION_STATUS")[0],
                    'msg' => "请运营快点跟进...",
                );
				if ($data["zl_status"] == 10) {
					$timeline_data[] = array(
						'no' => 1,
						'status' => 1,
						'time' => lq_cdate($data["zn_mdate"], 1) . " ,  客户：" . $data["zc_name"],
						'title' => "下咨询订单 - " . C("HD_APPLICATION_STATUS")[10],
						'msg' => "用户已取消...",
					);					
				}
            }
            $form_array["timeline_data"] = $timeline_data;

            $Form = new Form($this->myForm, $form_array, $this->myTable->getCacheComment());
            $this->assign("LQFdata", $Form->createHtml());//表单数据
            //表单数据初始化s

            $this->display($lcdisplay);
        }
    }

    //更改  zlvisible
    public function opVisible(){$this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！' . $this->tellAdmin, U("/Index/desktop"));}
    //更改字段值
    public function opProperty(){$this->ajaxReturn($this->C_D->setProperty());}
	//数据导出
	public function opExportXls(){
				error_reporting(E_ALL); //开启错误
				set_time_limit(0); //脚本不超时	

				$page_parameter["s"] = $this->getSafeData('s');
				$this->reSearchPara($page_parameter["s"]);//反回搜索数
				$search_content_array = array(
					'pagesize' => urldecode(I('get.pagesize', '0', 'int')),
					'fkeyword' => trim(urldecode(I('get.fkeyword', $this->keywordDefault))),
					'keymode' => urldecode(I('get.keymode', '1', 'int')),
					'open_time' => urldecode(I('get.open_time', '0', 'int')),
					'time_start' => I('get.time_start', lq_cdate(0, 0, (-604800))),
					'time_end' => I('get.time_end', lq_cdate(0, 0)),
					'type' => I('get.type', '', 'int'),
					'progress' => I('get.progress', ''),
					'status' => I('get.status', ''),
					'pay' => I('get.pay', ''),
				);

				//sql合并
				$sqlwhere_parameter = " 1 ";//sql条件
				if ($search_content_array["fkeyword"] && $search_content_array["fkeyword"] != $this->keywordDefault) {
					if ($search_content_array["keymode"] == 1) {
						$sqlwhere_parameter .= " and zc_mobile ='" . $search_content_array["fkeyword"] . "' ";
					} else {
						$sqlwhere_parameter .= " and (zc_name like'" . $search_content_array["fkeyword"] . "%' or zc_mobile like'" . $search_content_array["fkeyword"] . "%') ";
					}
				}
				if ($search_content_array["type"]) $sqlwhere_parameter .= " and zl_type = " . $search_content_array["type"];
				if ($search_content_array["pay"]) $sqlwhere_parameter .= " and zl_deposit_pay = " .intval($search_content_array["pay"]);
				if ($search_content_array["progress"] != '') $sqlwhere_parameter .= " and zl_progress = " . intval($search_content_array["progress"]);
				if ($search_content_array["status"] != '') $sqlwhere_parameter .= " and zl_status = " . intval($search_content_array["status"]);
				if ($search_content_array["open_time"] == 1 && $search_content_array["time_start"] && $search_content_array["time_end"]) {
					$ts = strtotime($search_content_array["time_start"] . " 00:00:00");
					$te = strtotime($search_content_array["time_end"] . " 23:59:59");
					$sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
				}

				//sql合并
				$page_config = array(
					'field' => "`id`,`zc_order_no`,`zl_type`,`zn_designer_id`,`zn_hd_order_id`,`zc_member_account`,`zc_name`,`zf_total_fee`,`zn_acreage`,`zn_room`,`zn_hall`,`zn_kitchen`,`zn_toilet`,`zn_balcony`,`zc_mobile`,`zc_status_log`,`zl_progress`,`zl_status`,`zf_deposit`,`zl_deposit_pay`,`zn_mdate`,`zn_cdate`,`zc_address`",
					'where' => $sqlwhere_parameter,
					'order' => "id DESC",
				);
				$name = "家装咨询统计".date("Y-m-d");
				$list = $this->C_D->lqList(0,100000, $page_config);
				
				//导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
				import("Org.Util.PHPExcel");
				$PHPExcel=new \PHPExcel();				
				
				
				/*以下是一些设置 ，什么作者  标题啊之类的*/  
				$PHPExcel->getProperties()
						 ->setCreator("家装咨询统计")
						 ->setLastModifiedBy("家装咨询统计")
						 ->setTitle("家装咨询统计")
						 ->setSubject("家装咨询统计")
						 ->setDescription("家装咨询统计")
						 ->setKeywords("家装咨询统计")
						 ->setCategory("家装咨询统计");
				
				
				//序号	下单时间	类别	订单号	申请人	姓名	手机号码	地址	户型	订金	"支付状态 (订金)"	装修金额	装修天数	订单状态	跟进人员	订单更新时间	最后备注
				$num = 1;
				$PHPExcel->setActiveSheetIndex(0)  
							  ->setCellValue('A'.$num, '序号')     
							  ->setCellValue('B'.$num, '下单时间')  
							  ->setCellValue('C'.$num, '类别')  
							  ->setCellValue('D'.$num, '订单号')  
							  ->setCellValue('E'.$num, '申请人')  
							  ->setCellValue('F'.$num, '姓名')  
							  ->setCellValue('G'.$num, '手机号码')
							  ->setCellValue('H'.$num, '地址')
							  ->setCellValue('I'.$num, '户型')
							  ->setCellValue('J'.$num, '订金')
							  ->setCellValue('K'.$num, '订金支付状态')
							  ->setCellValue('L'.$num, '装修金额')
							  ->setCellValue('M'.$num, '装修天数')
							  ->setCellValue('N'.$num, '订单状态')
							  ->setCellValue('O'.$num, '跟进人员')
							  ->setCellValue('P'.$num, '订单更新时间')
							  ->setCellValue('Q'.$num, '最后备注')
							  
							  ;  
				
				/*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/  
       			foreach ($list as $lnKey => $laValue) {
				 $log_data = unserialize($laValue["zc_status_log"]);
				 $last_log = count($log_data) -1;
				 //订单更新时间
				 $last_time = $log_data[$last_log]["cdate"];
				 if(!$last_time)	$last_time = $laValue['zn_mdate'];

				 $num = $laValue['no']+1;
				 //Excel的第A列，uid是你查出数组的键值，下面以此类推  date('Y-m-d H:i:s',$log_data[$last_log]["cdate"])
				 $PHPExcel->setActiveSheetIndex(0)  
							  ->setCellValue('A'.$num, $laValue['no'])     
							  ->setCellValue('B'.$num, date('Y-m-d H:i:s',$laValue['zn_cdate']))  
							  ->setCellValue('C'.$num, $laValue['zl_type_label'])  
							  ->setCellValue('D'.$num, $laValue['zc_order_no'])  
							  ->setCellValue('E'.$num, $laValue['zc_member_account'])  
							  ->setCellValue('F'.$num, $laValue['zc_name'])  
							  ->setCellValue('G'.$num, $laValue['zc_mobile'])
							  ->setCellValue('H'.$num, $laValue['zc_address'])
							  ->setCellValue('I'.$num, $laValue['house_type'])
							  ->setCellValue('J'.$num, $laValue['zf_deposit'])
							  ->setCellValue('K'.$num, $laValue["zl_deposit_pay"] ? '已支付': '未支付')
							  ->setCellValue('L'.$num, $laValue['zf_total_fee'])
							  ->setCellValue('M'.$num, $laValue['time_diff'])
							  ->setCellValue('N'.$num, $laValue['zl_status_label'])
							  ->setCellValue('O'.$num, $log_data[$last_log]["admin_account"])
							  ->setCellValue('P'.$num, date('Y-m-d H:i:s',$last_time))
							  ->setCellValue('Q'.$num, lq_kill_html($log_data[$last_log]['remark']),100,'');  
				}  
	  
				$PHPExcel->getActiveSheet()->setTitle('订单明细');  
				$PHPExcel->setActiveSheetIndex(0);  
				header('Content-Type: applicationnd.ms-excel');  
				header('Content-Disposition: attachment;filename="'.$name.'.xls"');  
				header('Cache-Control: max-age=0');  
				$objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');  
				$objWriter->save('php://output');  
				exit;
	}



}

?>