const fs = require('fs')

const sass = require('sass')
const path = require('path')
const postcss = require('postcss')
const autoprefixer = require('autoprefixer')
const cssnano = require('cssnano')

const paths = require('../paths')

function compile(srcFile, outputFile, additionalVarsFiles = []) {
  const baseName = path.basename(outputFile, '.css')

  const compileOptions = {
    style: 'compressed',
    loadPaths: [
      paths.root(),
      // this is required because `compileString` does not resolve paths like `compile`
      paths.dirname(srcFile),
      ...additionalVarsFiles.map(varsFile => paths.relative(paths.root(), paths.dirname(varsFile)))
    ]
    //style: outputFile
    /*filename: srcFile,
    paths: [
      paths.root()
    ],
    math: 'always',
    sourceMap: {
      // directly embed the less files in the map instead of referencing them
      // this permits to avoid giving access to the original files
      outputSourceFiles: true,
      sourceMapBasepath: paths.root(),
      sourceMapFilename:'./'+baseName+'.css.map'
    }*/
  }

  let src = fs.readFileSync(srcFile, 'utf8')

  if (additionalVarsFiles) {
    // Add vars from all additional vars file
    src = src.concat(...additionalVarsFiles.map(varsFile => {
      let filePath = paths.relative(paths.root(), varsFile)
      filePath = filePath.replaceAll('\\', '/')

      return `@import "${filePath}";\n`
    }))
  }

  return sass.compileString(src, compileOptions)
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
