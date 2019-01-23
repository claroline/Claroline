/**
 * Organizations picker modal.
 *
 * Displays the organizations picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {OrganizationsModal} from '#/main/core/modals/organizations/containers/modal'

const MODAL_ORGANIZATIONS_PICKER = 'MODAL_ORGANIZATIONS_PICKER'

// make the modal available for use
registry.add(MODAL_ORGANIZATIONS_PICKER, OrganizationsModal)

export {
  MODAL_ORGANIZATIONS_PICKER
}
