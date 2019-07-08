/**
 * Template types picker modal.
 *
 * Displays the template types picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {TemplateTypesModal} from '#/main/core/modals/template-types/containers/modal'

const MODAL_TEMPLATE_TYPES = 'MODAL_TEMPLATE_TYPES'

// make the modal available for use
registry.add(MODAL_TEMPLATE_TYPES, TemplateTypesModal)

export {
  MODAL_TEMPLATE_TYPES
}
