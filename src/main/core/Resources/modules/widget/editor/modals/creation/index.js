/**
 * Widget Creation modal.
 *
 * The user can select the layout of the widget (aka number of columns)
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {WidgetCreationModal} from '#/main/core/widget/editor/modals/creation/components/modal'

const MODAL_WIDGET_CREATION = 'MODAL_WIDGET_CREATION'

// make the modal available for use
registry.add(MODAL_WIDGET_CREATION, WidgetCreationModal)

export {
  MODAL_WIDGET_CREATION
}
