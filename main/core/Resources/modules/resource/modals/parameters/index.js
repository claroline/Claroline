/**
 * Resource Parameters modal.
 * Displays a form to configure the resource.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/main/core/resource/modals/parameters/containers/modal'

const MODAL_RESOURCE_PARAMETERS = 'MODAL_RESOURCE_PARAMETERS'

// make the modal available for use
registry.add(MODAL_RESOURCE_PARAMETERS, ParametersModal)

export {
  MODAL_RESOURCE_PARAMETERS
}
