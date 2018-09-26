/**
 * Users picker modal.
 *
 * Displays the users picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {UsersPickerModal} from '#/main/core/modals/users/containers/modal'

const MODAL_USERS_PICKER = 'MODAL_USERS_PICKER'

// make the modal available for use
registry.add(MODAL_USERS_PICKER, UsersPickerModal)

export {
  MODAL_USERS_PICKER
}
