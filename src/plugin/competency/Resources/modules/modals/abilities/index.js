/**
 * Abilities picker modal.
 *
 * Displays the abilities picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AbilitiesPickerModal} from '#/plugin/competency/modals/abilities/containers/modal'

const MODAL_ABILITIES_PICKER = 'MODAL_ABILITIES_PICKER'

// make the modal available for use
registry.add(MODAL_ABILITIES_PICKER, AbilitiesPickerModal)

export {
  MODAL_ABILITIES_PICKER
}
