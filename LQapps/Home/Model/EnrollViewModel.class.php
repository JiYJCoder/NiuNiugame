<?php
namespace Home\Model;

use LQPublic\Model\ViewModel;

class EnrollViewModel extends ViewModel
{
    protected $tableName = 'lq_member_enroll';
    protected $viewFields = array(
        "MemberEnroll" => array("id", "zn_member_id","zn_cdate" ,'_type' => 'LEFT'),
//        "Live" => array("zn_fid ", "zn_cat_id", "zc_title", "_on" => "MemberEnroll.zn_object_id=Live.id"),
        "Member"=>array("zc_nickname","zc_school","_on" => "MemberEnroll.zn_member_id=Member.id")
    );
}