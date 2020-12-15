/**
 * Events Parameters modal.
 * Displays a form to configure an Event.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/plugin/agenda/event/modals/parameters/containers/modal'

const MODAL_EVENT_PARAMETERS = 'MODAL_EVENT_PARAMETERS'

// make the modal available for use
registry.add(MODAL_EVENT_PARAMETERS, ParametersModal)

export {
  MODAL_EVENT_PARAMETERS
}
