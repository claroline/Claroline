const fs = require('fs')
const less = require('less')
const path = require('path')
const paths = require('../paths')
const postcss = require('postcss')

function compile(srcFile, outputFile, additionalVarsFiles) {
  const baseName = path.basename(outputFile, '.css')

  const compileOptions = {
    filename: srcFile,
    sourceMap: {
      // directly embed the less files in the map instead of referencing them
      // this permits to avoid giving access to the original files
      outputSourceFiles: true,
      sourceMapBasepath: paths.root(),
      sourceMapFilename:'./'+baseName+'.css.map'
    }
  }

  var src = fs.readFileSync(srcFile, 'utf8')
  if (additionalVarsFiles) {
    // Add vars from all additional vars file
    src.concat(...additionalVarsFiles.map(varsFile => fs.readFileSync(varsFile, 'utf8')))
  }

  return less.render(src, compileOptions)
}

function optimize(input) {
  /*const postCmd = [
    'node_modules/postcss-cli/bin/postcss',
    '-u autoprefixer -u cssnano',
    '--autoprefixer.browsers "last 2 versions"',
    '--cssnano.safe true',
    '-o',
    cssFile,
    cssFile
  ].join(' ')*/

  return postcss([

  ]).process(input, { from, to });
}

module.exports = {
  compile
}
