/**
 * Widget Creation modal.
 *
 * The creation is split into two modals :
 *   - A first one, where the user select the layout of the widget (aka number of columns)
 *   - A second one, where the user can configure the widget
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {WidgetCreationModal} from '#/main/core/widget/editor/modals/creation/containers/modal'

const MODAL_WIDGET_CREATION = 'MODAL_WIDGET_CREATION'

// make the modal available for use
registry.add(MODAL_WIDGET_CREATION, WidgetCreationModal)

export {
  MODAL_WIDGET_CREATION
}
