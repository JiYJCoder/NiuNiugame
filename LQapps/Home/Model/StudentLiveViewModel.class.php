<?php
namespace Home\Model;

use LQPublic\Model\ViewModel;

class StudentLiveViewModel extends ViewModel
{
    protected $tableName = 'lq_live';
    protected $viewFields = array(
        "Live" => array("id"=>"live_id","zn_cat_id", "zc_title" => "live_title","zl_status","zc_image", '_type' => 'LEFT'),
//        "LessonLive" => array("id"=>"lesson_id","zc_date","zc_start_time","zc_end_time", "_on" => "Live.id=LessonLive.zn_cat_id"),
       "Member" => array("zc_school", "zc_nickname", "_on" => "Live.zn_teacher_id=Member.id"),
    );
}