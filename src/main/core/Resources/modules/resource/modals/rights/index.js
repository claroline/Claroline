/**
 * Resource rights modal.
 * Displays a form to configure the resource rights.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {RightsModal} from '#/main/core/resource/modals/rights/containers/modal'

const MODAL_RESOURCE_RIGHTS = 'MODAL_RESOURCE_RIGHTS'

// make the modal available for use
registry.add(MODAL_RESOURCE_RIGHTS, RightsModal)

export {
  MODAL_RESOURCE_RIGHTS
}
