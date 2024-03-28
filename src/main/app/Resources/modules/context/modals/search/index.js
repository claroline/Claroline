/**
 * Resource Parameters modal.
 * Displays a form to configure the resource.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {SearchModal} from '#/main/app/context/modals/search/containers/modal'

const MODAL_CONTEXT_SEARCH = 'MODAL_CONTEXT_SEARCH'

// make the modal available for use
registry.add(MODAL_CONTEXT_SEARCH, SearchModal)

export {
  MODAL_CONTEXT_SEARCH
}
