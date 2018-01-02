<?php
namespace Home\Model;

use LQPublic\Model\ViewModel;

class LiveRecordViewModel extends ViewModel
{
    protected $viewFields = array(
        "LiveRecord" => array("id","zn_live_id", "zn_member_id", '_type' => 'LEFT'),
        "Live" => array("zn_fid ", "zn_cat_id", "zn_teacher_id", "zc_image", "zc_title", "_on" => "LiveRecord.zn_live_id=Live.id"),
        "Member"=>array("zc_nickname","zc_school","_on" => "Live.zn_teacher_id=Member.id")
    );
}