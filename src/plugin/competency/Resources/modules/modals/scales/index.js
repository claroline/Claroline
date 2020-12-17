/**
 * Scales picker modal.
 *
 * Displays the scales picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ScalesPickerModal} from '#/plugin/competency/modals/scales/containers/modal'

const MODAL_COMPETENCY_SCALES_PICKER = 'MODAL_COMPETENCY_SCALES_PICKER'

// make the modal available for use
registry.add(MODAL_COMPETENCY_SCALES_PICKER, ScalesPickerModal)

export {
  MODAL_COMPETENCY_SCALES_PICKER
}
