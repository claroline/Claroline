/**
 * Plugins registry.
 *
 * It contains the configurations for all applications provided by enabled plugins.
 */

import {declareRegistry} from '#/main/app/registry'

// declares a new registry to grab plugins
const registry = declareRegistry('plugins')

export {
  registry
}
