/**
 * Widget Parameters modal.
 * Displays a form to configure a widget.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/main/core/widget/editor/modals/parameters/components/parameters'

const MODAL_WIDGET_PARAMETERS = 'MODAL_WIDGET_PARAMETERS'

// make the modal available for use
registry.add(MODAL_WIDGET_PARAMETERS, ParametersModal)

export {
  MODAL_WIDGET_PARAMETERS
}
