/**
 * Connection messages picker modal.
 *
 * Displays the connection messages picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ConnectionMessagesModal} from '#/main/core/modals/connection-messages/containers/modal'

const MODAL_CONNECTION_MESSAGES = 'MODAL_CONNECTION_MESSAGES'

// make the modal available for use
registry.add(MODAL_CONNECTION_MESSAGES, ConnectionMessagesModal)

export {
  MODAL_CONNECTION_MESSAGES
}
