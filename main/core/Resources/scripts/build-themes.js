const path = require('path');
const shell = require('shelljs');

const lessDir = path.resolve(__dirname, '../less/themes');
const cssDir = path.resolve(__dirname, '../../../../../web/themes');

shell.ls(lessDir).forEach(file => {
  const lessFile = path.join(lessDir, file);
  const cssFile = path.join(cssDir, path.basename(file, '.less'), 'bootstrap.css');

  shell.exec(['node_modules/.bin/lessc --verbose', lessFile, cssFile].join(' '));
  shell.echo('postcss: applying autoprefixer and cssnano');
  shell.exec([
    'node_modules/.bin/postcss',
    '-u autoprefixer -u cssnano',
    '--autoprefixer.browsers "last 2 versions"',
    '--cssnano.safe true',
    '-o',
    cssFile,
    cssFile
  ].join(' '));
});
