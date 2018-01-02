<?php
/*表单填充类 (bootstrap样式)
开发日期始于：2016-11-18
作者:国雾院theone  438675036@qq.com
*/
namespace LQLibs\Util;
class BootstrapForm
{
    // html字符串
    protected $html = "";
    // 字段信息
    protected $fields = array();//1、控件类型;2、控件名(ID);3、标识是否作字段值提交（LQF注册）;4、控件标签（Attribute）
    // 自动完成（数据值）
    protected $data = array();
    // 字段注释
    protected $comment = array();

    protected $token = '';

    //魔术方法__call  
    /* 
	$method 获得方法名 
	$arg 获得方法的参数集合 
	*/
    public function __call($method, $arg)
    {
        return '<div class="form-group"><label class="col-xs-12 col-sm-3 col-md-2 control-label"><i class="fa fa-bullhorn"></i> 出错：</label><div class="col-sm-6 col-md-8 col-xs-12"><span class="help-block"><label class="label label-danger">方法' . $method . '不存在</label></span></div></div>';
    }

    /** 初始化**/
    public function __construct($fields, $data, $comment)
    {
        $this->html = '';
        $this->fields = $fields;
        $this->data = $data;
        $this->comment = $comment;
        //构建授权机制
        if (MODULE_NAME == 'Admin') {
            $auth = array(
                'id' => session('admin_auth')["id"],
                'zc_account' => session('admin_auth')["zc_account"],
                'zn_last_login_time' => session('admin_auth')["zn_last_login_time"],
            );
        } else {
            $auth = array(
                'id' => session('member_auth')["id"],
                'zc_account' => session('member_auth')["zc_account"],
                'zl_role' => session('member_auth')["zl_role"],
                'zn_last_login_time' => session('member_auth')["zn_last_login_time"],
            );
        }
        $this->token = lq_data_auth_sign($auth);
    }


    //将json转化为数组
    protected function json2arr($str)
    {
        $array = json_decode($str, true);
        if ($array["display"] == 'none') {
            $array["display"] = 0;
        } else {
            $array["display"] = 1;
        }
        return $array;

    }

    //将注释字串转数组
    protected function comment2arr($str)
    {
        return explode("|theone|", $str);
    }

    //获得上传者ID
    protected function get_uid()
    {
        if (MODULE_NAME == 'Admin') {
            return session('admin_auth')["id"];
        } else {
            return session('member_auth')["id"];
        }
    }


    //实现attribute转化为字串
    protected function attribute2string($attribute, $controlTitle)
    {
        $lc_str = '';
        if (!$attribute["controlTitle"]) $attribute["controlTitle"] = $controlTitle;
        if ($attribute["required"]) $lc_str .= ' required="required"';
        $lc_str .= ' controlTitle="' . $attribute["controlTitle"] . '"';
        if ($attribute["dataType"]) $lc_str .= ' dataType="' . $attribute["dataType"] . '"';
        if ($attribute["confirm"]) $lc_str .= ' confirm="' . $attribute["confirm"] . '"';
        if ($attribute["dataLength"]) $lc_str .= ' dataLength="' . $attribute["dataLength"] . '"';
        if ($attribute["dataBetween"]) $lc_str .= ' dataBetween="' . $attribute["dataBetween"] . '"';
        if ($attribute["readonly"]) $lc_str .= ' readonly="readonly"';
        if ($attribute["disabled"]) $lc_str .= ' disabled="disabled"';
        if ($attribute["rows"]) $lc_str .= ' rows="' . $attribute["rows"] . '"';
        if ($attribute["maxl"]) $lc_str .= ' maxlength="' . $attribute["maxl"] . '"';
        if ($attribute["width"]) $lc_str .= ' width="' . $attribute["width"] . '"';
        if ($attribute["height"]) $lc_str .= ' height="' . $attribute["height"] . '"';
        if ($attribute["style"]) $lc_str .= ' style="' . $attribute["style"] . '"';
        return $lc_str;
    }

    //创建星号
    protected function requiredHtml($required = 0)
    {
        if ($required)
            return '<span class="require">*</span>';
        else
            return '';
    }

    //处理控件name
    protected function widgetName($fulldata)
    {
        if ($fulldata[3])
            return ' name="LQF[' . $fulldata[1] . ']"';
        else
            return ' name="' . $fulldata[1] . '"';
    }

    //**********************创建控件集*******************************************************************************************************************
    //创建隐藏控件
    protected function hidden($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        return '<input type="hidden" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' value="' . $value . '">';
    }


    //隐藏控件集
    protected function hiddens($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        return '<input type="hidden" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' value="' . $value . '">';
    }


    /*创建文本域(Text):*/
    protected function text($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        //快速填充
        $fast_fill_html_s = '';
        $fast_fill_html_e = '';
        $fast_fill_html = '';
        $fast_fill_js = '';
        if ($attribute_data["fast-fill"]) {
            $fast_fill_js = '<script type="text/javascript">$("#fast-menu-' . $fulldata[1] . '>li").click(function(){$("#' . $fulldata[1] . '").val($(this).text());});</script>';
            $fast_fill_html_s = '<div class="input-group">';
            $fast_fill_html_e = '</div>';
            $fast_fill_html = '<div class="input-group-btn"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="caret"></span></button> <ul class="dropdown-menu dropdown-menu-right" id="fast-menu-' . $fulldata[1] . '">';
            $fast_fill_data = explode(",", $attribute_data["fast-fill"]);
            foreach ($fast_fill_data as $k => $v) {
                $fast_fill_html .= "<li><a href=\"javascript:;\">" . $v . "</a></li>";
            }
            $fast_fill_html .= '</ul></div>';
        }

        if (substr($fulldata[1], 1, 1) == 'c') {
            return '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-6 col-md-8 col-xs-12">' . $fast_fill_html_s . '<input onkeyup="wordCount(this);" type="text" class="form-control" placeholder="请输入' . $fulldata[2] . '" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' value="' . $value . '"' . $this->attribute2string($attribute_data, $fulldata[2]) . ' >' . $fast_fill_html . $fast_fill_html_e . '<span class="help-block">' . $comment_string . ' ,<span id="word_count_' . $fulldata[1] . '">输入了' . lqAbslength($value) . '个字</span></span></div></div>' . $fast_fill_js;
        } else {
            return '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-6 col-md-8 col-xs-12">
		' . $fast_fill_html_s . '<input type="text" class="form-control" placeholder="请输入' . $fulldata[2] . '" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' value="' . $value . '"' . $this->attribute2string($attribute_data, $fulldata[2]) . ' >' . $fast_fill_html . $fast_fill_html_e . '<span class="help-block">' . $comment_string . '</span></div></div>' . $fast_fill_js;
        }
    }

    //创建密码域
    protected function password($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释		
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        return '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-6 col-md-8 col-xs-12"><input type="password" class="form-control" placeholder="请输入' . $fulldata[2] . '" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' value="' . $value . '"' . $this->attribute2string($attribute_data, $fulldata[2]) . ' ><span class="help-block">' . $comment_string . '</span></div></div>';
    }

    //创建下拉列表
    protected function select($fulldata, $data = '')
    {

        //控件值
        $value = $data[$fulldata[1]];
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释
        if ($comment_string == '') $comment_string = $fulldata[2];
        //attribute数据集
        $attribute_data = $this->json2arr($fulldata[4]);
        $lc_option_str = lqCreatOption($data[$fulldata[1] . "_data"], $value, $attribute_data["please"]);
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        return '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-6 col-md-8 col-xs-12"><select class="form-control tpl-category-parent" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . $this->attribute2string($attribute_data, $fulldata[2]) . '>' . $lc_option_str . '</select><span class="help-block">' . $comment_string . '</span></div></div>';
    }

    //创建单选按钮
    protected function radio($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        $radio_list = $data[$fulldata[1] . "_data"];
        $string = '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-9 col-xs-12">';
        foreach ($radio_list as $k => $v) {
            if ($value == $k) {
                $checked = ' checked="checked"';
            } else {
                $checked = '';
            }
            $string .= '<label class="radio-inline"><input type="radio" id="' . $fulldata[1] . $k . '"' . $this->widgetName($fulldata) . ' value="' . $k . '" ' . $checked . '/>' . $v . '</label>';
        }
        $string .= '<span class="help-block">' . $comment_string . '</span></div></div>';
        return $string;
    }

    //创建复选框
    protected function checkbox($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释		
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        $checkbox_list = $data[$fulldata[1] . "_data"];
        if ($attribute_data["menu"] == 1) {
            $lockstr_script = 'if($(this).prop("checked")){
							lockstr+="|1";
					  }else{
							lockstr+="|0";
					 }';
            $la_value = explode("|", $value);
        } else {
            $lockstr_script = 'if($(this).attr("checked")) lockstr+=","+$(this).val();';
            $la_value = explode(",", $value);
        }

        if ($attribute_data["menu"] == 1) {
            $string = '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><input type="hidden" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' value="' . $value . '" /><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-8 col-xs-12"><label class="checkbox-inline edit-checkbox"><input type="checkbox" id="' . $fulldata[1] . '_choose_all"/>&nbsp;全选：</label>';
        } else {
            $string = '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data) . $fulldata[2] . '</label><div class="col-sm-8 col-xs-12"><label class="checkbox-inline edit-checkbox"><input type="checkbox" id="' . $fulldata[1] . '_choose_all"/>全选：</label>';
        }
        foreach ($checkbox_list as $k => $v) {
            if ($attribute_data["menu"] == 1 && $la_value[$k]) {
                $checked = ' checked="checked"';
            } elseif ($attribute_data["menu"] == 0 && in_array($k, $la_value)) {
                $checked = ' checked="checked"';
            } else {
                $checked = '';
            }
            if ($attribute_data["menu"] == 1) {
                $string .= '<label class="checkbox-inline"><input type="checkbox" id="checkbox_' . $fulldata[1] . $k . '" name="' . $fulldata[1] . '_checkbox" value="' . $k . '" ' . $checked . ' class="' . $fulldata[1] . '_checkbox"/>' . $v . '</label>';
            } else {
                $string .= '<label class="checkbox-inline"><input type="checkbox" id="checkbox_' . $fulldata[1] . $k . '" name="LQF[' . $fulldata[1] . '][]" value="' . $k . '" ' . $checked . ' class="' . $fulldata[1] . '_checkbox"/>' . $v . '</label>';
            }

        }
        $string .= '<span class="help-block">' . $comment_string . '</span></div>';
        if ($attribute_data["menu"] == 1) {
            $string .= '<script>
					function get_' . $fulldata[1] . '_data(){
						var lockstr="";
						$("[name=' . $fulldata[1] . '_checkbox]").each(function(i){
									' . $lockstr_script . '
						});
						$("#' . $fulldata[1] . '").val(lockstr.substr(1));	
					}
					//整合列表锁数据
					$(".' . $fulldata[1] . '_checkbox").click(function(){get_' . $fulldata[1] . '_data();});					
					$("#' . $fulldata[1] . '_choose_all").click(function(){var checked_lockstatus = this.checked;$("input[name=' . $fulldata[1] . '_checkbox]").each(function() {this.checked = checked_lockstatus;});get_' . $fulldata[1] . '_data();
					});		
		</script>';
        } else {
            $string .= '<script>$("#' . $fulldata[1] . '_choose_all").click(function(){var checked_lockstatus = this.checked;$(".' . $fulldata[1] . '_checkbox").each(function() {this.checked = checked_lockstatus;});});</script>';
        }

        return $string . "</div>";
    }

    ////////////////////////////////////////////////////////////////////////////////html控件 start///////////////////////////////////////////////////////////////////
    //创建点击按钮
    function buttonDialog($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $value_label = $data[$fulldata[1] . "_label"];
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释		
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        //窗体目标地址
        if (__APP__ == 'do') {
            $open_url = U($attribute_data["controller"] . "/" . $attribute_data["type"] . "?field=" . $fulldata[1] . "&checkbox=" . $attribute_data["checkbox"]);
        } else {
            $open_url = __APP__ . "/" . $attribute_data["controller"] . "/" . $attribute_data["type"] . "/field/" . $fulldata[1] . "/checkbox/" . $attribute_data["checkbox"];
        }
        if ($value) {
            if (__APP__ == 'do') {
                $open_url .= '&ids=' . base64_encode($value);
            } else {
                $open_url .= '/ids/' . base64_encode($value);
            }
        }
        if ($attribute_data["type"] == 'tree') {
            $w = '420px';
            $h = '425px';
        } else {
            $w = '690px';
            $h = '410px';
        }

        $string = '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><input type="hidden" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' value="' . $value . '" /><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-8 col-xs-12">';
        if ($attribute_data["field"]) {
            $string .= '<input type="hidden" field="' . $attribute_data["field"] . '" id="' . $fulldata[1] . '_ext" value="">';
        }
        $string .= '<div class="input-group"><input type="text" id="' . $fulldata[1] . '_label" name="LQL[' . $fulldata[1] . '_label]" class="form-control" value="' . $value_label . '"' . $this->attribute2string($attribute_data, $fulldata[2]) . '/><span class="input-group-btn"><button class="btn btn-primary" type="button" onclick="open_' . $fulldata[1] . '_' . $attribute_data["type"] . '()">' . L('BUTTON_CLICK_CHOOSE') . '</button></span> </div>
		<span class="help-block">(' . $comment_string . '),如果留空表示该值为0</span></div>';
        $string .= "<script type=\"text/javascript\">
		function open_" . $fulldata[1] . "_" . $attribute_data["type"] . "(){
		require(['layer'], function(){
			layer.open({
			  type: 2,
			  title: '选择" . $fulldata[2] . "',
			  shadeClose: true,
			  shade: 0.8,
			  area: ['$w', '$h'],
			  content: '$open_url'
			}); 		
		});
		}";
        $string .= 'function select_' . $fulldata[1] . '_' . $attribute_data["type"] . '(id,label){ $("#' . $fulldata[1] . '").val(id);$("#' . $fulldata[1] . '_label").val(label); }</script></div>';
        return $string;
    }

    //创建文本域(Textarea)
    protected function textarea($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释		
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        return '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-8 col-xs-12"><textarea onkeyup="wordCount(this);" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' class="form-control"' . $this->attribute2string($attribute_data, $fulldata[2]) . 'placeholder="请输入' . $fulldata[2] . '">' . $value . '</textarea><span class="help-block">' . $comment_string . ' ,<span id="word_count_' . $fulldata[1] . '">输入了' . lqAbslength($value) . '个字</span></span></div></div>';
    }

    //编辑页的说明
    public function editMsg($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释		
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        return '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">' . $fulldata[2] . '</label><div class="col-sm-8 col-xs-12"><textarea class="form-control"' . $this->attribute2string($attribute_data, $fulldata[2]) . '>' . $value . '</textarea></div></div>';
    }

    //编辑器
    protected function editor($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释		
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        $lnWidth = $attribute_data["width"];
        $lnHeight = $attribute_data["height"];
        $tceditorid = $fulldata[1];

        // 加载编辑器的容器
        $string = '';

        if (!defined('TPL_INIT_UEDITOR')) {
            //配置文件
            $string .= '<script type="text/javascript" src="' . REL_ROOT . 'Public/Static/plugins/ueditor/ueditor.config.js"></script>';
            //编辑器源码文件
            $string .= '<script type="text/javascript" src="' . REL_ROOT . 'Public/Static/plugins/ueditor/ueditor.all.min.js"></script>';
            //语言文件
            $string .= '<script type="text/javascript" src="' . REL_ROOT . 'Public/Static/plugins/ueditor/lang/zh-cn/zh-cn.js"></script>';
            define('TPL_INIT_UEDITOR', 1);
        }

        $string .= '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-8 col-xs-12">';
        $string .= '<textarea type="text/plain" id="' . $tceditorid . '" name="' . $attribute_data["ext"] . '[' . $tceditorid . ']" style="width:' . $lnWidth . ';height:' . $lnHeight . ';">' . $value . '</textarea>
<script type="text/javascript">require(["baidueditor","zeroclipboard","bdlang"], function(UE, zcl){var ue_' . $tceditorid . ' = UE.getEditor("' . $tceditorid . '");})</script>';
        $string .= '<span class="help-block">' . $comment_string . '</span></div></div>';


        return $string;

    }


    /*日期(date):*/
    protected function date($fulldata, $data = '')
    {
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释		
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        //控件值
        if ($attribute_data["format"] == 'all') {
            $value = lq_cdate($data[$fulldata[1]], 1);
            $format = 'Y-m-d H:i:s';
            $withtime = "true";
            $mask = "9999-19-39 29:59";
        } else if ($attribute_data["format"] == 'day') {
            $value = lq_cdate($data[$fulldata[1]], 0);
            $format = 'Y-m-d';
            $withtime = "false";
            $mask = "9999-19-39";
        } else {
            $value = lq_cdate_format($data[$fulldata[1]], $attribute_data["format"]);
            $format = $attribute_data["format"];
            $withtime = "true";
            $mask = "9999-19-39 29:59";
        }
        return '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-8 col-xs-12"><div class="input-group input-append date"><input type="text" class="form-control" placeholder="请输入' . $fulldata[2] . '" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' value="' . $value . '"' . $this->attribute2string($attribute_data, $fulldata[2]) . ' ><span class="input-group-addon add-on"><i class="fa fa-calendar"></i></span></div> <span class="help-block">' . $comment_string . '</span></div></div><script type="text/javascript">
			require(["datetimepicker"], function(){
				var ' . $fulldata[1] . '_option = {lang : "zh",step : 5,timepicker : ' . $withtime . ',closeOnDateSelect : true,format : "' . $format . '",mask:"' . $mask . '"};
				$("#' . $fulldata[1] . '").datetimepicker(' . $fulldata[1] . '_option);
			});
		</script>';
    }


    //创建文件上传 image
    protected function image($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $value_label = $data[$fulldata[1] . "_label"];
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释		
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        if (!array_key_exists($attribute_data["type"], C("UPLOAD_EXT"))) {
            return '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-3 col-md-2 control-label"><i class="fa fa-bullhorn"></i> 出错：</label><div class="col-sm-6 col-md-8 col-xs-12"><span class="help-block"><label class="label label-danger">上传目录' . $attribute_data["type"] . '不存在</label></span></div></div>';
        }
        //上传的规则
        $uploadext_label = implode(',', C("UPLOAD_EXT")[$attribute_data["type"]]);
        if ($attribute_data["uploadsize"] == '') {
            $uploadsize_label = byteFormat(C("UPLOAD_MAX_SIZE")[$attribute_data["type"]]);
        } else {
            $uploadsize_label = $attribute_data["uploadsize"] . "KB";
        }
        $img_default = REL_ROOT . "Public/Static/images/upload-pic.png";

        if ($value) {
            $img_src = $value;
        } else {
            $img_src = $img_default;
        }
        if (MODULE_NAME == 'Admin') {
            $lqUrl = U("Attachment/images/s/" . base64_encode("field/" . $fulldata[1] . "/checkbox/0/type/" . $attribute_data["type"] . "/") . "/");
            $lqAction = U(CONTROLLER_NAME . "/opUploadImages?fileid=" . $fulldata[1] . "&type=" . $attribute_data["type"]);
        } else {
            $lqUrl = U(MODULE_NAME . "/Attachment/images?lqs=" . base64_encode("field=" . $fulldata[1] . "=checkbox=0=type=" . $attribute_data["type"] . "="));
            $lqAction = U(CONTROLLER_NAME . "/op_upload_images?fileid=" . $fulldata[1] . "&type=" . $attribute_data["type"]);
        }
        return '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-6 col-md-8 col-xs-12"><div class="input-group">' . $this->hidden($fulldata, $data) . '<input class="form-control images-upload" type="file" name="file_' . $fulldata[1] . '" extend="' . $uploadext_label . '" lqAction="' . $lqAction . '"/><span class="input-group-btn"><button class="btn btn-primary open-images-win" type="button" allowOpen="' . $attribute_data["allowOpen"] . '" lqTitle="图片库" lqUrl="' . $lqUrl . '">选择图片</button></span></div><div class="input-group" style="margin-top:.5em;"> <img src="' . $img_src . '" id="' . $fulldata[1] . '_preview" class="img-responsive img-thumbnail" width="100" height="100" /> </div><span class="help-block">(' . $comment_string . ',附件上传规则格式：' . $uploadext_label . '最大字节' . $uploadsize_label . ')</span></div></div>' . "\n";
    }

    /*图册：多文件上传:*/
    protected function multiImage($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        if ($attribute_data["returnData"] == 'ids') {//ids&pahts
            $attribute_data["returnData"] = "ids";
            $set_value = 'if(myvalue){$("#' . $fulldata[1] . '").val(myvalue+","+ids);}else{$("#' . $fulldata[1] . '").val(ids);}';
        } else {
            $attribute_data["returnData"] = "pahts";
            $set_value = 'if(myvalue){$("#' . $fulldata[1] . '").val(myvalue+","+imagePath);}else{$("#' . $fulldata[1] . '").val(imagePath);}';
        }

        $jsString = '';
        if (!defined('TPL_INIT_KINDEDITOR')) {
            $jsString .= '<script type="text/javascript" src="' . REL_ROOT . 'Public/Static/plugins/lqkindeditor/kindeditor-all-min.js"></script>
<script type="text/javascript" src="' . REL_ROOT . 'Public/Static/plugins/lqkindeditor/lang/zh_CN.js"></script>';
            define('TPL_INIT_KINDEDITOR', 1);
        }
        //上传的规则
        $imageUploadLimit = $attribute_data["imageUploadLimit"];
        if (!$imageUploadLimit) $imageUploadLimit = 5;
        $uploadext = implode(',', C("UPLOAD_EXT")[$attribute_data["type"]]);
        if ($attribute_data["uploadsize"] == '') {
            $uploadsize_label = byteFormat(C("UPLOAD_MAX_SIZE")[$attribute_data["type"]]);
        } else {
            $uploadsize_label = $attribute_data["uploadsize"] . "KB";
        }

        $img_default = REL_ROOT . "Public/Static/images/upload-pic.png";
        $quantity = 0;
        $imageString = '';
        if ($value) {
            $srcArr = explode(",", $value);
            foreach ($srcArr as $path) {
                $quantity++;
                if (is_numeric($path)) {
                    $path = M("attachment")->where("id=" . intval($path))->getField("zc_file_path");
                    if (!$path) $path = $img_default;
                }
                $imageString .= '<span><a href="javascript:void(0);"><i class="fa fa-times"></i></a><img src="' . $path . '" id="' . $fulldata[1] . '_preview_' . $quantity . '" on="1" width="60" height="60" /></span>';
            }
        } else {
            $imageString = '<span><a href="javascript:void(0);"><i class="fa fa-times"></i></a><img src="' . $img_default . '" id="' . $fulldata[1] . '_preview_' . $quantity . '" on="0" class="onsrc"  width="60" height="60" /></span>';
        }


        if (MODULE_NAME == 'Admin') {
            $lqUrl = U("Attachment/images/s/" . base64_encode("field/" . $fulldata[1] . "/checkbox/1/type/" . $attribute_data["type"] . "/returnData/" . $attribute_data["returnData"] . "/") . "/");
            $uploadJson = U(CONTROLLER_NAME . "/opMultiImageUp?uid=" . $this->get_uid() . "&token=" . $this->token . "&fileid=" . $fulldata[1] . "&type=" . $attribute_data["type"]);
        } else {
            $lqUrl = U(MODULE_NAME . "/Attachment/images?lqs=" . base64_encode("field=" . $fulldata[1] . "=checkbox=1=type=" . $attribute_data["type"] . "=returnData=" . $attribute_data["returnData"] . "="));
            $uploadJson = U(CONTROLLER_NAME . "/op_multi_image_up?uid=" . $this->get_uid() . "&token=" . $this->token . "&fileid=" . $fulldata[1] . "&type=" . $attribute_data["type"]);
        }

        $jsString .= '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '>
		 <label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label>
		 <div class="col-sm-6 col-md-8 col-xs-12"><div class="input-group" style="margin-bottom:10px;"><span class="input-group-addon"><i class="fa fa-image" style="color:#f00"></i></span><input type="hidden" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' value="' . $value . '">
		 <input type="button" class="form-control" value="点击批量上传' . $fulldata[2] . '" id="button_' . $fulldata[1] . '" allowOpen="1" lqTitle="多图片上传" lqUrl="' . U("Attachment/multiUpWindow/s/" . base64_encode("field/" . $fulldata[1] . "/type/" . $attribute_data["type"] . "/") . "/") . '">
		 <span class="input-group-btn"><button class="btn btn-primary open-images-win" type="button" allowOpen="' . $attribute_data["allowOpen"] . '" lqTitle="图片库" lqUrl="' . $lqUrl . '">选择图片</button></span></div>
		 <div id="' . $fulldata[1] . '_list" class="album_list">' . $imageString . '</div>
		 <span class="help-block">' . $comment_string . ",文件格式" . $uploadext . ',单个文件最大' . $uploadsize_label . ',最多可以上传' . $attribute_data["imageUploadLimit"] . '个文件</span>
		 </div></div><script type="text/javascript">
		 KindEditor.ready(function(K){
			K.create();
			var ' . $fulldata[1] . '_editor = K.editor({uploadJson:"' . $uploadJson . '",imageUploadLimit:' . $imageUploadLimit . ',imageSizeLimit:"' . $uploadsize_label . '"});
			K("#button_' . $fulldata[1] . '").click(function () {
				var quantity=$("#' . $fulldata[1] . '_list",parent.document).find(".img-thumbnail").size();
				var onsrc=$("#' . $fulldata[1] . '_list",parent.document).find(".onsrc").size();
				if(onsrc){
					quantity=0;
				}else{
					quantity=parseInt(quantity);
				}				
				
				var srcString="",ids="",imagePath="";
				' . $fulldata[1] . '_editor.loadPlugin("multiimage", function () {
					' . $fulldata[1] . '_editor.plugin.multiImageDialog({
					clickFn: function (urlList) {
						K.each(urlList,function(i,data){';
        $jsString .= "srcString+='<span><a href=\"javascript:void(0);\"><i class=\"fa fa-times\"></i></a><img src=\"'+data.url+'\" id=\"" . $fulldata[1] . "_preview_'+(i+quantity+1)+'\" on=\"0\" width=\"60\" height=\"60\" /></span>';
						if(i==0){
						ids=data.id;
						imagePath=data.url;
						}else{
						ids+=','+data.id;
						imagePath+=','+data.url;
						}		 
		 ";

        $jsString .= '
							});
						var myvalue=$("#' . $fulldata[1] . '").val();
						' . $set_value . '
						if(onsrc){
							$("#' . $fulldata[1] . '_list").html(srcString);
						}else{
							$("#' . $fulldata[1] . '_list").append(srcString);
						}							
						' . $fulldata[1] . '_editor.hideDialog();	
						}
					});
				});
			});
		});
		 $(".form-group").on("click","#' . $fulldata[1] . '_list a",function(){
			  var myobj=$(this).parent();
			  var myindex = $("#' . $fulldata[1] . '_list a").index(this);
			  var myvalue=$("#' . $fulldata[1] . '").val();
			  var myArr = myvalue.split(",");//字符串转化为数组
			  myArr.splice(myindex,1);
			  $("#' . $fulldata[1] . '").val(myArr.join(","));
			  myobj.remove();
		 });
	 	 </script>' . "\n\n\n\n\n\n";
        return $jsString;
    }

    //地区联动选择
    protected function selectRegion($fulldata, $data = '')
    {
        //控件值
        $value_array = explode("|", $fulldata[1]);
        $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        $string = '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-8 col-lg-10 col-xs-12">';
        foreach ($value_array as $key => $value) {
            $commentArr = $this->comment2arr($this->comment[$value]);
            if ($attribute_data["required"]) {
                $required = ' required="required"';
            } else {
                $required = '';
            }
            $lc_option_str = lqCreatOption($data[$value . "_data"], $data[$value], $attribute_data["please"] . "-" . $commentArr[0]);

            $string .= "<div class=\"col-lg-3\" style=\"padding-left:0px;\"><select lqkey='" . $key . "' nextSelect=\"" . $value_array[$key + 1] . "\" class=\"form-control tpl-category-parent\" id=\"" . $value . "\" name=\"LQF[" . $value . "]\"" . $required . " controlTitle=\"" . $attribute_data["please"] . "-" . $commentArr[0] . "\" onchange=\"util.loadRegion('" . U("jsLoadRegion") . "','$value','" . $attribute_data["label"] . "');\">" . $lc_option_str . "</select></div>";
        }

        if ($attribute_data["label"]) {
            $attr_label = $attribute_data["label"];
            $value_label = $data[$attr_label];
            $string .= '<input type="hidden" id="' . $attr_label . '" name="LQF[' . $attr_label . ']" value="' . $value_label . '">';
        }
        $string .= '</div></div>';
        return $string;
    }

    //创建关联列表信息
    function linkMessage($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $value_label = $data[$fulldata[1] . "_label"];
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        //窗体目标地址
        if (__APP__ == 'do') {
            $open_url = U($attribute_data["controller"] . "/" . $attribute_data["type"] . "?field=" . $fulldata[1] . "&checkbox=" . $attribute_data["checkbox"]);
        } else {
            $open_url = __APP__ . "/" . $attribute_data["controller"] . "/" . $attribute_data["type"] . "/field/" . $fulldata[1] . "/checkbox/" . $attribute_data["checkbox"];
        }
        if ($value) {
            if (__APP__ == 'do') {
                $open_url .= '&ids=' . base64_encode($value);
            } else {
                $open_url .= '/ids/' . base64_encode($value);
            }
        }
        if ($attribute_data["type"] == 'tree') {
            $w = '420px';
            $h = '425px';
        } else {
            $w = '690px';
            $h = '410px';
        }

        $string = '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><input type="hidden" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' value="' . $value . '" /><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-8 col-xs-12">';
        if ($attribute_data["field"]) {
            $string .= '<input type="hidden" field="' . $attribute_data["field"] . '" id="' . $fulldata[1] . '_ext" value="">';
        }
        $string .= '<div class="input-group"><input type="text" id="' . $fulldata[1] . '_label" name="LQL[' . $fulldata[1] . '_label]" class="form-control" value="' . $value_label . '"' . $this->attribute2string($attribute_data, $fulldata[2]) . '/><span class="input-group-btn"><button class="btn btn-primary" type="button" onclick="open_' . $fulldata[1] . '_' . $attribute_data["type"] . '()">' . L('BUTTON_CLICK_CHOOSE') . '</button></span> </div>
		<span class="help-block">(' . $comment_string . '),如果留空表示该值为0</span></div>';
        $string .= "<script type=\"text/javascript\">
		function open_" . $fulldata[1] . "_" . $attribute_data["type"] . "(){
		require(['layer'], function(){
			layer.open({
			  type: 2,
			  title: '选择" . $fulldata[2] . "',
			  shadeClose: true,
			  shade: 0.8,
			  area: ['$w', '$h'],
			  content: '$open_url'
			});
		});
		}";
        $string .= 'function select_' . $fulldata[1] . '_' . $attribute_data["type"] . '(id,label){ $("#' . $fulldata[1] . '").val(id);$("#' . $fulldata[1] . '_label").val(label); }</script></div>';
        return $string;
    }

    /*颜色吸取值控件(Text):*/
    protected function color($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        return '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '>
		 <label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label>
		 <div class="col-sm-6 col-md-8 col-xs-12"><div class="input-group"><span class="input-group-addon"><i id="i-' . $fulldata[1] . '" class="fa fa-circle" style="color:' . $value . '"></i></span><input maxlength="7" type="text" class="form-control" placeholder="请输入' . $fulldata[2] . '" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' value="' . $value . '"' . $this->attribute2string($attribute_data, $fulldata[2]) . ' ></div>
		<span class="help-block">' . $comment_string . '</span></div></div>
		<script type="text/javascript">
			require(["colorpicker"], function(){
				$("#' . $fulldata[1] . '").colorpicker().on("changeColor", function (ev) {
					$("#i-' . $fulldata[1] . '").css("color", ev.color.toHex());
				});		
			});		
		</script>';
    }


    /*创建文本域(展示):*/
    protected function textShow($fulldata, $data = '')
    {
        $value = $label = $data[$fulldata[1]];//控件值
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }
        if ($attribute_data["is_data"] == 1) $label = $data[$fulldata[1] . "_data"][$value];
        $hidden_string = '';
        if ($attribute_data["creat_hidden"] == 1) $hidden_string = '<input type="hidden" id="' . $fulldata[1] . '"' . $this->widgetName($fulldata) . ' value="' . $value . '">';
        return '<div class="form-group has-warning" id="form_group_' . $fulldata[1] . '"><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . $hidden_string . '</label><div class="col-sm-6 col-md-8 col-xs-12"><input readonly="readonly" type="text" class="form-control" placeholder="请输入' . $fulldata[2] . '" id="label_' . $fulldata[1] . '" value="' . $label . '"' . $this->attribute2string($attribute_data, $fulldata[2]) . ' ><span class="help-block">' . $comment_string . '</span></div></div>';
    }

    /*创建单图展示:*/
    protected function imageShow($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        $comment_string = $attribute_data["tip"];//控件注释
        if ($comment_string == '') $comment_string = $fulldata[2];
        if (!$value) $value = NO_PICTURE_ADMIN;

        return '<div class="form-group has-warning" id="form_group_' . $fulldata[1] . '"><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-6 col-md-8 col-xs-12"><div class="input-group" style="margin-top:.5em;"> <img src="' . $value . '" id="' . $fulldata[1] . '_preview" class="img-responsive img-thumbnail" width="100" height="100" /> </div>
<span class="help-block">(' . $comment_string . ')</span></div></div>';
    }


    /*进度条(Text):*/
    protected function progress_bar($fulldata, $data = '')
    {
        $value = intval($data[$fulldata[1]]);//控件值
        $list = $data[$fulldata[1] . "_data"];
        $list_count = count($list);
        $progress_string = '';
        $progress_width = round((100 / $list_count), 2);

        if ($list_count == $value) {
            foreach ($list as $k => $v) {
                $progress_string .= '<div class="progress-bar progress-bar-success" style="text-align:left;width:' . $progress_width . '%" title="' . $k . ":" . $v . '">' . $v . '</div>';
            }
        } else {
            foreach ($list as $k => $v) {
                if ($k == ($value + 1)) {
                    $progress_string .= '<div class="progress-bar progress-bar-danger" style="text-align:left;width:' . $progress_width . '%" title="' . $k . ":" . $v . '">' . $v . '</div>';
                } else if ($k <= $value) {
                    $progress_string .= '<div class="progress-bar progress-bar-success" style="text-align:left;width:' . $progress_width . '%" title="' . $k . ":" . $v . '">' . $v . '</div>';
                } else {
                    $progress_string .= '<div class="progress-bar progress-bar-hui" style="text-align:left;width:' . $progress_width . '%" title="' . $k . ":" . $v . '">' . $v . '</div>';
                }
            }
        }
        return '<div class="form-group" id="form_group_' . $fulldata[1] . '"' . $form_group_display . '><label class="col-xs-12 col-sm-3 col-md-2 control-label">' . $this->requiredHtml($attribute_data["required"]) . $fulldata[2] . '</label><div class="col-sm-6 col-md-8 col-xs-12"><div class="progress" style="margin-top:8px;">' . $progress_string . '</div></div></div>';
    }


    //时间轴
    protected function timeline($fulldata, $data = '')
    {
        $value = $data[$fulldata[1]];//控件值
        $list = $data[$fulldata[1] . "_data"];
        $commentArr = $this->comment2arr($this->comment[$fulldata[1]]);
        $comment_string = $commentArr[0];//控件注释		
        if ($comment_string == '') $comment_string = $fulldata[2];
        $attribute_data = $this->json2arr($fulldata[4]);//attribute数据集
        if ($attribute_data["display"] == 1) {
            $form_group_display = ' style="display: block;"';
        } else {
            $form_group_display = ' style="display: none;"';
        }

        $timeline_str = '';
        foreach ($list as $k => $v) {
            if (($k + 1) % 2 == 0) {
                $licss = ' class="timeline-inverted"';
            } else {
                $licss = '';
            }
            if ($v["status"] == 1) {
                $status_css = ' success';
            } else {
                $status_css = '';
            }
            $timeline_str .= '<li' . $licss . '><div class="timeline-badge' . $status_css . '"> <i class=" icon-pencil">' . $v["no"] . '</i> </div>
					  <div class="timeline-panel">
						<div class="timeline-heading"><h4 class="timeline-title">' . $v["title"] . '</h4><p> <small class="text-muted">' . $v["time"] . '</small> </p>
						</div>
						<div class="timeline-body"><p>' . $v["msg"] . '</p></div>
					  </div>
					</li>';
        }

        return '<ul class="timeline">' . $timeline_str . '</ul>';
    }


    //创建tab_title
    protected function creatTabTitle($tatitle = array())
    {
        if (is_array($tatitle) && $tatitle) {
            $titles = '';
            foreach ($tatitle as $lnKey => $laValue) {
                if ($lnKey == 1)
                    $titles .= '<li class="active"><a href="#formTab' . $lnKey . '" data-toggle="tab">' . $laValue . '</a></li>';
                else
                    $titles .= '<li><a href="#formTab' . $lnKey . '" data-toggle="tab">' . $laValue . '</a></li>';
            }
            return '<ul id="LQFormTab" class="nav nav-tabs">' . $titles . '</ul>';
        } else {
            return '';
        }
    }

    //创建HTML
    public function createHtml()
    {
        $lneditor = 0;

        //头部
        $this->html = '<div class="panel-body"><form name="LQForm" id="LQForm" uid="' . $this->get_uid() . '" token="' . $this->token . '" method="post" target="hidden_frame" enctype="multipart/form-data" class="form-horizontal form">';
        //标题
        $this->html .= $this->creatTabTitle($this->fields["tab_title"]);

        //内容块
        $this->html .= '<div id="LQFormTabContent" class="tab-content">' . "\n";
        foreach ($this->fields["tab_title"] as $k => $v) {
            if ($k == 1) {
                $this->html .= '<div class="tab-pane in active" id="formTab' . $k . '">' . "\n";
            } else {
                $this->html .= '<div class="tab-pane" id="formTab' . $k . '">' . "\n";
            }
            //加插控件s*****************
            foreach ($this->fields[$k] as $lnKey => $laValue) {
                $this->html .= $this->$laValue[0]($laValue, $this->data);
            }
            //加插控件e*****************
            $this->html .= "</div>\n";
        }
        $this->html .= '</div>' . "\n";

        //按钮
        $this->html .= '<div class="form-group"><div class="col-sm-12"><input id="LQFormSubmit" type="button" value="提交" class="btn btn-primary col-lg-1"><div class="btn-group pull-right">' . $this->data["os_record_time"] . '</div></div></div>';

        //尾部
        $this->html .= "<input type=\"hidden\" name=\"LQF[id]\" value=\"" . $this->data["id"] . "\" my_controller_name=\"" . CONTROLLER_NAME . "\"/><input type=\"hidden\" id=\"my_controller_name\" value=\"" . CONTROLLER_NAME . "\"/><input type=\"hidden\" id=\"my_module\" value=\"" . MODULE_NAME . "\"/></form>\n</div>\n<iframe style=\"display:none\" name=\"hidden_frame\" id=\"hidden_frame\"></iframe>";

        //返回表单 html
        return $this->html;
    }

}
