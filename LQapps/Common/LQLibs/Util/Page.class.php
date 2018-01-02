<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// |         lanfengye <zibin_5257@163.com>
// +----------------------------------------------------------------------

namespace LQLibs\Util;
class Page {
    
    // 分页栏每页显示的页数
    public $rollPage = 5;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页URL地址
    public $url     =   '';
    // 默认列表每页显示行数
    public $listRows = 20;
    // 起始行数
    public $firstRow    ;
    // 分页总页面数
    public $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页显示定制
    protected $config  =    array('header'=>'条记录','prev'=>'<','next'=>'>','first'=>'<<','last'=>'>>','theme'=>'%totalRow% %header% %nowPage% %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%');
    // 默认分页变量名
    protected $varPage;

    /**
     * 架构函数
     * @access public
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows,$listRows='',$parameter='',$url='') {
        $this->totalRows    =   $totalRows;
        $this->parameter    =   $parameter;
        $this->varPage      =   C('VAR_PAGE') ? C('VAR_PAGE') : 'p' ;
        if(!empty($listRows)) {
            $this->listRows =   intval($listRows);
        }
        $this->totalPages   =   ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages    =   ceil($this->totalPages/$this->rollPage);
        $this->nowPage      =   !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
        if($this->nowPage<1){
            $this->nowPage  =   1;
        }elseif(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage  =   $this->totalPages;
        }
        $this->firstRow     =   $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     * 分页显示输出
     * @access public
     */
    public function show() {
        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);

        // 分析分页参数
        if($this->url){
            $depr       =   C('URL_PATHINFO_DEPR');
            $url        =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__PAGE__';
        }else{
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                unset($_GET[C('VAR_URL_PARAMS')]);
                $var =  !empty($_POST)?$_POST:$_GET;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
            }
            $parameter[$p]  =   '__PAGE__';
            $url            =   U('',$parameter);
        }
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =   "<a href='".str_replace('__PAGE__',$upRow,$url)."'>".$this->config['prev']."</a>";
        }else{
            $upPage     =   '';
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<a href='".str_replace('__PAGE__',$downRow,$url)."'>".$this->config['next']."</a>";
        }else{
            $downPage   =   '';
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst   =   '';
            $prePage    =   '';
        }else{
            $preRow     =   $this->nowPage-$this->rollPage;
            $prePage    =   "<a href='".str_replace('__PAGE__',$preRow,$url)."' ><</a>";
            $theFirst   =   "<a href='".str_replace('__PAGE__',1,$url)."' >".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   '';
            $theEnd     =   '';
        }else{
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $nextPage   =   "<a href='".str_replace('__PAGE__',$nextRow,$url)."' >></a>";
            $theEnd     =   "<a href='".str_replace('__PAGE__',$theEndRow,$url)."' >".$this->config['last']."</a>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "&nbsp;<a href='".str_replace('__PAGE__',$page,$url)."'>&nbsp;".$page."&nbsp;</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "&nbsp;<a class='active'>".$page."</a>";
                }
            }
        }
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array('','','','',$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }


    /**
     * 分页显示输出 --- 后台
     * @access public
     */
    public function admin_show() {
        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);

        // 分析分页参数
		$parameter      =   $this->parameter;
		$parameter[$p]  =   '__PAGE__';
		$url            =   U('',$parameter);		
		
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =   "<li class='paginate_button previous'><a href='".str_replace('__PAGE__',$upRow,$url)."'>".$this->config['prev']."</a></li>";
        }else{
            $upPage     =   "<li class='paginate_button previous disabled'><a href=\"javascript:;\">".$this->config['prev']."</a></li>";
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<li class='paginate_button next'><a href='".str_replace('__PAGE__',$downRow,$url)."'>".$this->config['next']."</a></li>";
        }else{
            $downPage   =   "<li class='paginate_button next disabled'><a href=\"javascript:;\">".$this->config['next']."</a></li>";
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst   =   '';
            $prePage    =   '';
        }else{
            $preRow     =   $this->nowPage-$this->rollPage;
            $prePage    =   "<li class='paginate_button previous'><a href='".str_replace('__PAGE__',$preRow,$url)."' >上".$this->rollPage."页</a></li>";
            $theFirst   =   "<li class='paginate_button previous'><a href='".str_replace('__PAGE__',1,$url)."' >".$this->config['first']."</a></li>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   '';
            $theEnd     =   '';
        }else{
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $nextPage   =   "<li class='paginate_button next'><a href='".str_replace('__PAGE__',$nextRow,$url)."' >下".$this->rollPage."页</a></li>";
            $theEnd     =   "<li class='paginate_button next'><a href='".str_replace('__PAGE__',$theEndRow,$url)."' >".$this->config['last']."</a></li>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<li class='paginate_button'><a href='".str_replace('__PAGE__',$page,$url)."'>".$page."</a></li>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "<li class='paginate_button active'><a href=\"javascript:;\">".$page."</a></li>";
                }
            }
        }
		
		
		$admin_theme = '<div class="col-sm-6"><div class="data_info">共 <i class="label label-danger">%totalRow%</i>  %header% , <i class="label label-danger">%nowPage%/%totalPage%</i> 页 </div></div><div class="col-sm-6"><ul class="pagination">%upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end% </ul></div>';
		
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$admin_theme);
		
		$pageStr_header='<div class="row theone_page">';
		$pageStr_footer='</div>';
		
        return $pageStr_header.$pageStr.$pageStr_footer;
    }
	
	
    /**
     * 分页显示输出----窗体
     * @access public
     */
    public function window_show() {
        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);

        // 分析分页参数
		$parameter      =   $this->parameter;
		$parameter[$p]  =   '__PAGE__';
		$url            =   U('',$parameter);		
		
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if($nowCoolPage == 1){
            $theFirst   =   "<div class='page_turning'><a href=\"javascript:;\">".$this->config['first']."</a></div>";
        }else{
            $preRow     =   $this->nowPage-$this->rollPage;
            $theFirst   =   "<div class='page_turning'><a href='".str_replace('__PAGE__',1,$url)."' >".$this->config['first']."</a></li>";
        }
        if($nowCoolPage == $this->coolPages){
            $theEnd   =   "<div class='page_turning'><a href=\"javascript:;\">".$this->config['last']."</a></div>";
        }else{
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $theEnd     =   "<div class='page_turning'><a href='".str_replace('__PAGE__',$theEndRow,$url)."' >".$this->config['last']."</a></li>";
        }		
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<li><a href='".str_replace('__PAGE__',$page,$url)."'>".$page."</a></li>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "<li class='focus'><a href=\"javascript:;\">".$page."</a></li>";
                }
            }
        }
		
		$window_theme = '<div class="page_num_btn_info">共 <span class="digital">%totalRow%</span>  %header% , <span class="digital">%nowPage%/%totalPage%</span> 页 </div> %first%  <div class="num_btn_list"><ul>%linkPage%</ul></div>  %end% ';
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%first%','%linkPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$theFirst,$linkPage,$theEnd),$window_theme);
		
		$pageStr_header='<div class="page">';
		$pageStr_footer='</div>';
        return $pageStr_header.$pageStr.$pageStr_footer;
    }	

    /**
     * 分页显示输出
     * @access public
     */
    public function phone_show() {
        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);

        // 分析分页参数
        if($this->url){
            $depr       =   C('URL_PATHINFO_DEPR');
            $url        =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__PAGE__';
        }else{
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                unset($_GET[C('VAR_URL_PARAMS')]);
                $var =  !empty($_POST)?$_POST:$_GET;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
            }
            $parameter[$p]  =   '__PAGE__';
            $url            =   U('',$parameter);
        }
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =   "<li><a href='".str_replace('__PAGE__',$upRow,$url)."'>上一页</a></li>";
        }else{
            $upPage     =   "<li><a href=\"javascript:;\">上一页</a></li>";
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<li><a href='".str_replace('__PAGE__',$downRow,$url)."'>下一页</a></li>";
        }else{
            $downPage   =   "<li><a href=\"javascript:;\">下一页</a></li>";
        }
        $theFirst   =   "<li><a href='".str_replace('__PAGE__',1,$url)."' >首页</a></li>";
        $theEnd     =   "<li><a href='".str_replace('__PAGE__',$this->totalPages,$url)."' >末页</a></li>";
        
        // 1 2 3 4 5
        $linkPage = '<li class="xifenye" id="xifenye"><a id="xiye">'.$this->nowPage.'</a>/<a id="mo">'.$this->totalPages.'</a><div class="xab" id="xab" style="display:none"><ul id="uljia">';

        if( $this->nowPage>($this->totalPages-$this->rollPage) ){
            $page_up_num       =   ($nowCoolPage-1)*$this->rollPage;
            $linkPage .= '<li><a href="'.str_replace('__PAGE__',$page_up_num,$url).'"><-'.$page_up_num.'</a></li>';
        }   
        for($i=1;$i<=$this->rollPage;$i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                   $linkPage .= '<li><a href="'.str_replace('__PAGE__',$page,$url).'">'.$page.'</a></li>';
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= '<li><a href="javascript:;">'.$page.'</a></li>';
                }
            }
        }
        if( $this->nowPage<=($this->totalPages-$this->rollPage) ){
            $page_down_num       =   ($nowCoolPage-1)*$this->rollPage+$i;
            $linkPage .= '<li><a href="'.str_replace('__PAGE__',$page_down_num,$url).'">'.$page_down_num.'-></a></li>';
        }
    
        
        $linkPage .= '</ul></div></li>';
        
        $admin_theme = ' <div class="wap_page"> <ul> %first% %upPage% %linkPage%  %downPage% %end% </ul> </div>';
        
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$admin_theme);
        
        $pageStr_header='<div class="wap_page_box">';
        if( $this->totalPages==1 ){
        $pageStr_footer='</div><div class="fn-clear"></div> ';      
        }else{
        $pageStr_footer='</div><script>
        $("#xifenye").click(function(a){
            $("#xab").toggle();
        })
        </script><div class="fn-clear"></div> ';
        }
        
        return $pageStr_header.$pageStr.$pageStr_footer;
    }





}