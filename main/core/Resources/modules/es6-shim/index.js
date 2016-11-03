/**
 * This module imports the full ES6 shim from core-js. The aim is to provide a
 * browser support for ES6 features that cannot be polyfilled by the babel
 * compilator. To be safe, it should be included in every page that requires a
 * webpack bundle using those features.
 *
 * @see https://babeljs.io/docs/usage/polyfill/
 */

import 'core-js/shim'
