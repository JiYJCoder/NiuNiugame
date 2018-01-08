<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?php echo ($title); ?></title>
<style>
body{padding:20px;font-size:12px;color:#000}
h1{text-align:center;padding-bottom:20px}
h2{font-size:14px;margin-bottom:0;padding-bottom:0;margin-top:50px}
h3{font-size:12px;margin-bottom:0;padding-bottom:0;background-color:#eee;padding:5px}
ul{margin-top:0;padding-top:0;color:#333}
table{margin:0;padding:0;border-collapse:collapse;margin-top:10px}
table td,table th{border:1px solid #ccc;padding:2px}
pre{background-color: #EDF6EE;border: 1px dotted #9c9;color:#060;font-family:"Courier New";overflow:hidden;padding:20px;
　　word-break:break-all; /*支持IE，chrome，FF不支持*/
　　word-wrap:break-word;/*支持IE，chrome，FF*/
}
ol{list-style-type:none;}
#content{margin-left:320px;overflow:hidden}
.demo{border:1px solid #ccc;background-color:#ffe;padding:20px;width:250px;position:fixed;left:20px;top:350px;_position:absolute;}
.demo h2{margin-top: 0}
.demo .list{margin-top: 15px}
.leftBar{border:1px solid #ccc;background-color:#ffe;padding:20px 10px;width:250px;position:fixed;left:20px;top:20px;_position:absolute;overflow-y:scroll;height: 90%}
.leftBar h2{margin:0}
.leftBar li{margin:8px 0;}
.leftBar p{text-align:right;margin-top:-15px;height:15px;overflow:hidden}
#top{line-height:0;overflow:hidden}
.leftBar  a{color:#333;text-decoration:none;font-size:1.2em;}
.leftBar  a:hover{text-decoration:underline;color:#c00}
.leftBar  a.current{color:#c00;}
</style>
</head>

<body>
	<a id="top"></a>
	<h1>移动数据API文档</h1>
<div id="content">

<div>
<h2>1、接口说明<a id="cm.a1"></a></h2>
	<p>
        (1) 返回内容为json格式，采用UTF-8编码。<br/>
        (2) 信息内容中含有时间字段的，字段值为urlEncode格式。<br/>
        (3) 返回内容{"status":状态(0,1,2),"msg":"操作提示","data":"回调数据","url":"回调响应地址","note":"备注"};<br/>
        (4) 用户请求加权文档，统一传入uid,token两值。<br/>
        (4) 状态码说明：<br/>0：请求失败，1：请求成功，2：未授权
	</p>			
</div>

<?php if(is_array($list)): $key = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($key % 2 );++$key;?><div>
<h2><?php echo ($data["no"]); ?>、<?php echo ($data["zc_title"]); ?><a id="cm.a<?php echo ($data["no"]); ?>"></a></h2>
	<p><?php echo ($data["zc_url"]); ?></p>
    <?php echo (html_entity_decode($data["zc_content"])); ?>
</div><?php endforeach; endif; else: echo "" ;endif; ?>



</div>
<div class="leftBar">
<h2>API文档</h2>
<p><a id="showAll" href="#showAll">显示全部</a></p>
<ol id="leftBar">
</ol>
</div>

<!-- 左侧导航,数据为自动生成 -->
<script>
    (function () {
        //获取右侧H2的内容
        var doc = document, titles = doc.getElementById("content").getElementsByTagName("h2"),
        shtml = "";
        for (var k = 0, m = titles.length; k < m; k++) {
            var str = titles[k].innerText ? titles[k].innerText : titles[k].innerHTML;
            str = str.split("<a")[0];
            shtml += '<li><a href="#cm.a' + (k + 1) + '">' + str + '</a></li>\n';
        }
        doc.getElementById("leftBar").innerHTML = shtml;

        var links = doc.getElementById("leftBar").getElementsByTagName("a"),
            divs = doc.getElementById("content").getElementsByTagName("div"),
            oAll = doc.getElementById("showAll");
            oAll.style.color = "#ddd";
        function hideDivs() {
            for (var i = 0, len = divs.length; i < len; i++) {
                divs[i].style.display = "none";
            }
        }
        function showDiv(i) {
            divs[i].style.display = "block";
            var url = location.hash ? location.href.replace(location.hash, "#top") : (location.href + "#top");
            location.href = url;
        }
        function addCurrent(cls, that) {
            for (var i = 0, len = divs.length; i < len; i++) {
                links[i].className = "";
            }
            that.className = "current";
            oAll.style.color = "";
        }
        function showAll(obj) {
            for (var i = 0, len = divs.length; i < len; i++) {
                links[i].className = "";
                divs[i].style.display = "block";
            }
            obj.style.color = "#ddd";
        }
        for (var j = 0, l = links.length; j < l; j++) {
            (function (j) {
                links[j].onclick = function () {
                    hideDivs();
                    showDiv(j);
                    addCurrent("current", this);
                    return false;
                }
            })(j);
        }
        oAll.onclick = function () { showAll(this) };
    })();
</script>
</body>
</html>