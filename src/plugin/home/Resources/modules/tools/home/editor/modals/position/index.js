/**
 * Home tab position modal.
 *
 * Permits to choose a tab position.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {PositionModal} from '#/plugin/home/tools/home/editor/modals/position/containers/modal'

const MODAL_HOME_POSITION = 'MODAL_HOME_POSITION'

// make the modal available for use
registry.add(MODAL_HOME_POSITION, PositionModal)

export {
  MODAL_HOME_POSITION
}
