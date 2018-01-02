<?php
/*数据汇总
****
*/
namespace Admin\Controller;

use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use Member\Api\MemberApi as MemberApi;

class ExportController extends PublicController
{
    public $model_article, $model_designer, $model_designer_works, $model_diary, $product_cat, $model_product;

    public function __construct()
    {
        parent::__construct();
        $search_content_array = array(
            'time_start' => I('get.time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('get.time_end', lq_cdate(0, 0)),
        );
        $this->assign("search_content", $search_content_array);//搜索表单赋值


        C("TOKEN_ON", false);

        $this->model_article = D('Article');
        $this->model_designer = D('Designer');
        $this->model_designer_works = D("DesignerWorks");
        $this->model_diary = D("HdDiary");
        $this->product_cat = D("ProductCat");
        $this->model_product = D("Product");
    }

    //常规汇总
    public function index()
    {
        $this->assign("sys_current", '<ol class="breadcrumb" style="padding:10px 0px;margin:5px 0px;"><span><a><i class="fa fa-location-arrow"></i> 当前位置：</a></span><li><a href="/sys-index.php/Index/index" title="">系统桌面</a></li><li><a href="javascript:;"> 狸想家平台</a></li><li><a href="javascript:;">财务</a></li><li><a href="javascript:;">报表汇总
</a></li><li class="active">常规汇总</li></ol>');//搜索表单赋值
        $this->assign("sys_heading", '常规汇总');
        $this->display('index');
    }

    /////数据汇出操作
    public function opExportXls()
    {
        $op = I("post.op");
        if (!$op) $this->error("没有数据汇出");

        switch ($op) {
            case "article" :
                $this->articleExport();
                break;
            case "designer" :
                $this->designerExport();
                break;
            case "case" :
                $this->caseExport();
                break;
            case "diary" :
                $this->diaryExport();
                break;
            case "category" :
                $this->categoryExport();
                break;
            case "product" :
                $this->productExport();
                break;
            case "content" :
                $this->contentExport();
                break;
            case "member" :
                $this->memberExport();
                break;
        }
    }

    private function _set_excel($PHPExcel, $key, $defaut_width = array())
    {
        $cell_array = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
        if (!$defaut_width) $defaut_width = array(30, 60, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15);

        //设置行宽
        for ($i = 0; $i <= $key; $i++) {
            $PHPExcel->setActiveSheetIndex(0)->getColumnDimension($cell_array[$i])->setWidth($defaut_width[$i]);
            $PHPExcel->getActiveSheet()->getStyle($cell_array[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $PHPExcel->getActiveSheet()->getStyle($cell_array[$i])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }
    }

    //数据导出  装修攻略
    public function articleExport()
    {
        error_reporting(E_ALL); //开启错误
        set_time_limit(0); //脚本不超时

        $search_content_array = array(
            'time_start' => I('post.article_time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('post.article_time_end', lq_cdate(0, 0))
        );

        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zd_send_time >=" . $ts . " and zd_send_time<=" . $te;
        }

        $page_config = array(
            'field' => "`id`,`zn_cat_id`,`zc_title`,`zn_sort`,`zl_is_index`,`zl_is_good`,`zl_visible`,`zn_page_view`,`zn_agrees`,`zd_send_time`",
            'where' => $sqlwhere_parameter,
            'order' => 'zn_sort,id DESC',
        );

        $list = $this->model_article->lqList(0, 100000, $page_config);
        $name = "装修攻略_" . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"];

        import("Org.Util.PHPExcel");
        $PHPExcel = new \PHPExcel();

        $PHPExcel->getProperties()
            ->setCreator("装修攻略")
            ->setLastModifiedBy("装修攻略")
            ->setTitle("装修攻略")
            ->setSubject("装修攻略")
            ->setDescription("装修攻略")
            ->setKeywords("装修攻略")
            ->setCategory("装修攻略");

        $width_arr = array(30, 30, 60, 15, 15, 15, 15, 15, 15, 15, 15, 15);
        $this->_set_excel($PHPExcel, 12, $width_arr);

        // 日期	 发布时间 题目	数量	分类	推荐首页	推荐精品	访问次数	点赞次数	分享次数	排序	状态
        $num = 1;
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $num, '日期')
            ->setCellValue('B' . $num, '发布时间')
            ->setCellValue('C' . $num, '题目')
            ->setCellValue('D' . $num, '数量')
            ->setCellValue('E' . $num, '分类')
            ->setCellValue('F' . $num, '推荐首页')
            ->setCellValue('G' . $num, '推荐精品')
            ->setCellValue('H' . $num, '访问次数')
            ->setCellValue('I' . $num, '点赞次数')
            ->setCellValue('J' . $num, '分享次数')
            ->setCellValue('K' . $num, '排序')
            ->setCellValue('L' . $num, '状态');
        $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        $PHPExcel->getActiveSheet()->getStyle('A1:K1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $PHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $PHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->getStartColor()->setRGB('f7c389');

        foreach ($list as $lnKey => $laValue) {
            $num = $laValue['no'] + 1;
            $PHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"])
                ->setCellValue('B' . $num, date("Y-m-d  H:i:s",$laValue["zd_send_time"]))
                ->setCellValue('C' . $num, "(" . $laValue['id'] . "): " . $laValue['zc_title'])
                ->setCellValue('D' . $num, 1)
                ->setCellValue('E' . $num, $laValue['zn_cat_id_label'])
                ->setCellValue('F' . $num, $laValue['zl_is_index_label'])
                ->setCellValue('G' . $num, $laValue['zl_is_good_label'])
                ->setCellValue('H' . $num, $laValue["zn_page_view"])
                ->setCellValue('I' . $num, $laValue["zn_agrees"])
                ->setCellValue('J' . $num, " ")
                ->setCellValue('K' . $num, $laValue["zn_sort"])
                ->setCellValue('L' . $num, $laValue["visible_label"]);

            $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        }

        $PHPExcel->getActiveSheet()->setTitle('装修攻略' . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"]);
        $PHPExcel->setActiveSheetIndex(0);
        header('Content-Type: applicationnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //数据导出  设计师
    public function designerExport()
    {
        error_reporting(E_ALL); //开启错误
        set_time_limit(0); //脚本不超时

        $search_content_array = array(
            'time_start' => I('post.designer_time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('post.designer_time_end', lq_cdate(0, 0))
        );

        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
        }
// 时间	账号	昵称	设计师级别	入行年份	个人风格	推荐首页	访问次数	关注次数	预约次数	好评指数	作品数量	上架数量	首页推荐	状态
        $page_config = array(
            'field' => "`id`,`zc_member_account`,`zc_nickname`,`zl_level`,`zl_good_index`,`zl_is_index`,`zn_join_year`,`zn_subscribe`,`zc_style_tag`",
            'where' => $sqlwhere_parameter,
            'order' => 'zn_cdate,id DESC',
        );

        $list = $this->model_designer->lqList(0, 100000, $page_config);

        $name = "设计师_" . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"];

        import("Org.Util.PHPExcel");
        $PHPExcel = new \PHPExcel();

        $PHPExcel->getProperties()
            ->setCreator("设计师")
            ->setLastModifiedBy("设计师")
            ->setTitle("设计师")
            ->setSubject("设计师")
            ->setDescription("设计师")
            ->setKeywords("设计师")
            ->setCategory("设计师");

        $this->_set_excel($PHPExcel, 14);

        // 时间	账号	昵称	设计师级别	入行年份	个人风格	推荐首页	访问次数	关注次数	预约次数	好评指数	作品数量	上架数量	首页推荐	状态
        $num = 1;
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $num, '日期')
            ->setCellValue('B' . $num, '账号')
            ->setCellValue('C' . $num, '昵称')
            ->setCellValue('D' . $num, '设计师级别')
            ->setCellValue('E' . $num, '入行年份')
            ->setCellValue('F' . $num, '个人风格')
            ->setCellValue('G' . $num, '推荐首页')
            ->setCellValue('H' . $num, '访问次数')
            ->setCellValue('I' . $num, '关注次数')
            ->setCellValue('J' . $num, '预约次数')
            ->setCellValue('K' . $num, '好评指数')
            ->setCellValue('L' . $num, '作品数量')
            ->setCellValue('M' . $num, '上架数量')
            ->setCellValue('N' . $num, '状态');
        $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        $PHPExcel->getActiveSheet()->getStyle('A1:N1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $PHPExcel->getActiveSheet()->getStyle('A1:N1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $PHPExcel->getActiveSheet()->getStyle('A1:N1')->getFill()->getStartColor()->setRGB('f7c389');

        foreach ($list as $lnKey => $laValue) {
            $num = $laValue['no'] + 1;

            $PHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"])
                ->setCellValue('B' . $num, "(" . $laValue['id'] . "): " . $laValue['zc_member_account'])
                ->setCellValue('C' . $num, $laValue['zc_nickname'])
                ->setCellValue('D' . $num, $laValue['zl_level_label'])
                ->setCellValue('E' . $num, $laValue['zn_join_year'])
                ->setCellValue('F' . $num, $laValue['style_tag'])
                ->setCellValue('G' . $num, $laValue["zl_is_index_label"])
                ->setCellValue('H' . $num, " ")
                ->setCellValue('I' . $num, $laValue['zn_subscribe'])
                ->setCellValue('J' . $num, $laValue["application_num"])
                ->setCellValue('K' . $num, $laValue["zl_good_index_label"])
                ->setCellValue('L' . $num, $laValue["count"])
                ->setCellValue('M' . $num, $laValue["count_visible"])
                ->setCellValue('N' . $num, $laValue["is_visible"]);

            $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        }

        $PHPExcel->getActiveSheet()->setTitle('设计师' . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"]);
        $PHPExcel->setActiveSheetIndex(0);
        header('Content-Type: applicationnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //数据导出  案例
    public function caseExport()
    {
        error_reporting(E_ALL); //开启错误
        set_time_limit(0); //脚本不超时

        $search_content_array = array(
            'time_start' => I('post.designer_time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('post.designer_time_end', lq_cdate(0, 0))
        );

        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
        }
        //时间	作品名称	风格	户型	面积	数量	排序	推荐首页	访问次数	点赞次数	分享次数	设计师	状态
        $page_config = array(
            'field' => "`id`,`zn_designer_id`,`zc_thumb`,`zc_caption`,`zn_sort`,`zn_clicks`,`zn_agrees`,`zn_style`,`zn_household`,`zn_area`,`zl_visible`,`zc_works_photos`,`zl_is_index`",
            'where' => $sqlwhere_parameter,
            'order' => 'zn_sort,id DESC',
        );

        $list = $this->model_designer_works->lqList(0, 100000, $page_config);

        $excel_name = '案例';
        $name = $excel_name . "_" . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"];

        import("Org.Util.PHPExcel");
        $PHPExcel = new \PHPExcel();

        $PHPExcel->getProperties()
            ->setCreator($excel_name)
            ->setLastModifiedBy($excel_name)
            ->setTitle($excel_name)
            ->setSubject($excel_name)
            ->setDescription($excel_name)
            ->setKeywords($excel_name)
            ->setCategory($excel_name);

        $this->_set_excel($PHPExcel, 13);

        //时间	作品名称	风格	户型	面积	数量	排序	推荐首页	访问次数	点赞次数	分享次数	设计师	状态
        $num = 1;
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $num, '日期')
            ->setCellValue('B' . $num, '作品名称')
            ->setCellValue('C' . $num, '风格')
            ->setCellValue('D' . $num, '户型')
            ->setCellValue('E' . $num, '面积')
            ->setCellValue('F' . $num, '数量')
            ->setCellValue('G' . $num, '排序')
            ->setCellValue('H' . $num, '推荐首页')
            ->setCellValue('I' . $num, '访问次数')
            ->setCellValue('J' . $num, '点赞次数')
            ->setCellValue('K' . $num, '分享次数')
            ->setCellValue('L' . $num, '设计师')
            ->setCellValue('M' . $num, '状态');
        $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        $PHPExcel->getActiveSheet()->getStyle('A1:M1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $PHPExcel->getActiveSheet()->getStyle('A1:M1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $PHPExcel->getActiveSheet()->getStyle('A1:M1')->getFill()->getStartColor()->setRGB('f7c389');

        foreach ($list as $lnKey => $laValue) {
            $num = $laValue['no'] + 1;
            $attr_array = explode("/", $laValue['attribute']);

            $PHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"])
                ->setCellValue('B' . $num, "(" . $laValue['id'] . "): " . $laValue['zc_caption'])
                ->setCellValue('C' . $num, $attr_array[0])
                ->setCellValue('D' . $num, $attr_array[1])
                ->setCellValue('E' . $num, $attr_array[2])
                ->setCellValue('F' . $num, 1)
                ->setCellValue('G' . $num, $laValue["zn_sort"])
                ->setCellValue('H' . $num, $laValue["zl_is_index_label"])
                ->setCellValue('I' . $num, $laValue['zn_clicks'])
                ->setCellValue('J' . $num, $laValue["zn_agrees"])
                ->setCellValue('K' . $num, " ")
                ->setCellValue('L' . $num, $laValue["designer"])
                ->setCellValue('M' . $num, $laValue["visible_label"]);

            $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        }

        $PHPExcel->getActiveSheet()->setTitle($excel_name . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"]);
        $PHPExcel->setActiveSheetIndex(0);
        header('Content-Type: applicationnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //数据导出  装修日记
    public function diaryExport()
    {
        error_reporting(E_ALL); //开启错误
        set_time_limit(0); //脚本不超时

        $search_content_array = array(
            'time_start' => I('post.diary_time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('post.diary_time_end', lq_cdate(0, 0))
        );

        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
        }
        //时间	标题	风格	面积	户型	数量	步骤数量	推荐首页	访问次数	点赞次数	分享次数	设计师	状态
        $page_config = array(
            'field' => "`id`,`zc_title`,`zn_area`,`zc_style`,`zn_household`,`zn_member_id`,`zc_member_account`,`zn_designer_id`,`zc_image`,`zc_nickname`,`zl_is_index`,`zl_member_apply`,`zl_visible`,`zn_agrees`,`zn_page_view`,`zn_mdate`,`zn_cdate`",
            'where' => $sqlwhere_parameter,
            'order' => 'id DESC',
        );

        $list = $this->model_diary->lqList(0, 100000, $page_config);

        $excel_name = '装修日记';
        $name = $excel_name . "_" . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"];

        import("Org.Util.PHPExcel");
        $PHPExcel = new \PHPExcel();

        $PHPExcel->getProperties()
            ->setCreator($excel_name)
            ->setLastModifiedBy($excel_name)
            ->setTitle($excel_name)
            ->setSubject($excel_name)
            ->setDescription($excel_name)
            ->setKeywords($excel_name)
            ->setCategory($excel_name);

        $this->_set_excel($PHPExcel, 12);

        //时间	标题	风格	面积	户型	数量	骤数数量	推荐首页	访问次数	点赞次数	分享次数	设计师	状态

        $num = 1;
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $num, '日期')
            ->setCellValue('B' . $num, '标题')
            ->setCellValue('C' . $num, '风格')
            ->setCellValue('D' . $num, '面积')
            ->setCellValue('E' . $num, '户型')
            ->setCellValue('F' . $num, '数量')
            ->setCellValue('G' . $num, '步骤数量')
            ->setCellValue('H' . $num, '推荐首页')
            ->setCellValue('I' . $num, '访问次数')
            ->setCellValue('J' . $num, '点赞次数')
            ->setCellValue('K' . $num, '分享次数')
            ->setCellValue('L' . $num, '设计师')
            ->setCellValue('M' . $num, '状态');
        $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        $PHPExcel->getActiveSheet()->getStyle('A1:M1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $PHPExcel->getActiveSheet()->getStyle('A1:M1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $PHPExcel->getActiveSheet()->getStyle('A1:M1')->getFill()->getStartColor()->setRGB('f7c389');

        foreach ($list as $lnKey => $laValue) {
            $num = $laValue['no'] + 1;

            $PHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"])
                ->setCellValue('B' . $num, "(" . $laValue['id'] . "): " . $laValue['zc_title'])
                ->setCellValue('C' . $num, $laValue['style'])
                ->setCellValue('D' . $num, $laValue['zn_area'])
                ->setCellValue('E' . $num, $laValue['zn_household'])
                ->setCellValue('F' . $num, 1)
                ->setCellValue('G' . $num, $laValue["step_num"])
                ->setCellValue('H' . $num, $laValue["zl_is_index_label"])
                ->setCellValue('I' . $num, $laValue['zn_page_view'])
                ->setCellValue('J' . $num, $laValue["zn_agrees"])
                ->setCellValue('K' . $num, " ")
                ->setCellValue('L' . $num, $laValue["designer"])
                ->setCellValue('M' . $num, $laValue["visible_label"]);

            $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        }

        $PHPExcel->getActiveSheet()->setTitle($excel_name . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"]);
        $PHPExcel->setActiveSheetIndex(0);
        header('Content-Type: applicationnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //数据导出  分类图标
    public function categoryExport()
    {
        error_reporting(E_ALL); //开启错误
        set_time_limit(0); //脚本不超时

        $search_content_array = array(
            'time_start' => I('post.category_time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('post.category_time_end', lq_cdate(0, 0))
        );

        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
        }
        $sqlwhere_parameter .= " and zn_fid = 0";
        //时间	分类名称	二级分类名称	数量	状态
        $page_config = array(
            'field' => "`id`,`zc_caption`,`zl_visible`,`zn_cdate`",
            'where' => $sqlwhere_parameter,
            'order' => 'id DESC',
        );

        $list = $this->product_cat->lqListTotal($page_config);

        $excel_name = '分类图标';
        $name = $excel_name . "_" . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"];

        import("Org.Util.PHPExcel");
        $PHPExcel = new \PHPExcel();

        $PHPExcel->getProperties()
            ->setCreator($excel_name)
            ->setLastModifiedBy($excel_name)
            ->setTitle($excel_name)
            ->setSubject($excel_name)
            ->setDescription($excel_name)
            ->setKeywords($excel_name)
            ->setCategory($excel_name);

        $width_arr = array(30, 45, 45, 15, 15);
        $this->_set_excel($PHPExcel, 5, $width_arr);

        //时间	分类名称	二级分类名称	数量	状态
        $num = 1;
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $num, '日期')
            ->setCellValue('B' . $num, '分类名称')
            ->setCellValue('C' . $num, '二级分类名称')
            ->setCellValue('D' . $num, '数量')
            ->setCellValue('E' . $num, '状态');
        $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        $PHPExcel->getActiveSheet()->getStyle('A1:E1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $PHPExcel->getActiveSheet()->getStyle('A1:E1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $PHPExcel->getActiveSheet()->getStyle('A1:E1')->getFill()->getStartColor()->setRGB('f7c389');

        foreach ($list as $lnKey => $laValue) {
            $num++;

            $PHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"])
                ->setCellValue('B' . $num, $laValue['p_title'])
                ->setCellValue('C' . $num, $laValue['zc_caption'])
                ->setCellValue('D' . $num, $laValue['product_num'])
                ->setCellValue('E' . $num, $laValue['visible_label']);

            $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        }

        $PHPExcel->getActiveSheet()->setTitle($excel_name . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"]);
        $PHPExcel->setActiveSheetIndex(0);
        header('Content-Type: applicationnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //数据导出  建材，软装
    public function productExport()
    {
        error_reporting(E_ALL); //开启错误
        set_time_limit(0); //脚本不超时

        $search_content_array = array(
            'time_start' => I('post.product_time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('post.product_time_end', lq_cdate(0, 0))
        );

        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
        }
        //时间	分类名称	二级分类名称	品牌	产品名称	数量	状态
        $page_config = array(
            'field' => "`id`,`zn_cat_id`,`zn_product_brand_id`,`zc_title`,`zl_visible`,`zn_cdate`",
            'where' => $sqlwhere_parameter,
            'order' => 'id DESC',
        );

        $list = $this->model_product->lqList(0, 100000, $page_config);

        $excel_name = '建材,软装';
        $name = $excel_name . "_" . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"];

        import("Org.Util.PHPExcel");
        $PHPExcel = new \PHPExcel();

        $PHPExcel->getProperties()
            ->setCreator($excel_name)
            ->setLastModifiedBy($excel_name)
            ->setTitle($excel_name)
            ->setSubject($excel_name)
            ->setDescription($excel_name)
            ->setKeywords($excel_name)
            ->setCategory($excel_name);

        $width_arr = array(30, 45, 45, 45, 45, 15, 15);
        $this->_set_excel($PHPExcel, 7, $width_arr);

        //时间	分类名称	二级分类名称	品牌	产品名称	数量	状态
        $num = 1;
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $num, '日期')
            ->setCellValue('B' . $num, '分类名称')
            ->setCellValue('C' . $num, '二级分类名称')
            ->setCellValue('D' . $num, '品牌')
            ->setCellValue('E' . $num, '产品名称')
            ->setCellValue('F' . $num, '数量')
            ->setCellValue('G' . $num, '状态');
        $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        $PHPExcel->getActiveSheet()->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $PHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $PHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->getStartColor()->setRGB('f7c389');

        foreach ($list as $lnKey => $laValue) {
            $num++;

            $PHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"])
                ->setCellValue('B' . $num, $laValue['zn_top_cat_label'])
                ->setCellValue('C' . $num, $laValue['zn_cat_id_label'])
                ->setCellValue('D' . $num, $laValue['zn_product_brand_id_label'])
                ->setCellValue('E' . $num, $laValue['zc_title'])
                ->setCellValue('F' . $num, 1)
                ->setCellValue('G' . $num, $laValue['visible_label']);

            $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        }

        $PHPExcel->getActiveSheet()->setTitle($excel_name . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"]);
        $PHPExcel->setActiveSheetIndex(0);
        header('Content-Type: applicationnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //平台内容统计
    public function contentExport()
    {
        error_reporting(E_ALL); //开启错误
        set_time_limit(0); //脚本不超时

        $search_content_array = array(
            'time_start' => I('post.content_time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('post.content_time_end', lq_cdate(0, 0))
        );

        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
        }
        //////统计查询
        $article_1 = $this->model_article->where("zn_cdate >=" . $ts . " and zn_cdate<=" . $te . " and zn_cat_id = 6")->count();
        $article_2 = $this->model_article->where("zn_cdate >=" . $ts . " and zn_cdate<=" . $te . " and zn_cat_id = 7")->count();
        $article_3 = $this->model_article->where("zn_cdate >=" . $ts . " and zn_cdate<=" . $te . " and zn_cat_id = 8")->count();
        $article_4 = $this->model_article->where("zn_cdate >=" . $ts . " and zn_cdate<=" . $te . " and zn_cat_id = 9")->count();

        $designer_num = $this->model_designer->where($sqlwhere_parameter)->count();
        $case_num = $this->model_designer_works->where($sqlwhere_parameter)->count();
        $diary_num = $this->model_diary->where($sqlwhere_parameter)->count();
        $product_num = $this->model_product->where($sqlwhere_parameter)->count();

        $sum = $article_1 + $article_2 + $article_3 + $article_4 + $designer_num + $case_num + $diary_num + $product_num;

        $excel_name = '平台内容统计';
        $name = $excel_name . "_" . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"];

        import("Org.Util.PHPExcel");
        $PHPExcel = new \PHPExcel();

        $PHPExcel->getProperties()
            ->setCreator($excel_name)
            ->setLastModifiedBy($excel_name)
            ->setTitle($excel_name)
            ->setSubject($excel_name)
            ->setDescription($excel_name)
            ->setKeywords($excel_name)
            ->setCategory($excel_name);

        $PHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $excel_name);
        $PHPExcel->getActiveSheet()->mergeCells("A1:J1");
        $PHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);//行高
        $PHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('A1:J1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $PHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $PHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->getStartColor()->setRGB('f7c389');

        $width_arr = array(30, 15, 15, 15, 15, 15, 15, 15, 15, 15);
        $this->_set_excel($PHPExcel, 10, $width_arr);
        $num = 2;
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $num, '日期')
            ->setCellValue('B' . $num, '新手')
            ->setCellValue('C' . $num, '选材')
            ->setCellValue('D' . $num, '施工')
            ->setCellValue('E' . $num, '风水')
            ->setCellValue('F' . $num, '设计师')
            ->setCellValue('G' . $num, '装修案例')
            ->setCellValue('H' . $num, '装修日记')
            ->setCellValue('I' . $num, '平台商品')
            ->setCellValue('J' . $num, '合计');

        $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        $PHPExcel->getActiveSheet()->getStyle('A2:J2')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $PHPExcel->getActiveSheet()->getStyle('A2:J2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $PHPExcel->getActiveSheet()->getStyle('A2:J2')->getFill()->getStartColor()->setRGB('f7c389');

        $num = 3;
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $num, $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"])
            ->setCellValue('B' . $num, $article_1)
            ->setCellValue('C' . $num, $article_2)
            ->setCellValue('D' . $num, $article_3)
            ->setCellValue('E' . $num, $article_4)
            ->setCellValue('F' . $num, $designer_num)
            ->setCellValue('G' . $num, $case_num)
            ->setCellValue('H' . $num, $diary_num)
            ->setCellValue('I' . $num, $product_num)
            ->setCellValue('J' . $num, $sum);
        $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        $PHPExcel->getActiveSheet()->setTitle($excel_name . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"]);
        $PHPExcel->setActiveSheetIndex(0);
        header('Content-Type: applicationnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //数据导出  会员注册
    public function memberExport()
    {
        error_reporting(E_ALL); //开启错误
        set_time_limit(0); //脚本不超时

        $search_content_array = array(
            'time_start' => I('post.member_time_start', lq_cdate(0, 0, (-2592000))),
            'time_end' => I('post.member_time_end', lq_cdate(0, 0))
        );

        $sqlwhere_parameter = " 1 ";//sql条件
        if ($search_content_array["time_start"] && $search_content_array["time_end"]) {
            $ts = strtotime($search_content_array["time_start"] . " 00:00:00");
            $te = strtotime($search_content_array["time_end"] . " 23:59:59");
            $sqlwhere_parameter .= " and zn_cdate >=" . $ts . " and zn_cdate<=" . $te;
        }

        $excel_name = '会员注册';
        $name = $excel_name . "_" . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"];

        import("Org.Util.PHPExcel");
        $PHPExcel = new \PHPExcel();

        $PHPExcel->getProperties()
            ->setCreator($excel_name)
            ->setLastModifiedBy($excel_name)
            ->setTitle($excel_name)
            ->setSubject($excel_name)
            ->setDescription($excel_name)
            ->setKeywords($excel_name)
            ->setCategory($excel_name);

        $PHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '狸想家平台会员量-' . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"]);
        $PHPExcel->getActiveSheet()->mergeCells("A1:G1");
        $PHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);//行高
        $PHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $PHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $PHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->getStartColor()->setRGB('ffffff');

        ////日期 "微信关注（新增）"	"微信关注（取消）"	"微信关注（净增长）"	"微信关注（总量）"	"会员注册（新增）"	"会员总数（总量）"

        $width_arr = array(30, 25, 25, 25, 25, 25, 25);
        $this->_set_excel($PHPExcel, 7, $width_arr);
        $num = 2;
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $num, '日期')
            ->setCellValue('B' . $num, '微信关注（新增）')
            ->setCellValue('C' . $num, '微信关注（取消）')
            ->setCellValue('D' . $num, '微信关注（净增长）')
            ->setCellValue('E' . $num, '微信关注（总量）')
            ->setCellValue('F' . $num, '会员注册（新增）')
            ->setCellValue('G' . $num, '会员总数（总量）');

        $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
        $PHPExcel->getActiveSheet()->getStyle('A2:G2')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $PHPExcel->getActiveSheet()->getStyle('A2:G2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $PHPExcel->getActiveSheet()->getStyle('A2:G2')->getFill()->getStartColor()->setRGB('f7c389');

        $num = 3;
        $next_day = $ts;
        while ($next_day < $te) {
            $excel_data[] = $this->getDataFollow(date("Y-m-d", $next_day));
            $next_day += 3600 * 24;
        }

        foreach ($excel_data as $key => $value) {
            $PHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $value['time'])
                ->setCellValue('B' . $num, $value['new'])
                ->setCellValue('C' . $num, $value['cancel'])
                ->setCellValue('D' . $num, $value['increase'])
                ->setCellValue('E' . $num, $value['cumulate'])
                ->setCellValue('F' . $num, $value['member'])
                ->setCellValue('G' . $num, $value['member_total']);
            $PHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);//行高
            $num++;
        }

        $PHPExcel->getActiveSheet()->setTitle($excel_name . $search_content_array["time_start"] . " ~ " . $search_content_array["time_end"]);
        $PHPExcel->setActiveSheetIndex(0);
        header('Content-Type: applicationnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //粉丝与会员数据汇总
    protected function getDataFollow($date = '2017-01-01')
    {
        $start_time = strtotime($date . " 00:00:00");
        $end_time = strtotime($date . " 23:59:59");
        $this->model_member = new MemberApi;//实例化会员
        $new = $this->model_member->apiFollowCount("zl_type=1 and zn_cdate >=" . $start_time . " and zn_cdate<=" .
            $end_time);
        $cancel = $this->model_member->apiFollowCount("zl_type=1 and zl_visible=0 and zn_unsubscribe_time >=" . $start_time . " and zn_unsubscribe_time<=" . $end_time);
        $cumulate = $this->model_member->apiFollowCount("zl_type=1 and zn_cdate<=" . $end_time);
        $member = $this->model_member->apiListCount("zn_cdate >=" . $start_time . " and zn_cdate<=" . $end_time);
        $member_total = $this->model_member->apiListCount("zn_cdate<=" . $end_time);

        $datasets = array();
        $datasets["new"] = $new;
        $datasets["cancel"] = $cancel;
        $datasets["increase"] = $new - $cancel;
        $datasets["cumulate"] = $cumulate;
        $datasets["member"] = $member;
        $datasets["member_total"] = $member_total;
        $datasets['time'] = $date;

        return $datasets;
    }
}

?>