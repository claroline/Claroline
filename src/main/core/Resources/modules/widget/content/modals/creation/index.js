/**
 * Widget Content creation modal.
 *
 * The creation is split into two modals :
 *   - A first one, where the user select the type of the content
 *   - A second one, where the user can configure the content
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ContentCreationModal} from '#/main/core/widget/content/modals/creation/containers/modal'

const MODAL_WIDGET_CONTENT = 'MODAL_WIDGET_CONTENT'

// make the modal available for use
registry.add(MODAL_WIDGET_CONTENT, ContentCreationModal)

export {
  MODAL_WIDGET_CONTENT
}
