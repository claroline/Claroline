/**
 * Organization About modal.
 * Displays general information about the organization.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AboutModal} from '#/main/community/organization/modals/about/containers/modal'

const MODAL_ORGANIZATION_ABOUT = 'MODAL_ORGANIZATION_ABOUT'

// make the modal available for use
registry.add(MODAL_ORGANIZATION_ABOUT, AboutModal)

export {
  MODAL_ORGANIZATION_ABOUT
}
