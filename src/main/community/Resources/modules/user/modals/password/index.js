/**
 * User password modal.
 * Displays a form to change the user password.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {PasswordModal} from '#/main/community/user/modals/password/containers/password'

const MODAL_USER_PASSWORD = 'MODAL_USER_PASSWORD'

// make the modal available for use
registry.add(MODAL_USER_PASSWORD, PasswordModal)

export {
  MODAL_USER_PASSWORD
}
