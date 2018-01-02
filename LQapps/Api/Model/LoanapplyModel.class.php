<?php
namespace Api\Model;

defined('in_lqweb') or exit('Access Invalid!');

class LoanapplyModel extends PublicModel
{
    protected $model_progress;
    protected $tableName = 'loan_apply';

    public function __construct()
    {
        parent::__construct();
        $this->model_progress = M("loan_apply_progress");
    }

    //是否有申请记录
    public function isApply($sqlwhere = '1')
    {
        return $this->where($sqlwhere)->getField("id");
    }

    /*
     * 申请贷款成功后，插入进度明细
     *
     */
    public function addDetail($loan_id, $detail = '')
    {
        $data_1 = array(
            "zn_loan_apply_id" => $loan_id,
            "zl_role" => $detail["zl_role"],
            "zn_operate_id" => $detail["zn_operate_id"],
            "zc_operate_account" => $detail["zc_operate_account"],
            "zl_progress" => 1,
            "zl_status" => 1,
            "zc_remarks" => '申请成功',
            "zn_cdate" => NOW_TIME,
            "zn_mdate" => NOW_TIME,
        );
        $this->model_progress->add($data_1);

        $data_2 = array(
            "zn_loan_apply_id" => $loan_id,
            "zc_operate_account" => ' ',
            "zc_remarks" => ' ',
            "zl_progress" => 2,
        );
        $this->model_progress->add($data_2);

        $data_3 = array(
            "zn_loan_apply_id" => $loan_id,
            "zc_operate_account" => ' ',
            "zc_remarks" => ' ',
            "zl_progress" => 3,
        );
        $this->model_progress->add($data_3);
    }

    /*
     * 获取贷款进度
     */
    public function getStep($apply_id)
    {
        $apply_detail = $this->model_progress->where("zn_loan_apply_id = " . $apply_id)->order("zl_progress ASC")->select();

        $step_key = 1;
        foreach ($apply_detail as $lnKey => $laValue) {
            $step_now = $laValue['zl_status'] ? 1 : 0;

            $time = '';
            if ($step_now == 1) {
                $time = date("Y.m.d", $laValue['zn_cdate']);
                $progress_detail[] = array(
                    'no' => $step_key,
                    'step_now' => $step_now,
                    'icon' => API_DOMAIN . '/Public/Static/images/loan/' . $step_key . '.png',
                    'title' => C("LOAN_PROGRESS")[$step_key],
                    'step_title' => C("LOAN_STATUS")[$laValue['zl_status']],
                    'time' => $time
                );
                $last_step = $step_key;
            } else {
                $progress_detail[] = array(
                    'no' => $step_key,
                    'step_now' => $step_now,
                    'icon' => API_DOMAIN . '/Public/Static/images/loan/' . $step_key . '.png',
                    'title' => C("LOAN_DEFAULT_STATUS")[$lnKey][0],
                    'step_title' => C("LOAN_DEFAULT_STATUS")[$lnKey][1],
                    'time' => $time
                );
            }

            if ($lnKey == 2 and $laValue['zl_status'] == 7) {
                $progress_detail[2]['title'] = '申请终止';
                $progress_detail[2]['icon'] = API_DOMAIN . '/Public/Static/images/loan/4.png';
            } elseif ($lnKey == 2) {
                $progress_detail[2]['title'] = '申请成功';
                $progress_detail[2]['step_title'] = C("LOAN_STATUS")[9];
            }
            $step_key++;
        }
        if ($last_step != 3) $progress_detail[2]['icon'] = API_DOMAIN . '/Public/Static/images/loan/5.png';
        $progress_detail[$last_step - 1]['step_now'] = 2;
        //pr($progress_detail);
        return $progress_detail;
    }
}