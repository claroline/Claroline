/**
 * Example parameters modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/main/example/tools/example/modals/parameters/containers/modal'

const MODAL_EXAMPLE_PARAMETERS = 'MODAL_EXAMPLE_PARAMETERS'

// make the modal available for use
registry.add(MODAL_EXAMPLE_PARAMETERS, ParametersModal)

export {
  MODAL_EXAMPLE_PARAMETERS
}
