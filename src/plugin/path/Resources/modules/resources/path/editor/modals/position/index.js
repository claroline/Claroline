/**
 * Step position modal.
 *
 * Permits to choose a step position in the path (for copy or move).
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {PositionModal} from '#/plugin/path/resources/path/editor/modals/position/containers/modal'

const MODAL_STEP_POSITION = 'MODAL_PATH_STEP_POSITION'

// make the modal available for use
registry.add(MODAL_STEP_POSITION, PositionModal)

export {
  MODAL_STEP_POSITION
}
