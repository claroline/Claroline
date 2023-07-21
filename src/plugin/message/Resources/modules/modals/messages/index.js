/**
 * Messages modal.
 * Displays the unread messages of the current user.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {MessagesModal} from '#/plugin/message/modals/messages/containers/modal'

const MODAL_MESSAGES = 'MODAL_MESSAGES'

// make the modal available for use
registry.add(MODAL_MESSAGES, MessagesModal)

export {
  MODAL_MESSAGES
}
