/**
 * Card form modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CardModal} from '#/plugin/flashcard/resources/flashcard/editor/modals/card/components/card-modal.jsx'

const MODAL_CARD = 'MODAL_CARD'

// make the modal available for use
registry.add(MODAL_CARD, CardModal)

export {
  MODAL_CARD
}
