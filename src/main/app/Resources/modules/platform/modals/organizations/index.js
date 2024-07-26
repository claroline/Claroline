/**
 * Organizations modal.
 * It displays the user's organization to allow him to switch from organization to another.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {OrganizationsModal} from '#/main/app/platform/modals/organizations/components/modal'

const MODAL_PLATFORM_ORGANIZATIONS = 'MODAL_PLATFORM_ORGANIZATIONS'

// make the modal available for use
registry.add(MODAL_PLATFORM_ORGANIZATIONS, OrganizationsModal)

export {
  MODAL_PLATFORM_ORGANIZATIONS
}
