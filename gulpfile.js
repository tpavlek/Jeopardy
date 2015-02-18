/*
 * Install this into the root of your new project directory, and update
 * any paths. style/style.scss is the sassy css file you should create
 * specifically for your project, and it should import _base-style.scss from
 * diversity-css.
 */

var gulp = require('gulp');
var autoprefix = require('gulp-autoprefixer');
var concat = require('gulp-concat');
var minifyCSS = require('gulp-minify-css');
var sass = require('gulp-sass');

gulp.task('styles', function() {
    return gulp.src([
        'resources/css/fonts.css',
        'resources/css/pure.min.css',
        'resources/css/grids-responsive.min.css',
        'resources/style.scss'
    ])
        .pipe(sass({ style: "expanded" }))
        .pipe(concat('all.css'))
        .pipe(autoprefix('last 2 versions'))
        .pipe(minifyCSS())
        .pipe(gulp.dest('./client/css/'))
});
