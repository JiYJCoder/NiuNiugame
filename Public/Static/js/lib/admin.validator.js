/*

说明:后台正则验证
******************************************************************
引用：
name="password" controlTitle="帐号" dataType="account" 
name="password" controlTitle="密码" dataType="password" 
name="confirm" controlTitle="密码确认" confirm="password"
name="english" controlTitle="英文" dataType="english" dataLength="4,10"
name="chinese" required="required" controlTitle="中文"
name="number" controlTitle="数字" dataBetween="10,100" 
name="integer" controlTitle="整数" dataType="integer" 
name="float" controlTitle="浮点数" dataType="float"
name="date" controlTitle="日期" dataType="date"
name="email" controlTitle="邮件" dataType="email"
name="url" controlTitle="网址" dataType="url"
name="phone" controlTitle="电话" dataType="phone"
name="mobile" controlTitle="手机" dataType="mobile"
name="ip" controlTitle="IP地址" dataType="ip" 
name="zipcode" controlTitle="邮编" dataType="zipcode"
name="qq" controlTitle="QQ号码" dataType="ip"
name="msn" controlTitle="MSN" dataType="msn"
name="idcard" controlTitle="身份证" dataType="idcard" 
*/

//去除字符串两边的空格
String.prototype.trim = function (){return this.replace(/(^\s+)|(\s+$)/g, "");}
//检测字符串是否为空
String.prototype.isEmpty = function (){return !(/.?[^\s　]+/.test(this));}
//检测值是否介于某两个指定的值之间
String.prototype.isBetween = function (val, min, max){return isNaN(val) == false && val >= min && val <= max;}
//获取最大值或最小值
String.prototype.getBetweenVal = function (what) {
    var val = this.split(',');
    var min = val[0];
    var max = val[1] == null ? val[0] : val[1];
    if (parseInt(min) > parseInt(max)) {min = max;max = val[0];}
    return what == 'min' ? (isNaN(min) ? null : min) : (isNaN(max) ? null : max);
}

//特定后台页面
var adminValidator = function (formObj) {
    this.allTags = formObj.getElementsByTagName('*');
    //字符串验证正则表达式
    this.reg = new Object();
	this.reg.account = /^[a-zA-Z0-9@.]{4,30}$/;
	this.reg.account_hun = /^[a-zA-Z0-9\u4e00-\u9fa5]{4,30}$/;
	this.reg.password = /^[a-zA-Z0-9]{6,30}$/;	
    this.reg.english = /^[a-zA-Z\-]+$/;
    this.reg.chinese = /^[\u0391-\uFFE5]+$/;
    this.reg.number = /^[-\+]?\d+(\.\d+)?$/;
    this.reg.integer = /^[-\+]?\d+$/;
    this.reg.float = /^[-\+]?\d+(\.\d+)?$/;
    this.reg.date = /^(\d{4})(-|\/)(\d{1,2})\2(\d{1,2})$/;
	this.reg.time = /^(\d{2}):(\d{2})$/;
    this.reg.email = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
    this.reg.url = /^(((ht|f)tp(s?))\:\/\/)[a-zA-Z0-9]+\.[a-zA-Z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/;
    this.reg.phone = /^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/;
    this.reg.mobile = /^1[34578]\d{9}$/;
    this.reg.ip = /^(0|[1-9]\d?|[0-1]\d{2}|2[0-4]\d|25[0-5]).(0|[1-9]\d?|[0-1]\d{2}|2[0-4]\d|25[0-5]).(0|[1-9]\d?|[0-1]\d{2}|2[0-4]\d|25[0-5]).(0|[1-9]\d?|[0-1]\d{2}|2[0-4]\d|25[0-5])$/;
    this.reg.zipcode = /^[1-9]\d{5}$/;
    this.reg.qq = /^[1-9]\d{4,10}$/;
    this.reg.msn = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
    this.reg.idcard = /(^\d{15}$)|(^\d{17}[0-9Xx]$)/;
	this.reg.wechat = /^[a-zA-Z\d_]{5,}$/;
	this.reg.color = /^#[0-9a-fA-F]{6}$/;
	
    //错误输出信息
    this.tip = new Object();
    this.tip.unknow = '未找到的验证类型，无法执行验证。';
    this.tip.paramError = '参数设置错误，无法执行验证。';
    this.tip.required = '不允许为空。';
    this.tip.account = '仅允许英文字母及数字或@.[4-30个字符] (a-z A-Z 0-9)。';
	this.tip.account_hun = '允许中英文字符及数字[4-30个字符]';
	this.tip.password = '仅允许英文字母，（@#$!*）及数字[6-30个字符] (a-z A-Z 0-9)。';	
    this.tip.english = '仅允许英文字符及下划线 (a-zA-Z0-9_)。';
    this.tip.chinese = '仅允许中文字符。';
    this.tip.number = '不是一个有效的数字。';
    this.tip.integer = '不是一个有效的整数。';
    this.tip.float = '不是一个有效的浮点数。(如货币类)';
    this.tip.date = '不是一个有效的日期格式。 (例如：2015-06-01)';
	this.tip.time = '不是一个有效的时间格式。 (例如： 09:00)';
    this.tip.email = '不是一个有效的电子邮件格式。';
    this.tip.url = '不是一个有效的超链接格式。';
    this.tip.phone = '不是一个有效的电话号码。';
    this.tip.mobile = '不是一个有效的手机号码。';
    this.tip.ip = '不是一个有效的IP地址。';
    this.tip.zipcode = '不是一个有效的邮政编码。';
    this.tip.qq = '不是一个有效的QQ号码。';
    this.tip.msn = '不是一个有效的MSN帐户。';
    this.tip.idcard = '不是一个有效的身份证号码。';
	this.tip.wechat = '不是一个有效的微信号。';
	this.tip.color = '不是一个有效的十六进制值，如:#ffffff。';
	
    //获取控件标题
    this.getControlTitle = function (){return this.element.getAttribute('controlTitle') == null? '指定控件标题': this.element.getAttribute('controlTitle');}

    //设置控件焦点 
    this.setFocus = function (ele) {
		var eleEventid=ele.id;
		$("#form_group_"+eleEventid).addClass("has-error");
        ele.onkeyup = function () {$("#form_group_"+eleEventid).removeClass("has-error");}
    }
	
    //输出错误反馈信息
    this.feedback = function (type) {
        try {
            var msg = eval('this.tip.'+ type) == undefined ?type :this.getControlTitle() + eval('this.tip.' + type);
        } catch (e) {
            msg = type;
        }
        this.setFocus(this.element);
		util.sysMsg(0,msg);
    };
    //执行字符串验证
    this.validate = function () {
        var v = this.element.value;
        //验证是否允许非空
        var required = this.element.getAttribute('required');
        if (required != null && v.isEmpty()) {
            this.feedback('required');
            return false;
        }
        //验证是否合法格式
        var dataType = this.element.getAttribute('dataType');
        if (!v.isEmpty() && dataType != null &&  dataType.toLowerCase() != 'password') {
            dataType = dataType.toLowerCase();
            try {
                if (!(eval('this.reg.' + dataType)).test(v)) {
                    this.feedback(dataType);
                    return false;
                }
            } catch(e) {
                this.feedback('unknow');
                return false;
            }
        }
        //执行数据验证
        var confirm = this.element.getAttribute('confirm');
        if (confirm != null) {
            try {
                var data = eval('formObj.' + confirm + '.value');
                if (v != data) {
					util.sysMsg(0,"两次输入的内容不一致，请重新输入。");
                    this.setFocus(this.element);
                    return false;
                }
            } catch (e) {
                this.feedback('参数错误');
                return false;
            }
        }
		
		//验证上传文件后缀是否合法 s
		var fileext = this.element.getAttribute('fileext');
		if (fileext != null && !v.isEmpty()) {
			var lcext=/[^\.]+$/.exec(v);//获得后缀
			var laexts=fileext.split(",");
			var lnchick=0;
			for(var lnindex=0;lnindex<laexts.length;lnindex++){
				if(laexts[lnindex]==lcext){lnchick=1;}
			}			
            if(lnchick==0){
					util.sysMsg(0,"上传的文件不合法，请重新输入。");
                    this.setFocus(this.element);
                    return false;
            }
		}//验证上传文件后缀是否合法 e		
		
        //验证数字大小
        var dataBetween = this.element.getAttribute('dataBetween');
        if (!v.isEmpty() && dataBetween != null) {
            var min = dataBetween.getBetweenVal('min');
            var max = dataBetween.getBetweenVal('max');
            if (min == null || max == null) {this.feedback('paramError');return false;}
            if (!v.isBetween(v.trim(), min, max)) {
                this.feedback(this.getControlTitle() + '必须是介于 ' + min + '-' + max + ' 之间的数字。');return false;
            }
        }
        //验证字符长度
        var dataLength = this.element.getAttribute('dataLength');
        if (!v.isEmpty() && dataLength != null) {
            var min = dataLength.getBetweenVal('min');
            var max = dataLength.getBetweenVal('max');
            if (min == null || max == null) {
                this.feedback('paramError');return false;
            }
            if (!v.isBetween(v.trim().length, min, max)) {
                this.feedback(this.getControlTitle() + '必须是 ' + min + '-' + max + ' 个字符。');return false;
            }
        }
        return true;
    };
    //执行初始化操作
    this.init = function () {
		var reg=/'+|"+/;
		var tagName,tagType;
        for (var i=0; i<this.allTags.length; i++) {
			tagName=this.allTags[i].tagName.toLowerCase();
            if (tagName== 'input'||tagName=='select'||tagName=='file'||tagName=='textarea'){
				tagType=allTags[i].type.toLowerCase();
				if(tagType=="text"|tagType=="textarea"){
//					if(allTags[i].getAttribute("kindeditor")=='theone'){
//						//kindeditor_sync();//编辑器赋值
//					}else{
////						if(reg.test(allTags[i].value)&&allTags[i].id){
////							util.sysMsg(0,"输入内容不能含有\"单引号\"或\"双引号\"!");
////							this.setFocus(allTags[i]);
////							allTags[i].select();
////							return false;
////						}
//					}
				}
                this.element = allTags[i];
                if (!this.validate()) return false;
            }
        }
		return true;
    };
    return this.init();
}
