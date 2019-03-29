/**
 * Import items modal.
 *
 * Permits to import questions from the bank into the current step.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ImportModal} from '#/plugin/exo/items/modals/import/containers/modal'

const MODAL_ITEM_IMPORT = 'MODAL_QUIZ_ITEM_IMPORT'

// make the modal available for use
registry.add(MODAL_ITEM_IMPORT, ImportModal)

export {
  MODAL_ITEM_IMPORT
}
