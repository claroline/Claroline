/**
 * Competencies picker modal.
 *
 * Displays the competencies picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CompetenciesPickerModal} from '#/plugin/competency/modals/competencies/containers/modal'

const MODAL_COMPETENCIES_PICKER = 'MODAL_COMPETENCIES_PICKER'

// make the modal available for use
registry.add(MODAL_COMPETENCIES_PICKER, CompetenciesPickerModal)

export {
  MODAL_COMPETENCIES_PICKER
}
