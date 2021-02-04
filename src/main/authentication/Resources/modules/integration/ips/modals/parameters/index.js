/**
 * IP Parameters modal.
 * Displays a form to configure an IP.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/main/authentication/integration/ips/modals/parameters/containers/modal'

const MODAL_IP_PARAMETERS = 'MODAL_IP_PARAMETERS'

// make the modal available for use
registry.add(MODAL_IP_PARAMETERS, ParametersModal)

export {
  MODAL_IP_PARAMETERS
}
