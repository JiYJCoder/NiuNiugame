<?php
namespace Admin\Model;
use Think\Model;
use Member\Api\MemberApi as MemberApi;

class GameLogModel extends PublicModel {

    /** 初始化*/
    public function __construct() {
        parent::__construct();
        $this->pc_os_label =  "zc_title";//数据表显示标题字段
        $this->pc_index_list =  "RoomCard/index";//ajax返回首页
        $this->member_model = new MemberApi();
    }

    //列表页
    public function groupList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
            $list[$lnKey]['title'] = $laValue["zc_title"];

            $list[$lnKey]['zn_room_type_label'] = C('ROOM_SET')['room_type'][$laValue['zn_room_type']];
            $list[$lnKey]['zn_confirm_label'] = C('ROOM_SET')['confirm'][$laValue['zn_confirm']];
            $list[$lnKey]['zn_play_type_label'] = C('ROOM_SET')['play_type'][$laValue['zn_play_type']];
            $list[$lnKey]['zn_pay_type_label'] = C('ROOM_SET')['pay_type'][$laValue['zn_pay_type']];
            $list[$lnKey]['zn_extract_label'] = $laValue['zn_extract']."%";
            $list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
            $list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
            $list[$lnKey]['zc_member_label'] = $this->member_model->apiGetInfo($laValue['zn_member_id'])['zc_nickname']."/ ( ".$laValue['zn_member_id']." )";
            $list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }

    //列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
            $list[$lnKey]['title'] = $laValue["zc_title"];

            $list[$lnKey]['zn_room_type_label'] = C('ROOM_SET')['room_type'][$laValue['zn_room_type']];
            $list[$lnKey]['zn_confirm_label'] = C('ROOM_SET')['confirm'][$laValue['zn_confirm']];
            $list[$lnKey]['zn_play_type_label'] = C('ROOM_SET')['play_type'][$laValue['zn_play_type']];
            $list[$lnKey]['zn_pay_type_label'] = C('ROOM_SET')['pay_type'][$laValue['zn_pay_type']];
            $list[$lnKey]['zn_extract_label'] = $laValue['zn_extract']."%";
            $list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
            $list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
            $list[$lnKey]['zc_member_label'] = $this->member_model->apiGetInfo($laValue['zn_member_id'])['zc_nickname']."/ ( ".$laValue['zn_member_id']." )";
            $list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }

    //数据保存
    protected function _before_write(&$data){}
    public function lqSubmit(){return $this->lqCommonSave();}

    //确保tag|有效性
    protected function str_replace_tag($value){return str_replace("｜","|",$value);}

    //确保keyword|有效性
    protected function str_replace_keyword($value){return str_replace("，",",",$value);}


    //更改-是非首页 
    public function setProperty() {
        $lcop=I("get.tcop",'is_index');
        $data=array();
        $data["id"] = I("get.tnid",'0','int');
        if($lcop=='is_index'){
            $data['zl_is_index'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
            $op_data= array("status" => $data['zl_is_index'], "txt" => $data['zl_is_index'] == 1 ? "是首页" : "非首页" ) ;
        }elseif($lcop=='is_good'){
            $data['zl_is_good'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
            $op_data= array("status" => $data['zl_is_good'], "txt" => $data['zl_is_good'] == 1 ? "是精品" : "非精品" ) ;
        }else{
            return array('status' => 0, 'msg' => L("ALERT_ARRAY")["dataOut"]);
        }
        $data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
            $this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' =>$op_data );
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }

}

?>
