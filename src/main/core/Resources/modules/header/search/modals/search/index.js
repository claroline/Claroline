/**
 * Resource Parameters modal.
 * Displays a form to configure the resource.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {SearchModal} from '#/main/core/header/search/modals/search/containers/modal'

const MODAL_HEADER_SEARCH = 'MODAL_HEADER_SEARCH'

// make the modal available for use
registry.add(MODAL_HEADER_SEARCH, SearchModal)

export {
  MODAL_HEADER_SEARCH
}
