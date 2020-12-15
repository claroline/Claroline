/**
 * Agenda Parameters modal.
 * Displays a form to configure the messaging tool.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/plugin/agenda/tools/agenda/modals/parameters/containers/modal'

const MODAL_AGENDA_PARAMETERS = 'MODAL_AGENDA_PARAMETERS'

// make the modal available for use
registry.add(MODAL_AGENDA_PARAMETERS, ParametersModal)

export {
  MODAL_AGENDA_PARAMETERS
}
