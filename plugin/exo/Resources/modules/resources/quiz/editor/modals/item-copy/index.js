/**
 * Item copy modal.
 *
 * Permits to create n copies of an item and choose their new position.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CopyModal} from '#/plugin/exo/resources/quiz/editor/modals/item-copy/components/modal'

const MODAL_ITEM_COPY = 'MODAL_QUIZ_ITEM_COPY'

// make the modal available for use
registry.add(MODAL_ITEM_COPY, CopyModal)

export {
  MODAL_ITEM_COPY
}
