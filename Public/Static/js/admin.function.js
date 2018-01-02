

$(document).ready(function(){
	//********************************列表页操作 start********************************
	//全选/反选
	$("#list_checkbox").click(function(){util.checked(this);});
	$("input[name=items]").click(function(){if(this.checked){$("#os_warning").css({"display":"inline-block"});}else{$("#os_warning").css({"display":"none"});}});
	//无效操作提示
	$(".opInvalid").click(function(){util.sysMsg(4,"该操作无效！");return false;});
	//操作菜单响应
	$("#os_button").click(function(){$("#os_warning").css({"display":"none"});});
	$("#os_button_list a").click(function(){
		if($(this).parent().attr("class")=='line-th'){
			util.sysMsg(4,"该操作无效！");return false;
		}else{
			var op=$(this).attr("op");
			if(op=='opDeleteCheckbox'){
				util.deletelCheckbox();
			}else if(op=='opVisibleCheckbox'){
				util.visibleCheckbox($(this).attr("state"));
			}else if(op=='pagesize'){
                    var searchurl=$("#thinkphpurl").val()+'index/s/';
                    var urlpara="pagesize/"+encodeURIComponent($(this).attr("val"))+"/";
                    location.href=searchurl+base64encode(urlpara);				
				location.href=url;
			}
		}
	});
	//弹出页面层
	$("a[class=op-open-windows]").click(function(){
		var loobj=$(this).parent().parent().parent();
		require(['layer'], function(){
			layer.open({title:'ID['+loobj.find("tr").attr("id")+']详细内容',type: 1,skin: 'layui-layer-demo',closeBtn: 0,anim: 2,area: ['690px', '400px'],shadeClose: true,content: '<div style="padding:10px;">'+loobj.find("td[op='open-windows']").attr("lqInfo")+'</div>'
			});			
		});
	});	
	//列表页操作提示
	$('#op_msg').on('click', function(){require(['layer'], function(){layer.tips("单记录编辑，请先‘禁用’再操作", '#op_msg');});});	
	//********************************列表页操作 end********************************
	
	//********************************images文件上传 start********************************
	//打开图片列表
	$(".open-images-win").click(function(){
		if($(this).attr("allowOpen")==0){util.sysMsg(4,"该操作无效！");return false;}
		var open_url=$(this).attr("lqUrl");
		var title=$(this).attr("lqTitle");
		var height='410px';
		if(!title) title='图片库';
		if(title=='多图片上传') height='460px;'
		require(['layer'], function(){layer.open({type:2,title:title,shadeClose:true,shade:0.8,area:['690px',height],content:open_url}); 		
		});
	});
	//上传图片
	$(".images-upload").change(function(){
		var obj=$(this),extend=obj.attr("extend"),action=obj.attr("lqAction"),filename=obj.val();
		var myextend=/\.[^\.]+/.exec(obj.val());
		myextend=String(myextend);
		myextend=myextend.substring(1);
		if(myextend.indexOf(extend)){
			require(['layer'], function(){var loading = layer.load(1);$("#LQForm").attr("action",action);$("#LQForm").submit();return false;});				
		}
	});
	//********************************images文件上传 end********************************
	
	
	
});