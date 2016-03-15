var gulp = require('gulp');
var autoprefix = require('gulp-autoprefixer');
var concat = require('gulp-concat');
var minifyCSS = require('gulp-cssnano');
var sass = require('gulp-sass');


gulp.task('styles', function () {
    return gulp.src([
            'resources/css/pure.min.css',
            'resources/style.scss'
        ])
        .pipe(sass({style: "expanded"}))
        .pipe(concat('all.css'))
        .pipe(autoprefix('last 2 versions'))
        .pipe(minifyCSS({aggressiveMerging: false}))
        .pipe(gulp.dest('./client/css/'))
});
