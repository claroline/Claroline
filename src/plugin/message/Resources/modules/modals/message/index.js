/**
 * Message modal.
 * Displays a form to send a message to users.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {MessageModal} from '#/plugin/message/modals/message/containers/message'

const MODAL_MESSAGE = 'MODAL_MESSAGE'

// make the modal available for use
registry.add(MODAL_MESSAGE, MessageModal)

export {
  MODAL_MESSAGE
}
