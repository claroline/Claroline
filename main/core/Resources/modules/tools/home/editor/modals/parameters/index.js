/**
 * Tab creation modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/main/core/tools/home/editor/modals/parameters/components/parameters'

const MODAL_TAB_PARAMETERS = 'MODAL_TAB_PARAMETERS'

// make the modal available for use
registry.add(MODAL_TAB_PARAMETERS, ParametersModal)

export {
  MODAL_TAB_PARAMETERS
}
