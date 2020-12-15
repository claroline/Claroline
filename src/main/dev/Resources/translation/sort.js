// Reorders keys in JSON translations files.

const glob = require('glob')
const fs = require('fs')

const packageDir = `${__dirname}/../../../..`

glob(`${packageDir}/*/*/Resources/translations/*.json`, (er, files) => {
  files.forEach(file => {
    const content = fs.readFileSync(file, 'utf8')
    const decoded = JSON.parse(content)
    const sorted = Object.keys(decoded)
      .sort()
      .reduce((acc, key, i) => {
        acc[key] = decoded[key]
        return acc
      }, {})
    const prettified = JSON.stringify(sorted, null, 4)
    fs.writeFileSync(file, prettified)
  })
})
