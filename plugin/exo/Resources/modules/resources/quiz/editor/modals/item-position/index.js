/**
 * Item position modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {PositionModal} from '#/plugin/exo/resources/quiz/editor/modals/item-position/components/modal'

const MODAL_ITEM_POSITION = 'MODAL_QUIZ_ITEM_POSITION'

// make the modal available for use
registry.add(MODAL_ITEM_POSITION, PositionModal)

export {
  MODAL_ITEM_POSITION
}
