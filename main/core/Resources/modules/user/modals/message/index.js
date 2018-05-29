/**
 * User message modal.
 * Displays a form to send a message to users.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {MessageModal} from '#/main/core/user/modals/message/components/message'

const MODAL_USER_MESSAGE = 'MODAL_USER_MESSAGE'

// make the modal available for use
registry.add(MODAL_USER_MESSAGE, MessageModal)

export {
  MODAL_USER_MESSAGE
}
