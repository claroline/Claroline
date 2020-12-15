/**
 * Step position modal.
 *
 * Permits to choose a step position in the quiz (for copy or move).
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {PositionModal} from '#/plugin/exo/resources/quiz/editor/modals/step-position/containers/modal'

const MODAL_STEP_POSITION = 'MODAL_QUIZ_STEP_POSITION'

// make the modal available for use
registry.add(MODAL_STEP_POSITION, PositionModal)

export {
  MODAL_STEP_POSITION
}
