/**
 * Room booking form modal.
 * Displays a form to book a room.
 */

import {registry} from '#/main/app/modals/registry'

import {RoomBookingModal} from '#/main/core/tools/locations/room/modals/booking/containers/modal'

const MODAL_ROOM_BOOKING = 'MODAL_ROOM_BOOKING'

registry.add(MODAL_ROOM_BOOKING, RoomBookingModal)

export {
  MODAL_ROOM_BOOKING
}
