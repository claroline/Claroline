/**
 * Rooms picker modal.
 *
 * Displays the rooms picker inside a modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {RoomsModal} from '#/main/core/modals/rooms/containers/modal'

const MODAL_ROOMS = 'MODAL_ROOMS'

// make the modal available for use
registry.add(MODAL_ROOMS, RoomsModal)

export {
  MODAL_ROOMS
}
