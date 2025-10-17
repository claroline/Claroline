/**
 * Location form modal.
 * Displays a form to create/configure a location.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {LocationFormModal} from '#/main/core/tools/locations/modals/form/components/modal'

const MODAL_LOCATION_FORM = 'MODAL_LOCATION_FORM'

// make the modal available for use
registry.add(MODAL_LOCATION_FORM, LocationFormModal)

export {
  MODAL_LOCATION_FORM
}
