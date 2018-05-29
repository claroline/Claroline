/**
 * Modals registry.
 *
 * It contains all the modal declared in the application.
 *
 * NB. A modal MUST be registered in the registry before you can use it.
 */

import {declareRegistry} from '#/main/app/registry'

// declares a new registry to grab modals
const registry = declareRegistry('modals')

export {
  registry
}
