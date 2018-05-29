/**
 * Data registry.
 *
 * It contains all the data types declared in the application.
 */

import {declareRegistry} from '#/main/app/registry'

// declares a new registry to grab data types
const registry = declareRegistry('dataTypes')

export {
  registry
}
