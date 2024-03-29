const fs = require('fs')

const sass = require('sass')
const postcss = require('postcss')
const autoprefixer = require('autoprefixer')
const cssnano = require('cssnano')

const paths = require('../paths')

function compile(srcFile, outputFile, additionalVarsFiles = []) {
  const compileOptions = {
    loadPaths: [
      paths.root(),
      // this is required because `compileString` does not resolve paths like `compile`
      paths.dirname(srcFile),
      ...additionalVarsFiles.map(varsFile => paths.relative(paths.root(), paths.dirname(varsFile)))
    ],
    sourceMap: true
  }

  let src
  if (additionalVarsFiles) {
    // Add vars from all additional vars file
    let srcPath = paths.relative(paths.root(), srcFile)
    srcPath = srcPath.replaceAll('\\', '/')

    src = ''.concat(...additionalVarsFiles.map(varsFile => {
      let filePath = paths.relative(paths.root(), varsFile)
      filePath = filePath.replaceAll('\\', '/')

      return `@import "${filePath}";\n`
    }), `@import "${srcPath}";\n`)

  } else {
    src = fs.readFileSync(srcFile, 'utf8')
  }

  return sass.compileString(src, compileOptions)
}

function optimize(input, outputFile) {
  return postcss([
    autoprefixer(),
    cssnano({
      preset: 'default'
    })
  ])
    .process(input, {from: outputFile})
    .then(result => result.css)
}

module.exports = {
  compile,
  optimize
}
