<?php
namespace Home\Model;

use LQPublic\Model\ViewModel;

class LessonVodViewModel extends ViewModel
{
    protected $viewFields = array(
        "LessonVod" => array("id", "zc_title",'zc_vod_info', '_type' => 'LEFT'),
        "Vod" => array("zn_fid ","id"=>"vod_id", "zn_cat_id", "zn_teacher_id", "zc_image", "zc_title"=>"lesson_title", "_on" => "LessonVod.zn_cat_id=Vod.id"),
        "Member" => array("zc_nickname", "zc_school", "_on" => "Vod.zn_teacher_id=Member.id")
    );
}