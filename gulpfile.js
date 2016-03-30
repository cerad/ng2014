const path   = require('path');
const gulp   = require('gulp');
const concat = require('gulp-concat');
const concatCss = require('gulp-concat-css');

const appPublicDir   = path.join(__dirname, 'src/Cerad/Bundle/AppBundle/Resources/public');
const webAppDir      = path.join(__dirname, 'web/bundles/ceradapp');
const nodeModulesDir = path.join(__dirname,'node_modules');

const appTask = function() {

    // Style sheets
    gulp.src([
            appPublicDir + '/css/*',
        ])
        .pipe(gulp.dest(webAppDir + '/css'));

    // Javascripts
    gulp.src([
            appPublicDir + '/js/*'
        ])
        .pipe(gulp.dest(webAppDir + '/js'));

    // images
    gulp.src([
            appPublicDir + '/images/*'
        ])
        .pipe(gulp.dest(webAppDir + '/images'));

};
gulp.task('app',appTask);


const buildTask = function()
{
    appTask();
};
gulp.task('build',buildTask);

const watchTask = function()
{
    buildTask();

    // Why the warnings, seems to work fine
    gulp.watch([
        appPublicDir + '/css/*',
        appPublicDir + '/js/*',
        appPublicDir + '/images/*',
    ],  ['app']);
};
gulp.task('watch',watchTask);