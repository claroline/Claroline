/**
 * Login modal.
 * Displays a modal to login in the platform.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {LoginModal} from '#/main/app/modals/login/components/modal'

const MODAL_LOGIN = 'MODAL_LOGIN'

// make the modal available for use
registry.add(MODAL_LOGIN, LoginModal)

export {
  MODAL_LOGIN
}
