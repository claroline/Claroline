/**
 * Resource Explorer modal.
 *
 * Displays the resources explorer inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ResourcesModal} from '#/main/core/modals/resources/containers/modal'

const MODAL_RESOURCES = 'MODAL_RESOURCES'

// make the modal available for use
registry.add(MODAL_RESOURCES, ResourcesModal)

export {
  MODAL_RESOURCES
}
