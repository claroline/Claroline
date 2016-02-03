var path = require('path');

require('shelljs/make');

target.all = function () {
  target.themes();
};

target.themes = function () {
  var lessDir = path.resolve(__dirname, 'vendor/claroline/core-bundle/Resources/less/themes');
  var cssDir = path.resolve(__dirname, 'web/themes');

  ls(lessDir).forEach(function (file) {
    var lessFile = path.join(lessDir, file);
    var cssFile = path.join(cssDir, path.basename(file, '.less'), 'bootstrap.css');
    exec(['node_modules/.bin/lessc --verbose', lessFile, cssFile].join(' '));
    console.log('postcss: applying autoprefixer and cssnano');
    exec([
      'node_modules/.bin/postcss',
      '-u autoprefixer -u cssnano',
      '--autoprefixer.browsers "last 2 versions"',
      '--cssnano.safe true',
      '-o',
      cssFile,
      cssFile
    ].join(' '));
  });
};
