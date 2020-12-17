/**
 * Quiz item creation modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CreationModal} from '#/plugin/exo/items/modals/creation/components/modal'

const MODAL_ITEM_CREATION = 'MODAL_QUIZ_ITEM_CREATION'

// make the modal available for use
registry.add(MODAL_ITEM_CREATION, CreationModal)

export {
  MODAL_ITEM_CREATION
}
