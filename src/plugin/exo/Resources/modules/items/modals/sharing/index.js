/**
 * Quiz item sharing modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {SharingModal} from '#/plugin/exo/items/modals/sharing/components/modal'

const MODAL_ITEM_SHARING = 'MODAL_QUIZ_ITEM_SHARING'

// make the modal available for use
registry.add(MODAL_ITEM_SHARING, SharingModal)

export {
  MODAL_ITEM_SHARING
}
