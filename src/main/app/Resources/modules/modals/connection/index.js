/**
 * Connection modal.
 * Displays platform messages to the logged user.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ConnectionModal} from '#/main/app/modals/connection/containers/modal'

const MODAL_CONNECTION = 'MODAL_CONNECTION'

// make the modal available for use
registry.add(MODAL_CONNECTION, ConnectionModal)

export {
  MODAL_CONNECTION
}
