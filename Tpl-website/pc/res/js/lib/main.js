/*配置require.js*/
// function getBasePath(){
// 	var els = document.getElementsByTagName('script'), src;
// 	for (var i = 0, len = els.length; i < len; i++) {
// 		src = els[i].src || '';
// 		if (/teacher\.js/.test(src)) {
// 			return src.substring(0, src.lastIndexOf('/') + 1);
// 		}
// 	}
// 	return '';
// }
// var aa =  getBasePath()
// console.log(aa)
//     <!-- <script src="http://echarts.baidu.com/build/dist/echarts-all.js"></script> -->
//     /Tpl-website/pc/res/js
var baseUrl;
if (window.location.host == "localhost:8080") {
    baseUrl = "../res/js"
} else {
    baseUrl = "/Tpl-website/pc/res/js"
}

require.config({　
    baseUrl: baseUrl, //配置基目录
    　　paths: {　　　　　　
        "jquery": "./lib/jquery",
        'layer': '../plugins/layer/layer',
        "echarts": "./lib/echarts-all",
        "clip": './lib/clipboard.min', //复制插件
        "datePicker": './lib/datePicker',
        "timePicker": './lib/timePicker',
        "verify": "./tools/verify",
        "myAjx": "./tools/ajax",
        "ajax": "./tools/newAjax",
        "Util": "./tools/Util",
        "syllabus": "./teacher/syllabus",
        "unlock": "../plugins/unlock/unlock",
        "swiper":"../plugins/swiper/swiper-3.4.2.jquery.min",
        "kindeditor":"../plugins/kindeditor/kindeditor",
        'validate':'./lib/validate',

        "teacherIndex": './teacher/index',
        "register": './teacher/register',
        "myset": "./teacher/myset",
        "certification": "./teacher/certification",
        "sqlive": "./teacher/sqlive",
        "sqlivechange": "./teacher/sqlivechange",
        "teacher": "./teacher/teacher",
        "complete": "./teacher/completecourse",
        　
        "studentIndex": "./student/index",
        "my": "./student/my",
        "studentRegister":"./student/register",
        "studentBrodcast": "./student/broadcast",　　
        "myCourse": "./student/mycourse",
        "teacherdetail":"./search/teacherdetail",
        "live":"./live/index", 
        "vod":"./vod/index",
        "forget":'./student/forget',
        "login":'./student/login'  
    },
    waitSeconds: 15,
    map: {
        '*': {
            'css': './lib/css'
        }
    },
    shim: {
        'layer': {
            deps: ['css!../plugins/layer/skin/default/layer.css']
        },
        "unlock": {
            deps: ['css!../plugins/unlock/unlock.css']
        }
    }　　
});
