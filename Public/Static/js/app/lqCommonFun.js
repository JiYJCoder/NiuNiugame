/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:公共函数库
*/

/*
* Interfaces:
* b64 = base64encode(data);
* data = base64decode(b64);
*/
var base64EncodeChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
var base64DecodeChars = new Array(
     -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
     -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
     -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63,
     52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1,
     -1,   0,   1,   2,   3,   4,   5,   6,   7,   8,   9, 10, 11, 12, 13, 14,
     15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1,
     -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
     41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1);

function base64encode(str) {
     var out, i, len;
     var c1, c2, c3;

     len = str.length;
     i = 0;
     out = "";
     while(i < len) {
         c1 = str.charCodeAt(i++) & 0xff;
         if(i == len)
         {
             out += base64EncodeChars.charAt(c1 >> 2);
             out += base64EncodeChars.charAt((c1 & 0x3) << 4);
             out += "==";
             break;
         }
         c2 = str.charCodeAt(i++);
         if(i == len)
         {
             out += base64EncodeChars.charAt(c1 >> 2);
             out += base64EncodeChars.charAt(((c1 & 0x3)<< 4) | ((c2 & 0xF0) >> 4));
             out += base64EncodeChars.charAt((c2 & 0xF) << 2);
             out += "=";
             break;
         }
         c3 = str.charCodeAt(i++);
         out += base64EncodeChars.charAt(c1 >> 2);
         out += base64EncodeChars.charAt(((c1 & 0x3)<< 4) | ((c2 & 0xF0) >> 4));
         out += base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >>6));
         out += base64EncodeChars.charAt(c3 & 0x3F);
     }
     return out;
}
function base64decode(str) {
     var c1, c2, c3, c4;
     var i, len, out;

     len = str.length;
     i = 0;
     out = "";
     while(i < len) {
         /* c1 */
         do {
             c1 = base64DecodeChars[str.charCodeAt(i++) & 0xff];
         } while(i < len && c1 == -1);
         if(c1 == -1)
             break;

         /* c2 */
         do {
             c2 = base64DecodeChars[str.charCodeAt(i++) & 0xff];
         } while(i < len && c2 == -1);
         if(c2 == -1)
             break;

         out += String.fromCharCode((c1 << 2) | ((c2 & 0x30) >> 4));

         /* c3 */
         do {
             c3 = str.charCodeAt(i++) & 0xff;
             if(c3 == 61)
                 return out;
             c3 = base64DecodeChars[c3];
         } while(i < len && c3 == -1);
         if(c3 == -1)
             break;

         out += String.fromCharCode(((c2 & 0XF) << 4) | ((c3 & 0x3C) >> 2));

         /* c4 */
         do {
             c4 = str.charCodeAt(i++) & 0xff;
             if(c4 == 61)
                 return out;
             c4 = base64DecodeChars[c4];
         } while(i < len && c4 == -1);
         if(c4 == -1)
             break;
         out += String.fromCharCode(((c3 & 0x03) << 6) | c4);
     }
     return out;
}
//数组操作
Array.prototype.indexOf = function(val) {
for (var i = 0; i < this.length; i++) {
if (this[i] == val) return i;
}
return -1;
};
Array.prototype.remove = function(val) {
var index = this.indexOf(val);
if (index > -1) {
this.splice(index, 1);
}
};

//对查询的关键字处理
function lqKeywordOnclick(toobj){if(toobj.value =='请输入关键字...'){toobj.value=''}}
function lqKeywordOnblur(toobj){if(toobj.value ==''){toobj.value='请输入关键字...'}}
//树状展示操作
var opTreeDisplay=function(cid,op){
	if(op=="show"){
		$("tr[pid='"+cid+"']").each(function(){$(this).removeAttr("status").show();opTreeDisplay($(this).attr("id"),"show");});
	}else{
		$("tr[pid='"+cid+"']").each(function(){$(this).attr("status",1).hide();opTreeDisplay($(this).attr("id"),"hide");
		});
	}
}
function lqTreeDisplay(obj){
		if(obj.attr("status")!=1){opTreeDisplay(obj.parent().attr("id"),"hide");obj.attr("status",1);
		}else{opTreeDisplay(obj.parent().attr("id"),"show");obj.removeAttr("status");}	
}
var php_url_model=1;//URL访问模式
var LQ_DOMAIN="http://"+window.location.host+"/"; //域名
var LQ = {
	EDITOR: LQ_DOMAIN+"sys-index.php/##/opUploadEditor/uid/###/token/####",
};

(function(window) {
	var util = {};
	var lq_wait_time=2; //页面等待时间
	var lq_value='';//传递值，过渡值
	var lq_current_url=document.URL;//当前URL
	var lq_post_form='LQForm';//通用提交表单ID
	var lq_list_form='LQFormList';//通用列表表单ID
	var cookie_seconds = 90000;//cookie保存的时间
	var page_loading,obj,url;
	
	//系统菜单的展示
	util.menuDisplay = function(topid,leftid){
		$("#left-menu").find(".left-menu").css({"display":"none"});
		$("#top-key-"+topid).addClass("active");
		$("#left-menu-"+topid).css({"display":"block"});
		$("#left-key-"+leftid).addClass("active");
	}
	
	//页面跳转处理
	util.R = function(secs,url){ 
		if(--secs>0){setTimeout("util.R("+secs+"\,'"+url+"');",1000); 
		}else{if(url=='refresh'|url=='ref'){window.location.reload();}else{location.href=url;}}
	};
	
	/*
	弹出信息框：
	status:1-成功,0-失败,2-未知错误或系统错误
	msg:信息
	url:跳转页面、refresh|ref 刷新当前页
	wait:跳转等待时间
	*/
	util.sysMsg = function(status,msg,url,wait){
		if(status==1){var icon=1;}else if(status==0){var icon=2;}else if(status==4){var icon=4;}else{var icon=3;}
		if(typeof msg=='undefined'|msg==''){msg='系统提示！';}else{msg='系统提示：'+msg;}
		if (typeof wait=='undefined') wait= lq_wait_time;
		require(['layer'], function(){layer.msg(msg,{icon:icon,time:wait*1000});});
		if (typeof url!='undefined') util.R(wait,url);
	};

	/*
	打开窗体：
	t:标题
	w:宽度
	h:高度
	*/
	util.openWin = function(u,t,w,h){
		if(typeof u=='undefined'|u=='') return false;
		if(typeof t=='undefined'|t=='') t='窗口';
		if(typeof w=='undefined'|w=='') w='320px';
		if(typeof h=='undefined'|h=='') h='160px';
		require(['layer'], function(){
			layer.open({type: 2,title: t,shadeClose: true,shade: 0.8,area: [w,h],content:u});			
		});
	};
	
	//设定或返回sha1
	util.Rsha1 = function(str,obj){
        if (typeof str == 'string' && str.length > 0) {
			require(['sha1'], function(){lq_value = $.sha1(str);if (typeof obj=='object'){obj.val(lq_value);return true;}});
		}
		return lq_value;
	};	
	
	//全选/反选
	util.checked = function(obj){
		var checked_status = obj.checked;
		if(checked_status){$("#os_warning").css({"display":"inline-block"});}else{$("#os_warning").css({"display":"none"});}
		$("input[type=checkbox]").each(function() {this.checked = checked_status;});
	};
	//批量删除
	util.deletelCheckbox = function(){
		var itemsTxt='';
		require(['layer'], function(){
			layer.confirm('确认要批量删除吗?',function(){
				$("[name=items]:checked").each(function(){itemsTxt += ","+$(this).val();}) ;
				if(itemsTxt){
					    page_loading=layer.load(0,{shade:[0.1,'#fff']});
						itemsTxt=itemsTxt.substr(1);
						var thinkphpurl=$("#thinkphpurl").val()+"opDeleteCheckbox";	
						$.getJSON(thinkphpurl,{tcid:itemsTxt}, function(json){
							layer.close(page_loading);
							util.sysMsg(json.status,json.msg,json.url);
						});					
				}else{
					layer.msg("请先选择项目,谢谢!",{icon:5,time:lq_wait_time*1000});
			    }
			});
		});
	};
	//批量审批
	util.visibleCheckbox = function(state){
		var itemsTxt='';
		require(['layer'], function(){
				$("[name=items]:checked").each(function(){itemsTxt += ","+$(this).val();}) ;
				if(itemsTxt){
						page_loading=layer.load(0,{shade:[0.1,'#fff']});
						itemsTxt=itemsTxt.substr(1);
						var thinkphpurl=$("#thinkphpurl").val()+"opVisibleCheckbox";	
						$.getJSON(thinkphpurl, {status:state,tcid:itemsTxt}, function(json){
							layer.close(page_loading);
							util.sysMsg(json.status,json.msg,json.url);
						});					
				}else{
					layer.msg("请先选择项目,谢谢!",{icon:5,time:lq_wait_time*1000});
			    }
		});
	};
	//单项审批
	util.visible = function(obj,url){
		require(['layer'], function(){
			page_loading = layer.load(0,{shade:[0.1,'#fff']});
                    var id=obj.parents("tr").attr("id"),status=obj.attr("val");
                    $.getJSON(url,{tnid:id,status:status}, function(json){
						layer.close(page_loading);
                        if(json.status==1){
							obj.parents("tr").attr("visible",json.data.status);
							util.sysMsg(1,json.msg);
							if(json.url&&json.url!=''){util.R(lq_wait_time,json.url);}else{obj.attr("val",json.data.status).html(json.data.status==0? '<i class="fa fa-check-square ac_grey"></i>' : '<i class="fa fa-minus-square"></i>' );
							obj.parents("td").prev().html(json.data.visible_label);
							}
                        }else{layer.msg(json.msg,{icon:2,time:lq_wait_time*1000});}
                    });				
		});
	};	
	//单项删除
	util.delete = function(obj,url){
		if(obj.parents("tr").attr("visible")==1){
			 util.sysMsg(0,"该记录正处于使用状态，不能删除，若要删除请先‘禁用’操作。");
			 return false;
		}
		require(['layer'], function(){
			var id=obj.parents("tr").attr("id"),status=obj.attr("val");
			if(obj.attr("title")){
				confirmStr="确认要"+obj.attr("title");
			}else{
				confirmStr='确认要删除ID['+id+']吗?';
			}
			layer.confirm(confirmStr,function(){
				page_loading = layer.load(0,{shade:[0.1,'#fff']});
                    $.getJSON(url,{tnid:id,status:status}, function(json){
						layer.close(page_loading);
                        if(json.status==1){util.sysMsg(1,json.msg,json.url);}else{layer.msg(json.msg,{icon:2,time:lq_wait_time*1000});}
                    });				
			});
		});
	};
	//AJAX单项编辑
	util.ajaxEdit = function(obj,url){
		var lqval = obj.html(),lqop  = obj.attr("op"),lqid  =  obj.parents("tr").attr("id");
		if(lqop!='sort'){
		if(obj.parents("tr").attr("visible")==1) return false;
		}
		if(obj.attr('edit')==0) obj.attr('edit','1').html("<input class='form-control' id='edit_"+lqop+"_"+lqid+"' value='"+lqval+"' />").find("input").select();
		$("#edit_"+lqop+"_"+lqid).focus().bind("blur",function(){
				var editval = $(this).val();
				$(this).parents("td").html(editval).attr('edit','0');
				if(lqval!=editval){
					require(['layer'], function(){
					page_loading = layer.load(0,{shade:[0.1,'#fff']});
					if(lqop=='label'){
					$.post(url+"/opLabel",{tnid:lqid,vlaue:editval},function(json){layer.close(page_loading);if(json.status==0) {util.sysMsg(0,json.msg);obj.html(lqval);}});
					}else if(lqop=='sort'){
					$.post(url+"/opSort",{tnid:lqid,vlaue:editval},function(json){layer.close(page_loading);if(json.status==0){util.sysMsg(0,json.msg);obj.html(lqval);}});
					}else{
					$.post(url+"/opProperty",{tcop:lqop,tnid:lqid,vlaue:editval},function(json){layer.close(page_loading);if(json.status==0){util.sysMsg(0,json.msg);obj.html(lqval);}});
					}
					});
				}
		})	
	};	
	util.ajaxPropertyA = function(obj,url){
		if(obj.parents("tr").attr("visible")==1&&obj.parents("tr").attr("opCheck")==1){
			if(obj.parents("tr").attr("msg")==1) util.sysMsg(0,"该记录正处于使用状态，不能操作，请先‘禁用’操作。");
			return false;
		}
		require(['layer'], function(){
			page_loading = layer.load(0,{shade:[0.1,'#fff']});
			$.getJSON(url+"/opProperty",{tcop:obj.attr("op"),tnid:obj.parents("tr").attr("id"),vlaue:obj.attr("val")},function(json){layer.close(page_loading);obj.attr("val",json.data.status);obj.html(json.data.txt);if(json.status==0){util.sysMsg(0,json.msg,json.url);}});
	    });
	};	
	
	/*
	AJAX表单提交（POST）：
	url:提交请求的地址
	formObj:表单
	return:boolean
	*/	
	util.commonAjaxSubmit = function(url,form){
		require(['jquery','layer','ajax.post'], function(){
			if(!url||url==''){url=lq_current_url;}			
			if(!form||form==''){form=$("#"+lq_post_form);}
			var page_loading = layer.load(0,{shade:[0.1,'#fff']});
			form.ajaxSubmit({url:url,type:"POST",success:function(json) {
							if(json.status==1){
								if(json.flag!="login"){layer.msg(json.msg,{icon: 1});}
							}else if(json.status==0){
									layer.msg(json.msg,{icon: 2});
							}else{
									layer.msg('未知错误',{icon: 3});
							}
							if(json.url&&json.url!=''){
								if(json.flag=="login"){util.R(0,json.url);}else{if(json.status==1){util.R(2,json.url);}else{util.R(3,json.url);}}
							}else{
								layer.close(page_loading);	
							}
			}
			});
			return false;
		});
	};
	
	/*
	AJAX-GET请求（GET）：
	url:提交请求的地址
	return:boolean
	*/	
	util.getUrl = function(url){
		require(['jquery','layer'], function(){
			if(!url||url==''){layer.msg('请求的网络地址为空',{icon:2});return false;}
			page_loading = layer.load(0,{shade:[0.1,'#fff']});
			$.getJSON(url,{}, function(json){layer.close(page_loading);util.R(lq_wait_time,json.url);});		
			return false;
		});
	};
	
	//上传文件的回调操作函数
	util.uploadImagesCallback = function(message,status,key){
		if(status==false){util.sysMsg(0,message);}else{$("#"+key+"_preview").attr("src",message);$("#"+key).val(message);}  
	};	

	//加载地区
	util.loadRegion = function(url,id,label_input){
		require(['jquery','layer'], function(){
			var obj=$("#"+id);
			var nextSelect=obj.attr("nextSelect");
			var lqkey=parseInt(obj.attr("lqkey"));
			var listSelect=obj.parent().parent().find("select");
			if(typeof obj=='undefined'|obj=='') return false;
			listSelect.each(function(i){ if(lqkey<i) $(this).get(0).options.length = 1; });
			
			if(typeof nextSelect!='undefined'&&nextSelect!=''){
				page_loading = layer.load(0,{shade:[0.1,'#fff']});
				$.getJSON(url,{tnid:obj.val()}, function(json){
					layer.close(page_loading);
					if(json.status==1){$("#"+nextSelect).append(json.data);}else{layer.msg(json.msg,{icon: 2});}
				});				
			}
			if(label_input){
				var string='';
				listSelect.each(function(){
				if($(this).val()){
				string+=$(this).find("option:selected").text();
				}
				});
				$("#"+label_input).val(string);
			}			
		});					
	};	
	
	/********************************插件 start*****************************************/
	//复制
	util.clip = function(elm, str) {
		if(elm.clip) {
			return;
		}
		require(['jquery.zclip'], function(){
			$(elm).zclip({
				path: '/Public/Static/plugins/zclip/ZeroClipboard.swf',
				copy: str,
				afterCopy: function(){
					var obj = $('<em> &nbsp; <span class="label label-success"><i class="fa fa-check-circle"></i> 复制成功</span></em>');
					var enext = $(elm).next().html();
					if (!enext || enext.indexOf('&nbsp; <span class="label label-success"><i class="fa fa-check-circle"></i> 复制成功</span>')<0) {
						$(elm).after(obj);
					}
					setTimeout(function(){
						obj.remove();
					}, 2000);
				}
			});
			elm.clip = true;
		});
	};
	//cookie
	util.cookie = {
		'prefix' : '',
		// 保存 Cookie
		'set' : function(name, value, seconds) {
			if(!seconds) seconds=cookie_seconds;
			expires = new Date();
			expires.setTime(expires.getTime() + (1000 * seconds));
			document.cookie = this.name(name) + "=" + escape(value) + "; expires=" + expires.toGMTString() + "; path=/";
		},
		// 获取 Cookie
		'get' : function(name) {
			cookie_name = this.name(name) + "=";
			cookie_length = document.cookie.length;
			cookie_begin = 0;
			while (cookie_begin < cookie_length)
			{
				value_begin = cookie_begin + cookie_name.length;
				if (document.cookie.substring(cookie_begin, value_begin) == cookie_name)
				{
					var value_end = document.cookie.indexOf ( ";", value_begin);
					if (value_end == -1)
					{
						value_end = cookie_length;
					}
					return unescape(document.cookie.substring(value_begin, value_end));
				}
				cookie_begin = document.cookie.indexOf ( " ", cookie_begin) + 1;
				if (cookie_begin == 0)
				{
					break;
				}
			}
			return null;
		},
		// 清除 Cookie
		'del' : function(name) {
			var expireNow = new Date();
			document.cookie = this.name(name) + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT" + "; path=/";
		},
		'name' : function(name) {
			return this.prefix + name;
		}
	};//end cookie		
	/********************************插件 end*****************************************/

	if (typeof define === "function" && define.amd) {
		define(['bootstrap'], function(){
			return util;
		});
	} else {
		window.util = util;
	}
})(window);