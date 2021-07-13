/**
 * Material booking form modal.
 * Displays a form to book a material.
 */

import {registry} from '#/main/app/modals/registry'

import {MaterialBookingModal} from '#/main/core/tools/locations/material/modals/booking/containers/modal'

const MODAL_MATERIAL_BOOKING = 'MODAL_MATERIAL_BOOKING'

registry.add(MODAL_MATERIAL_BOOKING, MaterialBookingModal)

export {
  MODAL_MATERIAL_BOOKING
}
