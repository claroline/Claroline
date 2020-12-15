/**
 * Widget Parameters modal.
 * Displays a form to configure a widget.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/main/core/widget/content/modals/parameters/containers/modal'

const MODAL_CONTENT_PARAMETERS = 'MODAL_CONTENT_PARAMETERS'

// make the modal available for use
registry.add(MODAL_CONTENT_PARAMETERS, ParametersModal)

export {
  MODAL_CONTENT_PARAMETERS
}
