'use strict'
const path = require('path')
const fs = require('fs')
const shell = require('shelljs')

const lessDir = path.resolve(__dirname, '../../less/themes')
const themesDir = path.resolve(__dirname, '../../../../../../../../web/themes')
const oldThemesDir = path.resolve(themesDir, 'less')

const newThemes = []
let clarolineThemeLess = null
shell.ls(lessDir).forEach(file => {
  const extension = path.extname(file)
  if (extension === '.less') {
    const fileName = path.basename(file, extension)
    if (fileName.trim().toLowerCase() === 'claroline' || clarolineThemeLess === null) {
      clarolineThemeLess = path.resolve(lessDir, file)
    }
    newThemes.push(fileName)
  }
})

if (clarolineThemeLess === null) {
  console.error(`No default theme was found. Please make sure folder ${lessDir} is not empty and you have sufficient rights.`)
  process.exit(1)
}

if (!fs.existsSync(oldThemesDir) || !fs.lstatSync(oldThemesDir).isDirectory()) {
  console.log(`No old themes were found to compile.`)
  process.exit(0)
}

const oldThemes = fs.readdirSync(oldThemesDir)
if (!oldThemes || oldThemes.length === 0) {
  console.log(`No old themes were found to compile.`)
  process.exit(0)
}
oldThemes.forEach(folder => {
  const themeFolder = path.resolve(oldThemesDir, folder)
  // If folder is directory and folder name is not one of new themes, proceed to compile
  if (fs.lstatSync(themeFolder).isDirectory() && newThemes.indexOf(folder) === -1) {
    // Ne file containing common less code:
    const newCommonFilePath = path.resolve(themeFolder, 'newCommon.less')
    // theme.less file
    const themeFilePath = path.resolve(themeFolder, 'theme.less')
    // variables.less file
    const variablesFilePath = path.resolve(themeFolder, 'variables.less')
    // newCommon.less content
    let newCommonLessScript = ''
    // If varibales.less exists import it in newCommon.less
    if (fs.existsSync(variablesFilePath)) {
      newCommonLessScript += `@import "variables.less";\n`
    }
    // If theme.less exists import it in newCommon.less
    if (fs.existsSync(themeFilePath)) {
      newCommonLessScript += `@import "theme.less";\n`
    }
    // If newCommon.less not empty,
    if (newCommonLessScript !== '') {
      newCommonLessScript = `@import "${path.relative(themeFolder, clarolineThemeLess)}";\n` + newCommonLessScript
      fs.writeFileSync(newCommonFilePath, newCommonLessScript, {'mode': 0o775})
      const cssFile = path.join(themesDir, folder, 'bootstrap.css')
      const cssBakFile = path.join(themesDir, folder, 'bootstrap.css.bak')
      // Backup old css file if exists, is only made once before first script execution. This way we ensure old css integrity.
      if (!fs.existsSync(cssBakFile) && fs.existsSync(cssFile)) {
        const copyCmd = `cp -p -f ${cssFile} ${cssBakFile}`
        shell.exec(copyCmd, (code, stdout) => {
          assertSuccess('cp', cssBakFile, code)
        })
      }
      // Compile and minimize theme
      const lessCmd = `node_modules/less/bin/lessc --verbose ${newCommonFilePath} ${cssFile}`
      const postCmd = [
        'node_modules/postcss-cli/bin/postcss',
        '-u autoprefixer -u cssnano',
        '--autoprefixer.browsers "last 2 versions"',
        '--cssnano.safe true',
        '-o',
        cssFile,
        cssFile
      ].join(' ')

      console.log(`Compiling and applying PostCSS on theme '${folder}'`);

      shell.exec(lessCmd, (code, stdout) => {
        assertSuccess('Lessc', folder, code)
        shell.exec(postCmd, code => {
          assertSuccess('PostCSS', folder, code)
          console.log(`postcss: wrote ${folder}`)
        })
      })
    }
  }
})

function assertSuccess(pgm, file, code) {
  if (code !== 0) {
    console.error(`${pgm} failed on ${file} with code ${code}`)
    process.exit(1)
  }
}
