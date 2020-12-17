/**
 * locations picker modal.
 *
 * Displays the locations picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {LocationsModal} from '#/main/core/modals/locations/containers/modal'

const MODAL_LOCATIONS = 'MODAL_LOCATIONS'

// make the modal available for use
registry.add(MODAL_LOCATIONS, LocationsModal)

export {
  MODAL_LOCATIONS
}
