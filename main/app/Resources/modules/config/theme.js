import {asset} from '#/main/app/config/asset'
import {param} from '#/main/app/config/parameters'

/**
 * Gets the name of the current theme.
 *
 * @return {string}
 */
function currentTheme() {
  return param('theme.name')
}

/**
 * Gets the path to a theme file.
 * NB. If no file provided, it will return the path to the main theme file.
 *
 * @param assetName
 *
 * @return {string}
 *
 * @todo manage versioning
 */
function theme(assetName = 'bootstrap') {
  return asset(`themes/${currentTheme()}/${assetName}.css`)
}

/**
 * Get the icon defined for a mime-type.
 *
 * @param {object} mimeType
 */
function icon(mimeType) {
  const icons = param('theme.icons')

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

  return asset(typeIcon.url)
}

export {
  currentTheme,
  theme,
  icon
}
