<?php
namespace Home\Model;

use LQPublic\Model\ViewModel;

class StudentVodViewModel extends ViewModel
{
    protected $tableName = 'lq_vod';
    protected $viewFields = array(
        "Vod" => array("id"=>"vod_id","zn_cat_id", "zc_title" => "vod_title","zl_status","zc_image", '_type' => 'LEFT'),
//        "LessonLive" => array("id"=>"lesson_id","zc_date","zc_start_time","zc_end_time", "_on" => "Vod.id=LessonVod.zn_cat_id"),
       "Member" => array("zc_school", "zc_nickname", "_on" => "Vod.zn_teacher_id=Member.id"),
    );
}