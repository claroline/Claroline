/**
 * Plugins registry.
 *
 * It contains the configurations for all enabled application plugins.
 */

import {declareRegistry} from '#/main/app/registry'

// declares a new registry to grab plugins
const registry = declareRegistry('plugins')

export {
  registry
}
