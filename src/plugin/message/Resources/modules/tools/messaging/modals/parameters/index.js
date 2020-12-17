/**
 * Messaging Parameters modal.
 * Displays a form to configure the messaging tool.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/plugin/message/tools/messaging/modals/parameters/containers/modal'

const MODAL_MESSAGING_PARAMETERS = 'MODAL_MESSAGING_PARAMETERS'

// make the modal available for use
registry.add(MODAL_MESSAGING_PARAMETERS, ParametersModal)

export {
  MODAL_MESSAGING_PARAMETERS
}
