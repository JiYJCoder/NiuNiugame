<?php
namespace Home\Model;

use LQPublic\Model\ViewModel;

class EnrollLiveViewModel extends ViewModel
{
    protected $tableName = 'lq_member_enroll';
    protected $viewFields = array(
        "MemberEnroll" => array( 'zn_object_id','_type' => 'LEFT'),
        "Live" => array("zn_fid ", "zn_cat_id","zc_image", "zc_title", "_on" => "MemberEnroll.zn_object_id=Live.id"),
        "LessonLive" => array("id"=>"live_lesson_id","zc_date ", "zc_start_time", "zc_end_time", "_on" => "MemberEnroll.zn_object_id=LessonLive.zn_cat_id"),
        "Member"=>array("id"=>"teacher_id","zc_nickname","zc_school","_on" => "MemberEnroll.zn_teacher_id=Member.id")
    );
}