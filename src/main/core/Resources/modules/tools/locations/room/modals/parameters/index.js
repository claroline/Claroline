/**
 * Room form modal.
 * Displays a form to configure a room.
 */

import {registry} from '#/main/app/modals/registry'

import {RoomParametersModal} from '#/main/core/tools/locations/room/modals/parameters/containers/modal'

const MODAL_ROOM_PARAMETERS = 'MODAL_ROOM_PARAMETERS'

registry.add(MODAL_ROOM_PARAMETERS, RoomParametersModal)

export {
  MODAL_ROOM_PARAMETERS
}
