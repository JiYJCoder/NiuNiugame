<?php //会员 Member 介面操作 
namespace Admin\Controller;

use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use Member\Api\MemberApi as MemberApi;
use Admin\Model\RegionModel as RegionApi;

class MemberController extends PublicController
{
    protected $MemberModel;
    protected $myForm = array(
        //标题
        'tab_title' => array(1 => '帐户信息', 2 => '会员信息', 3 => '邦定信息'),
        //帐户信息
        '1' => array(
            array('text', 'zc_account', "会员帐号", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
            array('password', 'zc_password', "会员密码", 1, '{"required":"1","dataType":"password","dataLength":"","readonly":0,"disabled":0}'),
            array('text', 'zc_mobile', "会员手机", 1, '{"required":"1","dataType":"mobile","dataLength":"11","readonly":0,"disabled":0}'),
        ),
        //个人信息
        '2' => array(
            array('select', 'zl_sex', "会员性别", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择性别"}'),
            array('selectRegion', 'zn_province|zn_city|zn_district', "地区", 1, '{"label":"zc_area","required":"0","please":"请选择"}'),
            array('text', 'zc_nickname', "用户昵称", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
            array('text', 'zc_address', "地址", 1, '{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
            array('image', 'zc_headimg', "头像", 1, '{"type":"avatar","allowOpen":1}'),
        ),
        '3' => array(
            array('textShow', 'zl_account_bind', "帐号", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('textShow', 'zl_openid_bind', "微信", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('textShow', 'zl_mobile_bind', "电话", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('textShow', 'zl_email_bind', "邮箱", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('textShow', 'zc_easemob_account', "环信帐号", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('textShow', 'zc_easemob_password', "环信密码", 1, '{"is_data":"0","creat_hidden":"0"}'),
        ),
    );

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->MemberModel = new MemberApi;
    }

    //列表页
    public function index()
    {
        $this->assign("os_lock", array('edit' => 1, 'delete' => 1, 'visible' => 1, 'cache' => 0, 'search' => 0, 'sort' => 0));//列表锁

        //列表表单初始化****start
        $page_parameter["s"] = $this->getSafeData('s');
        $this->reSearchPara($page_parameter["s"]);//反回搜索数
        $search_content_array = array(
            'pagesize' => urldecode(I('get.pagesize', '0', 'int')),
            'fkeyword' => trim(urldecode(I('get.fkeyword', $this->keywordDefault))),
            'keymode' => urldecode(I('get.keymode', '1', 'int')),
            'open_time' => urldecode(I('get.open_time', '0', 'int')),
            'time_start' => I('get.time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('get.time_end', lq_cdate(0, 0)),
            'role' => I('get.role', '', 'int'),
            'bind' => I('get.bind', '', 'int'),
            'city' => I('get.city', '', 'int'),
            'use' => I('get.use', ''),
        );
        $this->assign("search_content", $search_content_array);//搜索表单赋值
        $role_array = C("MEMBER_ROLE");
        $role_array[6] = '设计师';
        $this->assign("zl_role_str", lqCreatOption($role_array, $search_content_array["role"], "选择角色"));
        $bind_array = array(
            1 => '帐号邦定',
            2 => '微信邦定',
            3 => '电话邦定',
            4 => '邮箱邦定',
        );
        $this->assign("bind_str", lqCreatOption($bind_array, $search_content_array["bind"], "请选择邦定"));

        $this->assign("use_str", lqCreatOption(C("USE_STATUS"), $search_content_array["use"], "请选择使用状态"));

        //sql合并
        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["fkeyword"] && $search_content_array["fkeyword"] != $this->keywordDefault) {
            if ($search_content_array["keymode"] != 1) {
                $sqlwhere_parameter .= " and zc_account ='" . $search_content_array["fkeyword"] . "' ";
            } else {
                $sqlwhere_parameter .= " and (zc_nickname like'" . $search_content_array["fkeyword"] . "%' or zc_mobile like'" . $search_content_array["fkeyword"] . "%') ";
            }
        }

        if ($search_content_array["bind"]) {
            if ($search_content_array["bind"] == 1) {
                $sqlwhere_parameter .= " and zl_account_bind =1 ";
            } elseif ($search_content_array["bind"] == 2) {
                $sqlwhere_parameter .= " and zl_openid_bind =1 ";
            } elseif ($search_content_array["bind"] == 3) {
                $sqlwhere_parameter .= " and zl_mobile_bind =1 ";
            } elseif ($search_content_array["bind"] == 4) {
                $sqlwhere_parameter .= " and zl_email_bind =1 ";
            } else {
                $sqlwhere_parameter .= "";
            }
        }
        if ($search_content_array["use"] != '') {
            $sqlwhere_parameter .= " and zl_visible = " . intval($search_content_array["use"]);
        }

        if ($search_content_array["open_time"] == 1 && $search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
        }
        //首页设置s
        $page_title = array('checkbox' => 'checkbox', 'no' => L("LIST_NO"), 'id' => L("LIST_ID"), 'reg_time' => '注册时间', 'zc_account' => '帐户名/昵称', 'zl_role' => '角色', 'zc_email' => '积分', 'zc_mobile' => '联系电话', 'member_login_clear' => '清零/邦定：帐号/微信/电话/邮箱', 'status' => L("LIST_STAYUS"), 'os' => L("LIST_OS"));
        $page_config = array(
            'field' => "`id`,`zl_role`,`zc_nickname`,`zn_pay_integration`,`zn_rank_integration`,`zc_mobile`,`zl_account_bind`,`zl_openid_bind`,`zl_mobile_bind`,`zl_email_bind`,`zl_visible`,`zn_cdate`,`zn_mdate`",
            'where' => $sqlwhere_parameter,
            'order' => 'id DESC',
            'title' => $page_title,
            'thinkphpurl' => __CONTROLLER__ . "/",
        );
        //列表表单初始化 e

        $count = $this->MemberModel->apiListCount($sqlwhere_parameter);
        $page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->MemberModel->apiListMember($page->firstRow, $page->listRows, $page_config));
        $this->assign('empty_msg', $this->tableEmptyMsg(count($page_title)));
        $this->assign("page_config", $page_config);//列表设置赋值模板
        $this->display();
    }

    // 插入/添加
    public function add()
    {
        if (IS_POST) {
            $data = $this->MemberModel->apiInsertMember();
            if (preg_match('/^([1-9]\d*)$/', $data)) {
                $this->addAdminLog($data);//写入日志
                $this->ajaxReturn(array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"], 'url' => U(CONTROLLER_NAME . '/add')));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $data));
            }
        } else {
            $lcdisplay = 'Public/common-edit';

            //表单数据初始化s
            $form_array = lq_post_memory_data();//获得上次表单的记忆数据
            $form_array["id"] = '';
            $form_array["zl_role_data"] = C('MEMBER_ROLE');
            $form_array["zl_sex_data"] = C('_SEX');
            $form_array["zl_account_bind"] = 1;
            $form_array["zl_mobile_bind"] = 1;
            $form_array["zl_email_bind"] = 1;
            $form_array["zl_openid_bind"] = 0;

            $Form = new Form($this->myForm, $form_array, $this->MemberModel->apiGetCacheComment());
            $this->assign("LQFdata", $Form->createHtml());//表单数据
            //表单数据初始化s
            $this->display($lcdisplay);
        }
    }

    // 更新/编辑
    public function edit()
    {
        if (IS_POST) {
            $data = $this->MemberModel->apiUpdateMember();
            if (preg_match('/^([1-9]\d*)$/', $data)) {

                $this->addAdminLog($data);//写入日志
                $this->ajaxReturn(array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"], 'url' => U(CONTROLLER_NAME . '/edit/tnid/' . $data)));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $data));
            }
        } else {
            $lcdisplay = 'Public/common-edit';

            //读取数据s
            $data = $this->MemberModel->apiGetInfoByID($this->lqgetid);
            if (!$data) {
                $this->error(C("ALERT_ARRAY")["recordNull"]);
            }//无记录

            //表单数据初始化s
            $form_array = array();
            $form_array["os_record_time"] = $this->osRecordTime($data);//操作时间
            foreach ($data as $lnKey => $laValue) {
                $form_array[$lnKey] = $laValue;
            }
            unset($this->myForm[1][1]);
            unset($this->myForm[1][2]);

            if ($data["zl_visible"]) {
                $this->myForm[1][5] = array('textShow', 'zl_role', "会员角色", 1, '{"is_data":"1","creat_hidden":"1"}');
            }
            if ($data["zl_account_bind"]) $this->myForm[1][0] = array('textShow', 'zc_account', "会员帐号", 1, '{"is_data":"0","creat_hidden":"0"}');
            if ($data["zl_email_bind"]) $this->myForm[1][3] = array('textShow', 'zc_email', "会员邮箱", 1, '{"is_data":"0","creat_hidden":"0"}');
            if ($data["zl_mobile_bind"]) $this->myForm[1][4] = array('textShow', 'zc_mobile', "会员手机", 1, '{"is_data":"0","creat_hidden":"0"}');

            $this->myForm[1][7] = array('textShow', 'zc_openid', "openid", 1, '{"is_data":"0","creat_hidden":"0"}');
            $form_array["zl_role_data"] = C('MEMBER_ROLE');
            $form_array["zl_sex_data"] = C('_SEX');
            $Form = new Form($this->myForm, $form_array, $this->MemberModel->apiGetCacheComment());
            $this->assign("LQFdata", $Form->createHtml());//表单数据
            //表单数据初始化e

            $this->display($lcdisplay);
        }
    }


    //管理员管理 - 修改密码
    public function editPass()
    {
        if (IS_POST) {
            $data = $this->MemberModel->apiEditPass();
            if (preg_match('/^([1-9]\d*)$/', $data)) {
                $this->addAdminLog($data);//写入日志
                $this->ajaxReturn(array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"], 'url' => U(CONTROLLER_NAME . '/index')));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $data));
            }
        } else {
            $lcdisplay = 'edit-password';
            $combutton = 'edit';
            $this->assign("pc_button_os", $combutton); //通用按钮

            //读取数据s
            $data = $this->MemberModel->apiGetInfoByID($this->lqgetid);
            if (!$data) {
                $this->error(C("ALERT_ARRAY")["recordNull"]);
            }//无记录

            //表单数据初始化s
            $form_array = array();
            $form_array["os_record_time"] = $this->osRecordTime($data);//操作时间
            foreach ($data as $lnKey => $laValue) {
                $form_array[$lnKey] = $laValue;
            }
            $this->assign("LQFdata", $form_array);//表单数据
            //表单数据初始化e
            $this->display($lcdisplay);
        }
    }

    //更改字段值
    public function opProperty()
    {
        $dataReturn = $this->MemberModel->apiProperty();
        if ($dataReturn["status"] == 1) $this->addAdminLog($dataReturn["id"]);//写入日志
        $this->ajaxReturn($dataReturn);
    }

    //更改  zlvisible
    public function opVisible()
    {
        $dataReturn = $this->MemberModel->apiVisible();
        if ($dataReturn["status"] == 1) {
            $this->addAdminLog($dataReturn["id"]);//写入日志
        }
        $this->ajaxReturn($dataReturn);
    }

    //多记录审批
    public function opVisibleCheckbox()
    {
        $dataReturn = $this->MemberModel->apiVisibleCheckbox();
        if ($dataReturn["status"] == 1) $this->addAdminLog($dataReturn["ids"]);//写入日志
        $this->ajaxReturn($dataReturn);
    }

    //单记录删除
    public function opDelete()
    {
        $dataReturn = $this->MemberModel->apiDelete();
        if ($dataReturn["status"] == 1) $this->addAdminLog($dataReturn["id"]);//写入日志
        $this->ajaxReturn($dataReturn);
    }

    //多记录删除
    public function opDeleteCheckbox()
    {
        $dataReturn = $this->MemberModel->apiDeleteCheckbox();
        if ($dataReturn["status"] == 1) $this->addAdminLog($dataReturn["ids"]);//写入日志
        $this->ajaxReturn($dataReturn);
    }

    //授权会员登录
    public function authLogin()
    {
        $data = $this->MemberModel->apiGetInfoByID($this->lqgetid);
        //会员的后台
        if ($data["zl_visible"] == 1 && $data["zl_role"] == 1) {
            if ($data["zl_is_designer"] == 0) {
                $manage = 0;
            } else {
                $manage = 1;
            }
        } elseif ($data["zl_visible"] == 1 && $data["zl_role"] == 6) {
            $manage = 1;
        } else {
            $manage = 0;
        }
        if ($manage == 1) {
            $this->MemberModel->apiLoginSession($data);
            //写入日志
            $log_data = array(
                'id' => $this->lqgetid,
                'action' => "authLogin",
                'table' => "member",
                'url' => $_SERVER['SERVER_NAME'] . ':' . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"],
                'operator' => session('admin_auth')["id"],
            );
            $this->UserModel->addAdminLog($log_data);
            $lcdisplay = 'auth-login';
            $this->assign("mes", 3);
            $this->display($lcdisplay);
        } else {
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            echo '<script language="javascript">window.opener=null;alert("会员' . $data["zc_account"] . '未授权登录操作");window.close();</script>';
        }
    }

    protected function addAdminLog($id)
    {
        if (!is_numeric($id)) $id = array('in', $id);
        //写入日志
        $log_data = array(
            'id' => $id,
            'action' => ACTION_NAME,
            'table' => "member",
            'url' => $_SERVER['SERVER_NAME'] . ':' . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"],
            'operator' => session('admin_auth')["id"],
        );
        $this->UserModel->addAdminLog($log_data);
    }

    //数据导出
    public function opExportXls()
    {
        error_reporting(E_ALL); //开启错误
        set_time_limit(0); //脚本不超时

        $page_parameter["s"] = $this->getSafeData('s');
        $this->reSearchPara($page_parameter["s"]);//反回搜索数
        $search_content_array = array(
            'pagesize' => urldecode(I('get.pagesize', '0', 'int')),
            'fkeyword' => trim(urldecode(I('get.fkeyword', $this->keywordDefault))),
            'keymode' => urldecode(I('get.keymode', '1', 'int')),
            'open_time' => urldecode(I('get.open_time', '0', 'int')),
            'time_start' => I('get.time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('get.time_end', lq_cdate(0, 0)),
            'role' => I('get.role', '', 'int'),
            'bind' => I('get.bind', '', 'int'),
            'city' => I('get.city', '', 'int'),
            'use' => I('get.use', ''),
        );
        $this->assign("search_content", $search_content_array);//搜索表单赋值
        $role_array = C("MEMBER_ROLE");
        $role_array[6] = '设计师';
        $this->assign("zl_role_str", lqCreatOption($role_array, $search_content_array["role"], "选择角色"));
        $bind_array = array(
            1 => '帐号邦定',
            2 => '微信邦定',
            3 => '电话邦定',
            4 => '邮箱邦定',
        );

        //sql合并
        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["fkeyword"] && $search_content_array["fkeyword"] != $this->keywordDefault) {
            if ($search_content_array["keymode"] == 1) {
                $sqlwhere_parameter .= " and zc_account ='" . $search_content_array["fkeyword"] . "' ";
            } else {
                $sqlwhere_parameter .= " and (zc_nickname like'" . $search_content_array["fkeyword"] . "%' or zc_mobile like'" . $search_content_array["fkeyword"] . "%') ";
            }
        }

        if ($search_content_array["bind"]) {
            if ($search_content_array["bind"] == 1) {
                $sqlwhere_parameter .= " and zl_account_bind =1 ";
            } elseif ($search_content_array["bind"] == 2) {
                $sqlwhere_parameter .= " and zl_openid_bind =1 ";
            } elseif ($search_content_array["bind"] == 3) {
                $sqlwhere_parameter .= " and zl_mobile_bind =1 ";
            } elseif ($search_content_array["bind"] == 4) {
                $sqlwhere_parameter .= " and zl_email_bind =1 ";
            } else {
                $sqlwhere_parameter .= "";
            }
        }

        if ($search_content_array["use"] != '') {
            $sqlwhere_parameter .= " and zl_visible = " . intval($search_content_array["use"]);
        }

        if ($search_content_array["open_time"] == 1 && $search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
        }
        $page_config = array(
            'field' => "`id`,`zl_role`,`zc_account`,`zc_nickname`,`zn_pay_integration`,`zn_rank_integration`,`zc_mobile`,`zc_email`,`zl_visible`,`zn_cdate`,`zn_mdate`",
            'where' => $sqlwhere_parameter,
            'order' => 'id DESC',
        );
        $list = $this->MemberModel->apiListMember(0, 100000, $page_config);
        $name = "会员统计" . date("Y-m-d");

        import("Org.Util.PHPExcel");
        $PHPExcel = new \PHPExcel();

        $PHPExcel->getProperties()
            ->setCreator("会员统计")
            ->setLastModifiedBy("会员统计")
            ->setTitle("会员统计")
            ->setSubject("会员统计")
            ->setDescription("会员统计")
            ->setKeywords("会员统计")
            ->setCategory("会员统计");

        //设置行宽
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(9);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(25);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(30);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(30);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(20);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(35);
        $PHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(25);

        //设置水平居中
        $PHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //设置垂直居中
        $PHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //序号 注册/修改时间	ID/帐户名/昵称	角色/设计师		联系电话	清零/邦定	状态
        $num = 1;
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $num, '序号')
            ->setCellValue('B' . $num, '注册时间')
            ->setCellValue('C' . $num, '帐户名')
            ->setCellValue('D' . $num, '昵称')
            ->setCellValue('E' . $num, '角色')
            ->setCellValue('F' . $num, '邮箱')
            ->setCellValue('G' . $num, '联系电话');
        $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高

        foreach ($list as $lnKey => $laValue) {
            $num = $laValue['no'] + 1;
            $PHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $laValue['no'])
                ->setCellValue('B' . $num, date('Y-m-d H:i:s', $laValue['zn_cdate']))
                ->setCellValue('C' . $num, $laValue['zc_account'])
                ->setCellValue('D' . $num, $laValue['zc_nickname'])
                ->setCellValue('E' . $num, explode("/", $laValue['role_label'])[0])
                ->setCellValue('F' . $num, $laValue['zc_email'])
                ->setCellValue('G' . $num, $laValue["zc_mobile"]);

            $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        }

        $PHPExcel->getActiveSheet()->setTitle('会员数据');
        $PHPExcel->setActiveSheetIndex(0);
        header('Content-Type: applicationnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}

?>