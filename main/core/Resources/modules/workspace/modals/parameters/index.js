/**
 * Workspace Parameters modal.
 * Displays a form to configure the workspace.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/main/core/workspace/modals/parameters/containers/modal'

const MODAL_WORKSPACE_PARAMETERS = 'MODAL_WORKSPACE_PARAMETERS'

// make the modal available for use
registry.add(MODAL_WORKSPACE_PARAMETERS, ParametersModal)

export {
  MODAL_WORKSPACE_PARAMETERS
}
