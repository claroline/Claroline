/**
 * Documentation modal.
 * Displays the list of available documentations.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {DocumentationModal} from '#/plugin/documentation/modals/documentation/containers/modal'

const MODAL_DOCUMENTATION = 'MODAL_DOCUMENTATION'

// make the modal available for use
registry.add(MODAL_DOCUMENTATION, DocumentationModal)

export {
  MODAL_DOCUMENTATION
}
