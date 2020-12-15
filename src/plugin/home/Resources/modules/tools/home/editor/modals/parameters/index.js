/**
 * Home Tab Parameters modal.
 * Displays a form to configure a home tab.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/plugin/home/tools/home/editor/modals/parameters/containers/modal'

const MODAL_HOME_PARAMETERS = 'MODAL_HOME_PARAMETERS'

// make the modal available for use
registry.add(MODAL_HOME_PARAMETERS, ParametersModal)

export {
  MODAL_HOME_PARAMETERS
}
