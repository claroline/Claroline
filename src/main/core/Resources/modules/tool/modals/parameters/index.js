/**
 * Tool Parameters modal.
 * Displays a form to configure the tool.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/main/core/tool/modals/parameters/containers/modal'

const MODAL_TOOL_PARAMETERS = 'MODAL_TOOL_PARAMETERS'

// make the modal available for use
registry.add(MODAL_TOOL_PARAMETERS, ParametersModal)

export {
  MODAL_TOOL_PARAMETERS
}
