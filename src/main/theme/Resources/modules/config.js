import {asset} from '#/main/app/config/asset'
import {param} from '#/main/app/config/parameters'

/**
 * Gets the name of the current theme.
 *
 * @return {string}
 */
function currentTheme() {
  return param('theme.name').toLowerCase().replace(/\s/g, '-')
}

/**
 * Gets the path to a theme file.
 * NB. If no file provided, it will return the path to the main theme file.
 *
 * @param assetName
 *
 * @return {string}
 */
function theme(assetName = 'bootstrap') {
  return asset(`themes/${currentTheme()}/${assetName}.css?v=${param('version')}`)
}

/**
 * Get the icon defined for a mime-type.
 *
 * @param {object} mimeType
 * @param {string} set
 */
function icon(mimeType, set) {
  const icons = param('theme.icons')[set]

  // try to find an icon for the exact mime type
  let typeIcon = icons.find(current => -1 !== current.mimeTypes.indexOf(mimeType))

  if (!typeIcon) {
    // fallback to an icon for the first mimeType part
    const type = mimeType.split('/')[0]
    typeIcon = icons.find(current => -1 !== current.mimeTypes.indexOf(type))

    if (!typeIcon)  {
      typeIcon = icons.find(current => -1 !== current.mimeTypes.indexOf('custom/default'))
    }
  }

  return typeIcon
}

export {
  currentTheme,
  theme,
  icon
}
