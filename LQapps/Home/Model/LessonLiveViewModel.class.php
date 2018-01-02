<?php
namespace Home\Model;

use LQPublic\Model\ViewModel;

class LessonLiveViewModel extends ViewModel
{
    protected $viewFields = array(
        "LessonLive" => array("id", "zc_title","zc_date","zc_start_time","zc_end_time", '_type' => 'LEFT'),
        "Live" => array("zn_fid ","id"=>"live_id", "zn_cat_id", "zn_teacher_id", "zc_image", "zc_title"=>"lesson_title", "_on" => "LessonLive.zn_cat_id=Live.id"),
        "Member" => array("zc_nickname", "zc_school", "_on" => "Live.zn_teacher_id=Member.id")
    );
}