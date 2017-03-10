const fs = require('fs')
const less = require('less')
const path = require('path')
const postcss = require('postcss')
const autoprefixer = require('autoprefixer')
const cssnano = require('cssnano')

const paths = require('../paths')

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
    src = src.concat(...additionalVarsFiles.map(varsFile => `@import "${varsFile}";\n`))
  }

  return less.render(src, compileOptions)
}

function optimize(input) {
  return postcss([
    autoprefixer({
      browsers: 'last 2 versions'
    }),
    cssnano({
      safe: true
    })
  ])
    .process(input)
    .then(result => result.css)
}

module.exports = {
  compile,
  optimize
}
