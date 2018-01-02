<?php // LoanApply 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class LoanApplyController extends PublicController{
	public $myTable;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'订单信息',2=>'操作日志'),
		//通用信息
		'1'=>array(
		array('textShow', 'zc_order_no', "订单的系统编码",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zc_bank_name', "银行名称",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zc_member_account', "会员",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'status_label', "当前状态",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('text', 'zc_name', "申请人姓名",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
		array('text', 'zc_mobile', "申请人手机号码",1,'{"required":"1","dataType":"mobile","dataLength":"","readonly":0,"disabled":0,"maxl":11}'),
		array('text', 'zc_area', "装修贷申请人所在地",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
		),
		//操作日志
		'2'=>array(
		array('timeline', 'timeline', "操作日志",1,'{}'),
		),		
		
	);	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->myTable = M($this->pcTable);//主表实例化
	}

    
	//列表页
    public function index() {
		//列表表单初始化****start
		$page_parameter["s"]=$this->getSafeData('s');
		$this->reSearchPara($page_parameter["s"]);//反回搜索数
		$search_content_array=array(
			'pagesize'=>urldecode(I('get.pagesize','0','int')),
			'fkeyword'=>trim(urldecode(I('get.fkeyword',$this->keywordDefault))),
			'keymode'=>urldecode(I('get.keymode','1','int')),
			'open_time'=>urldecode(I('get.open_time','0','int')),
			'time_start'=>I('get.time_start',lq_cdate(0,0,(-604800))),
			'time_end'=>I('get.time_end',lq_cdate(0,0)),
			'status'=>I('get.status',''),			
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		
		//sql合并
		$sqlwhere_parameter=" 1 ";//sql条件
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_mobile ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and (zc_name like'".$search_content_array["fkeyword"]."%' or zc_order_no like'".$search_content_array["fkeyword"]."%' or zc_mobile like'".$search_content_array["fkeyword"]."%') ";
			}	
		}
		if($search_content_array["status"]!=''){
			if($search_content_array["status"]==999){
				$sqlwhere_parameter.=" and zl_status in(6,7,8) ";
			}else{
				$sqlwhere_parameter.=" and zl_status = ".intval($search_content_array["status"]);
			}
		}		
		if($search_content_array["open_time"]==1&&$search_content_array["time_start"]&&$search_content_array["time_end"]){
				$ts=strtotime($search_content_array["time_start"]." 00:00:00");
				$te=strtotime($search_content_array["time_end"]." 23:59:59");
				$sqlwhere_parameter.=" and zn_cdate >=".$ts." and zn_cdate<=".$te;	
	   }

		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_order_no'=>'编号','time_diff'=>'申请时间','zc_member_account'=>'会员','zc_name'=>'申请人','zc_mobile'=>'手机','zl_status'=>'状态','os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zc_order_no`,`zn_bank_id`,`zc_bank_name`,`zn_member_id`,`zc_member_account`,`zc_name`,`zc_mobile`,`zl_status`,`zn_mdate`,`zn_cdate`",
				'where'=>$sqlwhere_parameter,
				'order'=>"id DESC",
				'title'=>$page_title,
				'thinkphpurl'=>__CONTROLLER__."/",
		);		
		if($search_content_array["pagesize"]) C("PAGESIZE",$search_content_array["pagesize"]);
		//列表表单初始化****end
		
        $count = $this->myTable->where($sqlwhere_parameter)->count();
		$page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->C_D->lqList($page->firstRow, $page->listRows,$page_config));
		$this->assign('empty_msg',$this->tableEmptyMsg(count($page_title)));
		$this->assign("page_config",$page_config);//列表设置赋值模板
        $this->display();
    }

	// 更新/编辑
    public function edit() {
        if (IS_POST) {
            $this->ajaxReturn($this->C_D->lqSubmit());
        } else {
			$lcdisplay='edit';//引用模板
			
			//读取数据
			$data = $this->myTable->where("id=".$this->lqgetid)->find();
			if(!$data) {$this->error(C("ALERT_ARRAY")["recordNull"]);}//无记录
			$this->pagePrevNext($this->myTable,"id","zc_order_no");//上下页
			if($data["zl_status"]==3||$data["zl_status"]==7||$data["zl_status"]==8){
				$this->assign("close_submit",1);				
			}else{
				$this->assign("close_submit",0);				
			}	
			
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["status_label"]=C("LOAN_STATUS")[$data["zl_status"]];
			
			//时间轴数据构建
			$order_progress=M("loan_apply_progress")->field("zl_role,`zl_progress` as progress,`zl_status` as status,zc_remarks,zn_cdate,zc_operate_account")->where("zn_loan_apply_id=".(int)$data["id"])->order("zl_progress ASC")->select();
			$timeline_data=array();
			foreach ($order_progress as $k => $v) {
				if($v["status"]>0){
					$progress_status=1;
				}else{
					$progress_status=0;
				}
				if($v["status"]==0){
					$role = '';
					$status='';
				}else{
					$status=" - ".C("LOAN_STATUS")[$v["status"]];
					if($v["zl_role"]==1){
						$role = '业主';
					}else if($v["zl_role"]==6){
						$role = '银行';
					}
				}
				$timeline_data[]=array(
					'no'=>($k+1),
					'status'=>$progress_status,
					'time'=>$v["zn_cdate"]==0?'':lq_cdate($v["zn_cdate"],1)." ,  操作人：".$v["zc_operate_account"]." ".$role ,
					'title'=>C("LOAN_PROGRESS")[$v["progress"]].$status,
					'msg'=>$v["status"]==0?'':'备注：'.$v["zc_remarks"],
				);
			}
			$form_array["timeline_data"]=$timeline_data;

			
			
					
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

            $this->display($lcdisplay);
        }
    }

	//更改  zlvisible 
    public function opVisible() {
        $this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->tellAdmin, U("/Index/desktop"));
    }	
	
	//更改字段值 
    public function opProperty() {
        $this->ajaxReturn($this->C_D->setProperty());
    }

    //数据导出
    public function opExportXls(){
        error_reporting(E_ALL); //开启错误
        set_time_limit(0); //脚本不超时

        $page_parameter["s"]=$this->getSafeData('s');
        $this->reSearchPara($page_parameter["s"]);//反回搜索数
        $search_content_array=array(
            'pagesize'=>urldecode(I('get.pagesize','0','int')),
            'fkeyword'=>trim(urldecode(I('get.fkeyword',$this->keywordDefault))),
            'keymode'=>urldecode(I('get.keymode','1','int')),
            'open_time'=>urldecode(I('get.open_time','0','int')),
            'time_start'=>I('get.time_start',lq_cdate(0,0,(-604800))),
            'time_end'=>I('get.time_end',lq_cdate(0,0)),
            'status'=>I('get.status','0','int'),
        );

        //sql合并
        $sqlwhere_parameter=" 1 ";//sql条件
        if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
            if($search_content_array["keymode"]==1){
                $sqlwhere_parameter.=" and zc_mobile ='".$search_content_array["fkeyword"]."' ";
            }else{
                $sqlwhere_parameter.=" and (zc_name like'".$search_content_array["fkeyword"]."%' or zc_mobile like'".$search_content_array["fkeyword"]."%') ";
            }
        }

        if($search_content_array["status"]){
            if($search_content_array["status"]==999){
                $sqlwhere_parameter.=" and zl_status in(6,7,8) ";
            }else{
                $sqlwhere_parameter.=" and zl_status = ".intval($search_content_array["status"]);
            }
        }
        if($search_content_array["open_time"]==1&&$search_content_array["time_start"]&&$search_content_array["time_end"]){
            $ts=strtotime($search_content_array["time_start"]." 00:00:00");
            $te=strtotime($search_content_array["time_end"]." 23:59:59");
            $sqlwhere_parameter.=" and zn_cdate >=".$ts." and zn_cdate<=".$te;
        }

        $page_config = array(
            'field'=>"`id`,`zc_order_no`,`zn_bank_id`,`zc_bank_name`,`zn_member_id`,`zc_member_account`,`zc_name`,`zc_mobile`,`zl_status`,`zn_mdate`,`zn_cdate`",
            'where'=>$sqlwhere_parameter,
            'order'=>"id DESC"
        );
        $name = "装修贷申请".date("Y-m-d");
        $list = $this->C_D->lqList(0,100000, $page_config);
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        $PHPExcel=new \PHPExcel();


        /*以下是一些设置 ，什么作者  标题啊之类的*/
        $PHPExcel->getProperties()
            ->setCreator("装修贷申请")
            ->setLastModifiedBy("装修贷申请")
            ->setTitle("装修贷申请")
            ->setSubject("装修贷申请")
            ->setDescription("装修贷申请")
            ->setKeywords("装修贷申请")
            ->setCategory("装修贷申请");


        //设置行宽
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(9);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(25);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(25);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(25);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(25);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(25);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(25);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(30);

        //设置水平居中
        $PHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //设置垂直居中
        $PHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //序号	编号	申请时间	会员	申请人	手机	状态
        $num = 1;
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$num, '序号')
            ->setCellValue('B'.$num, '编号')
            ->setCellValue('C'.$num, '申请时间')
            ->setCellValue('D'.$num, '会员')
            ->setCellValue('E'.$num, '申请人')
            ->setCellValue('F'.$num, '手机')
            ->setCellValue('G'.$num, '状态')
            ->setCellValue('H'.$num, '银行');
        $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高



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
                ->setCellValue('B'.$num, $laValue['zc_order_no'])
                ->setCellValue('C'.$num, date('Y-m-d H:i:s',$laValue['zn_cdate']))
                ->setCellValue('D'.$num, $laValue['zc_member_account'])
                ->setCellValue('E'.$num, $laValue['zc_name'])
                ->setCellValue('F'.$num, $laValue['zc_mobile'])
                ->setCellValue('G'.$num, C("LOAN_STATUS")[$laValue["zl_status"]])
                ->setCellValue('H'.$num, $laValue['zc_bank_name']);

            $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        }

        $PHPExcel->getActiveSheet()->setTitle('装修贷申请');
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