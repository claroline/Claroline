/**
 * Role About modal.
 * Displays general information about the role.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AboutModal} from '#/main/community/role/modals/about/containers/modal'

const MODAL_ROLE_ABOUT = 'MODAL_ROLE_ABOUT'

// make the modal available for use
registry.add(MODAL_ROLE_ABOUT, AboutModal)

export {
  MODAL_ROLE_ABOUT
}
