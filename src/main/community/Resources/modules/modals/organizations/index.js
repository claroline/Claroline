/**
 * Organization picker modal.
 *
 * Displays the groups picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {OrganizationsModal} from '#/main/community/modals/organizations/containers/modal'

const MODAL_ORGANIZATIONS = 'MODAL_ORGANIZATIONS'

// make the modal available for use
registry.add(MODAL_ORGANIZATIONS, OrganizationsModal)

export {
  MODAL_ORGANIZATIONS
}
