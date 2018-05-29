/**
 * Store registry.
 *
 * It contains all the reducers dynamically declared in the application.
 */

import {declareRegistry} from '#/main/app/registry'

// declares a new registry to grab reducers
const registry = declareRegistry('store')

export {
  registry
}
