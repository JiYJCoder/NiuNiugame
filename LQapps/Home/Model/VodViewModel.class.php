<?php
namespace Home\Model;

use LQPublic\Model\ViewModel;

class VodViewModel extends ViewModel
{
    protected $viewFields = array(
        "Vod" => array("id","zn_fid","zn_cat_id","zc_summary","zc_image","zn_enroll_num","zn_fav_num", "zc_title", '_type' => 'LEFT'),
//        "LessonVod" => array("zc_title"=>"vod_title","_on" => "Vod.id=LessonVod.zn_cat_id"),
        "Member" => array("zc_school", "zc_nickname", "_on" => "Vod.zn_teacher_id=Member.id"),
    );
}