/**
 * Users picker modal.
 *
 * Displays the users picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AboutModal} from '#/main/example/tools/example/modals/about/components/modal'

const MODAL_EXAMPLE_ABOUT = 'MODAL_EXAMPLE_ABOUT'

// make the modal available for use
registry.add(MODAL_EXAMPLE_ABOUT, AboutModal)

export {
  MODAL_EXAMPLE_ABOUT
}
