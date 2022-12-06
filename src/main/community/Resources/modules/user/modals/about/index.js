/**
 * User About modal.
 * Displays general information about the user.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AboutModal} from '#/main/community/user/modals/about/containers/modal'

const MODAL_USER_ABOUT = 'MODAL_USER_ABOUT'

// make the modal available for use
registry.add(MODAL_USER_ABOUT, AboutModal)

export {
  MODAL_USER_ABOUT
}
