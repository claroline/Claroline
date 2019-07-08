/**
 * Provides all shim required to make client work.
 *
 * All polyfills are provided by external libs.
 * The only purpose of the module is to bundle them and configure them.
 *
 * NB. You never have to require this module manually.
 */

// Provides ES5/ES6 features.
// TODO : filter to only get required polyfills
import 'core-js'

// Provides SVG polyfills.
// (Mostly to get SVG external reference polyfill for IE<13)
import svg4everybody from 'svg4everybody'

svg4everybody()

// This module imports the fetch ES6 shim from whatwg-fetch.js. (https://github.com/github/fetch)
// @see https://babeljs.io/docs/usage/polyfill/
import 'whatwg-fetch'
