/**
 * Users/Groups picker modal.
 *
 * Displays the users/groups picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {RegisterModal} from '#/main/community/modals/register/containers/modal'

const MODAL_REGISTER = 'MODAL_REGISTER'

// make the modal available for use
registry.add(MODAL_REGISTER, RegisterModal)

export {
  MODAL_REGISTER
}
