/**
 * Provides all shim required to make client work.
 *
 * All polyfills are provided by external libs.
 * The only purpose of the module is to bundle them and configure them.
 *
 * NB. You never have to require this module manually.
 */

import '#/main/core/scaffolding/shim/core-js'
import '#/main/core/scaffolding/shim/modernizr'
import '#/main/core/scaffolding/shim/svg4everybody'
import '#/main/core/scaffolding/shim/whatwg-fetch'
