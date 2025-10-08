/**
 * Page position modal.
 *
 * Permits choosing a page position in the lesson.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {PositionModal} from '#/plugin/lesson/resources/lesson/modals/position/containers/modal'

const MODAL_PAGE_POSITION = 'MODAL_PAGE_POSITION'

// make the modal available for use
registry.add(MODAL_PAGE_POSITION, PositionModal)

export {
  MODAL_PAGE_POSITION
}
