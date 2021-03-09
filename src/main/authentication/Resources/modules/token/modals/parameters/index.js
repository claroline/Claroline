/**
 * Token Parameters modal.
 * Displays a form to configure an authentication token.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/main/authentication/token/modals/parameters/containers/modal'

const MODAL_TOKEN_PARAMETERS = 'MODAL_TOKEN_PARAMETERS'

// make the modal available for use
registry.add(MODAL_TOKEN_PARAMETERS, ParametersModal)

export {
  MODAL_TOKEN_PARAMETERS
}
