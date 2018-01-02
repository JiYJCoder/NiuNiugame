<?php
namespace Home\Model;

use LQPublic\Model\ViewModel;

class LiveViewModel extends ViewModel
{
    protected $viewFields = array(
        "Live" => array("id","zn_fid","zn_cat_id", "zc_title","zc_summary","zn_fav_num","zn_teacher_id","zn_enroll_num","zc_image", '_type' => 'LEFT'),
        "LessonLive" => array("min(zc_date)"=>"zc_date","min(zc_start_time)"=>"zc_start_time","min(zc_end_time)"=>"zc_end_time", "_on" => "Live.id=LessonLive.zn_cat_id"),
       "Member" => array("zc_school", "zc_nickname", "_on" => "Live.zn_teacher_id=Member.id"),
    );
}