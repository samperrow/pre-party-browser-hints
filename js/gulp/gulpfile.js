const { src, dest } = require('gulp');
const concat = require('gulp-concat');

const jsBundle = () =>
    src([
        'src/init.js',
        'src/createAjaxRequest.js'
    ])
    .pipe(concat('scripts.js'))
    .pipe(dest('dist/js'));

exports.jsBundle = jsBundle;

