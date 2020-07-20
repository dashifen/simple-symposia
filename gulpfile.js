const {src, dest} = require('gulp'),
  webpackConfig = require('./webpack.config.js'),
  sourcemaps = require('gulp-sourcemaps'),
  webpack = require('webpack-stream'),
  plumber = require('gulp-plumber'),
  named = require('vinyl-named'),
  through = require('through2');

const jsSymposiumDev = async function () {
  return jsDev('assets/js/max/symposium.js');
}

const jsDev = async function (source) {
  return js('development', source);
};

const js = async function (webpackMode, source) {
  const localConfig = webpackConfig;
  localConfig.mode = webpackMode;

  src(source)
    .pipe(plumber())
    .pipe(named())
    .pipe(webpack(localConfig))
    .pipe(sourcemaps.init({loadMaps: true}))
    .pipe(through.obj(function (file, enc, cb) {
      const isSourceMap = /\.map$/.test(file.path);
      if (!isSourceMap) {
        this.push(file);
      }
      cb();
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(dest('assets/js/min/'));
};

const jsSymposiumProd = async function () {
  return jsProd('assets/js/max/symposium.js');
}

const jsProd = async function (source) {
  return js('production', source);
};

exports.jsSymposiumDev = jsSymposiumDev;
exports.jsSymposiumProd = jsSymposiumProd;
