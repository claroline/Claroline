/**
 * locations picker modal.
 *
 * Displays the locations picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {LocationsPickerModal} from '#/main/core/modals/locations/containers/modal'

const MODAL_LOCATIONS_PICKER = 'MODAL_LOCATIONS_PICKER'

// make the modal available for use
registry.add(MODAL_LOCATIONS_PICKER, LocationsPickerModal)

export {
  MODAL_LOCATIONS_PICKER
}
