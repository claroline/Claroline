/**
 * Registration parameters modal.
 * Displays a modal to edit the custom registration form (if defined in the Course).
 * NB. This is only available for users registration (not groups).
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/plugin/cursus/registration/modals/parameters/containers/modal'

const MODAL_REGISTRATION_PARAMETERS = 'MODAL_REGISTRATION_PARAMETERS'

// make the modal available for use
registry.add(MODAL_REGISTRATION_PARAMETERS, ParametersModal)

export {
  MODAL_REGISTRATION_PARAMETERS
}
