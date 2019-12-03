/**
 * Maintenance modal.
 * Displays a confirm modal to allow configure maintenance message before enabling it.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {MaintenanceModal} from '#/main/app/modals/maintenance/containers/modal'

const MODAL_MAINTENANCE = 'MODAL_MAINTENANCE'

// make the modal available for use
registry.add(MODAL_MAINTENANCE, MaintenanceModal)

export {
  MODAL_MAINTENANCE
}
