/**
 * Buttons registry.
 *
 * It contains all the buttons declared in the application.
 *
 * NB. A button MUST be registered in the registry before you can use it through the `main/app/action` module
 * or the `GenericButton` component.
 */

import {declareRegistry} from '#/main/app/registry'

// declares a new registry to grab modals
const registry = declareRegistry('buttons')

export {
  registry
}
