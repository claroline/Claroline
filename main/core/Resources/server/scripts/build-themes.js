const path = require('path')
const shell = require('shelljs')

const lessDir = path.resolve(__dirname, '../../less/themes')
const cssDir = path.resolve(__dirname, '../../../../../../../../web/themes')

shell.ls(lessDir).forEach(file => {
  const lessFile = path.join(lessDir, file)
  const cssFile = path.join(cssDir, path.basename(file, '.less'), 'bootstrap.css')
  const lessCmd = `node_modules/less/bin/lessc --verbose ${lessFile} ${cssFile}`
  const postCmd = [
    'node_modules/postcss-cli/bin/postcss',
    '-u autoprefixer -u cssnano',
    '--autoprefixer.browsers "last 2 versions"',
    '--cssnano.safe true',
    '-o',
    cssFile,
    cssFile
  ].join(' ')

  console.log(`Compiling and applying PostCSS on theme '${file}'`);

  shell.exec(lessCmd, (code, stdout) => {
    assertSuccess('Lessc', file, code)
    shell.exec(postCmd, code => {
      assertSuccess('PostCSS', file, code)
      console.log(`postcss: wrote ${file}`)
    })
  })
})

function assertSuccess(pgm, file, code) {
  if (code !== 0) {
    console.error(`${pgm} failed on ${file} with code ${code}`)
    process.exit(1)
  }
}
