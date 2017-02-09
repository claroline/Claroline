// Creates empty translation files where needed for supported languages.

const glob = require('glob')
const path = require('path')
const fs = require('fs')

const packageDir = `${__dirname}/../../../..`
const languages = ['fr', 'nl', 'de', 'es', 'it']

glob(`${packageDir}/*/*/Resources/translations/*.en.json`, (er, files) => {
  files.forEach(file => {
    const parsed = path.parse(file)
    const domain = parsed.name.split('.')[0]

    languages.forEach(language => {
      const file = `${parsed.dir}/${domain}.${language}.json`

      if (!fs.existsSync(file)) {
        fs.writeFileSync(file, '{}')
      }
    })
  })
})
