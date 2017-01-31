/**
 * Transpiles es6 and jsx files with babel.
 */
const babel = instrument => {
  return {
    test: /\.jsx?$/,
    exclude: /(node_modules|packages)/,
    loader: 'babel',
    query: {
      cacheDirectory: true,
      presets: ['es2015', 'react'],
      plugins: instrument ?
        ['transform-runtime', 'istanbul'] :
        ['transform-runtime']
    }
  }
}

/**
 * Returns the contents of HTML files as plain strings.
 */
const rawHtml = () => {
  return {
    test: /\.html$/,
    loader: 'raw'
  }
}

/**
 * NOTE: do we still need this now that jquery has been moved to externals?
 *
 * Disables AMD for jQuery UI modules. The reason is that these modules try to
 * load jQuery via AMD first but get a version of jQuery which isn't the one
 * made globally available, causing several issues. This loader could probably
 * be removed when jQuery is required only through module imports.
 */
const jqueryUiNoAmd = () => {
  return {
    test: /jquery-ui/,
    loader: 'imports?define=>false'
  }
}

/**
 * Enables css files imports.
 */
const css = () => {
  return {
    test: /\.css$/,
    loader: 'style!css'
  }
}

/**
 * Encodes small images as base64 URIs.
 */
const imageUris = () => {
  return {
    test: /\.(jpe?g|png|gif|svg)$/,
    loader: 'url?limit=25000'
  }
}

/**
 * Loads modernizr configuration.
 *
 * @see https://github.com/peerigon/modernizr-loader
 */
const modernizr = () => {
  return {
    test: /\.modernizrrc$/,
    loader: 'modernizr'
  }
}

/**
 * Loads JSON files.
 */
const json = () => {
  return {
    test: /\.json$/,
    loader: 'json'
  }
}

module.exports = {
  babel,
  rawHtml,
  jqueryUiNoAmd,
  css,
  imageUris,
  modernizr,
  json
}
