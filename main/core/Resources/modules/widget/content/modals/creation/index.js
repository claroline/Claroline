/**
 * Widget Content creation modal.
 *
 * The creation is split into two modals :
 *   - A first one, where the user select the type of the content
 *   - A second one, where the user can configure the content
 *
 * NB. Only the first modal is public to be sure content creation
 * always follow the same process
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ContentTypeModal} from '#/main/core/widget/content/modals/creation/components/type'

// internal modals
import {ParametersModal, MODAL_WIDGET_CONTENT_PARAMETERS} from '#/main/core/widget/content/modals/creation/components/parameters'

const MODAL_WIDGET_CONTENT = 'MODAL_WIDGET_CONTENT'

// make the modal available for use
registry.add(MODAL_WIDGET_CONTENT, ContentTypeModal)
registry.add(MODAL_WIDGET_CONTENT_PARAMETERS, ParametersModal)

export {
  MODAL_WIDGET_CONTENT
}
