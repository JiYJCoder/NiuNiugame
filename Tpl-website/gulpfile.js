/*  工具基本库  */
const gulp = require('gulp') // 引入gulp基础库
const watch = require('gulp-watch') // 监听
const plumber = require('gulp-plumber') // 防止编译错误报错终止监听
const connect = require('gulp-connect') // 启动WEB服务，热加载
const cache = require('gulp-cache') // 拉取缓存

/*  htmlmin  */
const htmlmin = require('gulp-htmlmin')
/*  css  */
const minifyCSS = require('gulp-minify-css') // css压缩
const less = require('gulp-less') // less编译
const autoprefixer = require('gulp-autoprefixer') // 兼容前缀
const fileinclude = require('gulp-file-include'); //合并公共代码
const importCss = require('gulp-import-css'); //打包import进来的css
//base64
var base64 = require('gulp-base64');

/*  javascript  */
const uglify = require('gulp-uglify') // JS代码压缩
const babel = require('gulp-babel') // ES6转换（gulp-babel babel-preset-es2015）
/*  images  */
const imagemin = require('gulp-imagemin') // 图片压缩
const pngquant = require('imagemin-pngquant') // 深度压缩图片

/*  dist输出路径  */
const DIST_PATH = 'dist'
const SRC_PATH = 'pc'
/*  build输出路径  */
const BUILD_PATH = 'build'

gulp.task('connect', function() {
    connect.server({
        port: 8080,
        root: './pc',
        livereload: true
    })
})


/*  将html复制到dist目录  */
gulp.task('html', function() {
    gulp.src('./pc/**/*.html')
        .pipe(plumber())
        .pipe(connect.reload())
})


/*  task:编译less，并输出到dist/css目录下  */
gulp.task('less', () => {
    gulp.src('pc/res/less/**/*.less')
        .pipe(plumber())
        .pipe(less())
        .pipe(autoprefixer())
        .pipe(base64({
            extensions: ['png'],
            maxImageSize: 30 * 1024, // bytes
            debug: false
        }))
        // .pipe(minifyCSS({ keepSpecialComments: 1, processImport: false })) //解决importCSS报错问题
        .pipe(gulp.dest(SRC_PATH + "/res/css"))
        .pipe(connect.reload())
})

//gulp.src([*.js,'!b*.js']) //匹配所有js文件，但排除掉以b开头的js文件
// '!.tmp/**/*.scss'
// gulp.task('js', () => {
//     // 过滤掉require.js这个文件
//     return gulp.src(['./pc/res/es6/**/*.js','!./pc/res/es6/lib/**/*'])
//         .pipe(plumber())
//         .pipe(babel({
//             presets: ['es2015'],
//             compact: false //解决文件大于500k会报错问题
//         }))
//         // .pipe(uglify())
//         .pipe(gulp.dest(SRC_PATH + '/res/js'))
//         .pipe(connect.reload())
// })

gulp.task('js', () => {
    return gulp.src(['./pc/res/js/**/*.js'])
        .pipe(plumber())
        .pipe(connect.reload())
})



/*  压缩图片  */
gulp.task('images', () => {
    gulp.src('./src/res/images/**/*')
        // .pipe(imagemin({
        //   progressive: true,
        //   svgoPlugins: [{removeViewBox: false}],//不要移除svg的viewbox属性
        //   use: [pngquant()] //使用pngquant深度压缩png图片的imagemin插件
        // }))
        // .pipe(gulp.dest(DIST_PATH + '/images'))
        .pipe(connect.reload())
})


// 自动监听
gulp.task('auto', () => {
    gulp.watch('pc/**/*.html', ['html']),
        gulp.watch('pc/res/js/**/*.js', ['js']),
        gulp.watch('pc/**/*.less', ['less']),
        gulp.watch('pc/images/**/*)', ['images'])
})

// 默认动作
gulp.task('default', ['html', 'js', 'less', 'images', 'auto', 'connect'])