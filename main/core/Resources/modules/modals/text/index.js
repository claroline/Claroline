/**
 * Groups picker modal.
 *
 * Displays the groups picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {TextModal} from '#/main/core/modals/text/containers/modal'

const MODAL_TEXT_SEARCH = 'MODAL_TEXT_SEARCH'

// make the modal available for use
registry.add(MODAL_TEXT_SEARCH, TextModal)

export {
  MODAL_TEXT_SEARCH
}
