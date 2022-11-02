/**
 * Users picker modal.
 *
 * Displays the users picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {UsersModal} from '#/main/community/modals/users/containers/modal'

const MODAL_USERS = 'MODAL_USERS'

// make the modal available for use
registry.add(MODAL_USERS, UsersModal)

export {
  MODAL_USERS
}
