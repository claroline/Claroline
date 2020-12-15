/**
 * Selection modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {SelectionModal} from '#/main/app/modals/selection/components/modal'

const MODAL_SELECTION = 'MODAL_SELECTION'

// make the modal available for use
registry.add(MODAL_SELECTION, SelectionModal)

export {
  MODAL_SELECTION
}
