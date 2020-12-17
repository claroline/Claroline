/**
 * Templates picker modal.
 *
 * Displays the templates picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {TemplatesModal} from '#/main/core/modals/templates/containers/modal'

const MODAL_TEMPLATES = 'MODAL_TEMPLATES'

// make the modal available for use
registry.add(MODAL_TEMPLATES, TemplatesModal)

export {
  MODAL_TEMPLATES
}
