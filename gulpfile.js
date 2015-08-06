// gulp 安裝命令
// @author   Jason 封仁杰 <solidzoro@live.com>
// @lastdate 2015-08-05 15:51:03
//
// npm install gulp autoprefixer strftime gulp-less gulp-autoprefixer gulp-concat gulp-jade gulp-jshint gulp-uglify gulp-jshint gulp-coffee gulp-header gulp-rename gulp-sourcemaps gulp-minify-css gulp-livereload gulp-watch --save-dev



//
//
// init module
// --------------------------------------------
var gulp = require('gulp');
var less = require('gulp-less');
// var minifyCSS = require('gulp-minify-css');
// var sourcemaps = require('gulp-sourcemaps');
var prefix = require('gulp-autoprefixer');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var strftime = require('strftime');
var header = require('gulp-header');
var rename = require('gulp-rename');
var livereload = require('gulp-livereload');
// var jsincluder = require('gulp-jsincluder');



//
//
// mod module
// --------------------------------------------
var gulp_date_now = strftime('%F %T'); // mod current time
var gulp_comment_banner = '/*! * <%= date %> */\n\n'; // mod ASCII banner



//
//
// ADMIN
// --------------------------------------------
var ADMIN = {
    ROOT: 'Application/Admin',
    VIEW: 'Application/Admin/View',
};

// SCRIPT ADMIN
var admin_script_files = [
    "Public/libs/bootstrap/js/transition.js",
    "Public/libs/bootstrap/js/alert.js",
    "Public/libs/bootstrap/js/button.js",
    "Public/libs/bootstrap/js/carousel.js",
    "Public/libs/bootstrap/js/collapse.js",
    "Public/libs/bootstrap/js/dropdown.js",
    "Public/libs/bootstrap/js/modal.js",
    "Public/libs/bootstrap/js/tooltip.js",
    "Public/libs/bootstrap/js/popover.js",
    "Public/libs/bootstrap/js/scrollspy.js",
    "Public/libs/bootstrap/js/tab.js",
    "Public/libs/bootstrap/js/affix.js",

    "Application/Admin/View/_Layout/$_main.js",
    "Application/Admin/View/Index/$_index.js",
    "Application/Admin/View/Public/$_login.js",
    "Application/Common/Builder/$_builder.js",
];

gulp.task('admin_script_module', function() {
    gulp
        .src(admin_script_files)
        .pipe(concat('admin_source.js'))
        .pipe(gulp.dest('Public/js/'))
        .pipe(uglify()).pipe(rename('admin.js'))
        .pipe(header(gulp_comment_banner, {
            date: gulp_date_now
        }))
        .pipe(gulp.dest('Public/js/'));
});

// STYLE ADMIN
var admin_style_files = [
    ADMIN.VIEW + "/_Resource/less/admin.less",
];

gulp.task('admin_style_module', function() {
    gulp
        .src(admin_style_files)
        .pipe(less())
        .pipe(prefix())
        .pipe(rename('admin.css'))
        .pipe(header(gulp_comment_banner, {
            date: gulp_date_now
        }))
        .pipe(gulp.dest('Public/css/'))
        .pipe(livereload());
});



//
//
// 监听
// --------------------------------------------
gulp.task('watching', function() {

    // TASK
    gulp.watch(admin_script_files, ['admin_script_module']);
    gulp.watch([
        ADMIN.ROOT + '/**/*.less',
        'Application/Common/**/*.less'
    ], ['admin_style_module']);

    // LIVERELOAD
    livereload.listen();

    gulp.watch([
        'index.php',
        ADMIN.ROOT + '/**/*.php',
        ADMIN.ROOT + '/**/*.less',
        'Application/Common/**/*.less',
    ], function(event) {
        livereload.changed(event.path);
    });
});



//
//
// 运行
// --------------------------------------------
gulp.task('default', [
    // 后端
    'admin_script_module',
    'admin_style_module',
    // 监听
    'watching'
]);