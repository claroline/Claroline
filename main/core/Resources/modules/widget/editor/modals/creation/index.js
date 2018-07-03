/**
 * Widget Creation modal.
 *
 * The creation is split into two modals :
 *   - A first one, where the user select the layout of the widget (aka number of columns)
 *   - A second one, where the user can configure the widget
 *
 * NB. Only the first modal is public to be sure widget creation
 * always follow the same process
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {LayoutModal} from '#/main/core/widget/editor/modals/creation/components/layout'

// internal modals
import {ParametersModal, MODAL_WIDGET_CREATION_PARAMETERS} from '#/main/core/widget/editor/modals/creation/components/parameters'

const MODAL_WIDGET_CREATION = 'MODAL_WIDGET_CREATION'

// make the modal available for use
registry.add(MODAL_WIDGET_CREATION, LayoutModal)
registry.add(MODAL_WIDGET_CREATION_PARAMETERS, ParametersModal)

export {
  MODAL_WIDGET_CREATION
}
