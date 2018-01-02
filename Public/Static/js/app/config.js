
require.config({
	baseUrl: '/Public/Static/js/app',
	paths: {
		'jquery': '../lib/jquery-1.11.1.min',
		'jquery.ui': '../lib/jquery-ui-1.10.3.min',
		'jquery.caret': '../lib/jquery.caret',
		'jquery.jplayer': '../../plugins/jplayer/jquery.jplayer.min',
		'jquery.zclip': '../../plugins/zclip/jquery.zclip.min',
		'bootstrap': '../lib/bootstrap.min',
		'bootstrap.switch': '../../plugins/switch/bootstrap-switch.min',
		'css': '../lib/css.min',
		'map': 'http://api.map.baidu.com/getscript?v=2.0&ak=F51571495f717ff1194de02366bb8da9&services=&t=20140530104353',
		'layer': '../../plugins/layer3/layer',
		'md5': '../lib/jquery-md5',
		'sha1': '../lib/jquery-sha1',
		'ajax.post': '../lib/jquery.form',
		'moment': '../lib/moment',
		'datetimepicker': '../../plugins/datetimepicker/jquery.datetimepicker',
		'daterangepicker': '../../plugins/daterangepicker/daterangepicker',
		'colorpicker': '../../plugins/colorpicker/js/bootstrap-colorpicker',
		'chart': '../lib/chart.min',
		'baidueditor': '../../plugins/ueditor/theone',
		'bdlang': '../../plugins/ueditor/lang/zh-cn/zh-cn',
		'zeroclipboard': '../../plugins/ueditor/third-party/zeroclipboard/ZeroClipboard.min',		
	},
	shim:{
		'jquery.ui': {
			exports: "$",
			deps: ['jquery']
		},
		'layer': {
			exports: "$",
			deps: ['css!../../plugins/layer3/skin/default/layer.css', 'jquery']
		},						
		'map': {
			exports: 'BMap'
		},
		'daterangepicker': {
			exports: '$',
			deps: ['bootstrap', 'moment', 'css!../../plugins/daterangepicker/daterangepicker.css']
		},
		'datetimepicker' : {
			exports : '$',
			deps: ['jquery', 'css!../../plugins/datetimepicker/jquery.datetimepicker.css']
		},
		'colorpicker' : {
			exports : '$',
			deps: ['css!../../plugins/colorpicker/css/colorpicker.css']
		},				
		'chart': {
			exports: 'Chart'
		},		
		'baidueditor': {
			deps: ['../../plugins/ueditor/ueditor.config', 'css!../../plugins/ueditor/themes/default/css/ueditor']
		},
		'bdlang':{
			 deps: ['baidueditor']
		}		
	}
});