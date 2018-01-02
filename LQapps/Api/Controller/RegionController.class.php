<?php
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:区域
家装:hd(home-decoration)
*****************************************************************************
(1) 返回内容为json格式，采用UTF-8编码。
(2) 信息内容中含有时间字段的，字段值为urlEncode格式。
(3) 返回内容{"status":0,"msg":"操作提示","data":"回调数据","url":"回调响应地址","note":"备注"};
(4) 用户请求加权文档，统一传入uid,token两值。
*****************************************************************************
*/

namespace Api\Controller;
use Think\Controller;
defined('in_lqweb') or exit('Access Invalid!');

class RegionController extends PublicController{
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
    }
	
	//会员首页
    public function index(){
		$this->ajaxReturn(array('status'=>0,'msg'=>'当前端口暂时关闭','data' =>array(),"url"=>"","note"=>""),$this->JSONP);
    }

	//返回省份
	public function get_province(){
		$region=M("region")->where('zl_visible=1 and zn_fid=1')->order('zn_sort','id desc')->field("`id`,`zc_name` as title")->select();
		if($region){
            $this->ajaxReturn(array('status' => 1,'msg'=>C('ALERT_ARRAY')["success"],'data'=>$region));
        } else {
            $this->ajaxReturn(array('status' => 0,'msg'=>C('ALERT_ARRAY')["error"],'data'=>''));
        }
	}
	
	//返回市或区
	public function get_region(){
		$lnfid = I("get.tnid","440000","int");
		$region=M("region")->where('zl_visible=1 and zn_fid='.$lnfid)->order('zn_sort','id desc')->field("`id`,`zc_name` as title")->select();
		if($region){
            $this->ajaxReturn(array('status' => 1,'msg'=>C('ALERT_ARRAY')["success"],'data'=>$region));
        } else {
            $this->ajaxReturn(array('status' => 0,'msg'=>C('ALERT_ARRAY')["error"],'data'=>''));
        }
	}
	
	
	/*生成 json
	{
                "name":"广州",
                "sub":[
                ],
                "type":0
            },
            {
                "name":"深圳",
                "sub":[
                ],
                "type":0
            },
			'zl_visible=1 and id in (440000,110000,120000,710000,820000,810000)'
			
	$jsonStr='[';
		$province=M("region")->where('zl_visible=1 and  `zn_fid` =1 ')->order('zn_sort','id desc')->field("*")->select();
	    foreach ($province as $k => $v){
			$jsonStr.='{
        "name":"'.$v["zc_name"].'",
        "sub":[
		{"name":"请选择","sub":[]}';
				   $city=M("region")->where('zl_visible=1 and zn_fid='.$v["id"])->order('zn_sort','id desc')->field("*")->select();
				   if($city){
					   foreach ($city as $k1 => $v1){
						   
						   $jsonStr.=',{"name":"'.$v1["zc_name"].'","sub":[';
						   
				   			$district=M("region")->where('zl_visible=1 and zn_fid='.$v1["id"])->order('zn_sort','id desc')->field("*")->select();
							if($district){
								$temp='{"name":"请选择"}';
								foreach ($district as $k2 => $v2){
									 $temp.=',{"name":"'.$v2["zc_name"].'"}';
								}
							}
							$jsonStr.=$temp.'],"type":0}';
					   }
				   }
			
			if(count($province)==($k+1)){
				$jsonStr.='],"type":1}';
			}else{
				$jsonStr.='],"type":1},';
			}
			
        }	
		$jsonStr.=']';			

		$jsonStr='[';
		$province=M("region")->where('zl_visible=1 and  `zn_fid` =1')->order('zn_sort','id desc')->field("*")->select();
	    foreach ($province as $k => $v){
			$jsonStr.='{ "name": "'.$v["zc_name"].'", "city":[';
			
			
			
				   $city=M("region")->where('zl_visible=1 and zn_fid='.$v["id"])->order('zn_sort','id desc')->field("*")->select();
				   if($city){
					   foreach ($city as $k1 => $v1){
						   
						   $jsonStr.='{"name":"'.$v1["zc_name"].'", "area":[';
						   
				   			$district=M("region")->where('zl_visible=1 and zn_fid='.$v1["id"])->order('zn_sort','id desc')->field("*")->select();
							if($district){
								$temp='item_area';
								foreach ($district as $k2 => $v2){
									 $temp.=',"'.$v2["zc_name"].'"';
								}
							}
							
							if(count($city)==($k1+1)){
								$jsonStr.=$temp.']}';
							}else{
								$jsonStr.=$temp.']},';
							}							
							
							
					   }
				   }
					if(count($province)==($k+1)){
						$jsonStr.=']}';
					}else{
						$jsonStr.=']},';
					}
			
        }	
		$jsonStr.=']';			
			
    */	
	
	public function creat_json(){
	$this->ajaxReturn(array('status'=>0,'msg'=>'当前端口暂时关闭','data' =>array(),"url"=>"","note"=>""),$this->JSONP);
		
	$jsonStr='[';
		$province=M("region")->where('zl_visible=1 and  `zn_fid` =1 ')->order('zn_sort','id desc')->field("*")->select();
	    foreach ($province as $k => $v){
			$jsonStr.='{"'.$v["zc_name"].'": [';
			
				   $city=M("region")->where('zl_visible=1 and zn_fid='.$v["id"])->order('zn_sort','id desc')->field("*")->select();
				   if($city){
					   foreach ($city as $k1 => $v1){
						   
						   $jsonStr.='{"'.$v1["zc_name"].'":[';
						   
				   			$district=M("region")->where('zl_visible=1 and zn_fid='.$v1["id"])->order('zn_sort','id desc')->field("*")->select();
							if($district){
								$temp='region_aaaa';
								foreach ($district as $k2 => $v2){
									 $temp.=',"'.$v2["zc_name"].'"';
								}
							}
							
							if(count($city)==($k1+1)){
							$jsonStr.=$temp.']}';
							}else{
							$jsonStr.=$temp.']},';
							}							
					   }
				   }
			
			if(count($province)==($k+1)){
				$jsonStr.=']}';
			}else{
				$jsonStr.=']},';
			}
			
        }	
		$jsonStr.=']';			
		

	}	
	

	
}