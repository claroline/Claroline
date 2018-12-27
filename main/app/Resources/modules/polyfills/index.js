/**
 * Provides all shim required to make client work.
 *
 * All polyfills are provided by external libs.
 * The only purpose of the module is to bundle them and configure them.
 *
 * NB. You never have to require this module manually.
 */

import '#/main/app/polyfills/core-js'
import '#/main/app/polyfills/svg4everybody'
import '#/main/app/polyfills/whatwg-fetch'
