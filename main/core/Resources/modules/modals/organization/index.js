/**
 * Organization picker modal.
 *
 * Displays the groups picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {OrganizationPickerModal} from '#/main/core/modals/organization/containers/modal'

const MODAL_ORGANIZATION_PICKER = 'MODAL_ORGANIZATION_PICKER'

// make the modal available for use
registry.add(MODAL_ORGANIZATION_PICKER, OrganizationPickerModal)

export {
  MODAL_ORGANIZATION_PICKER
}
