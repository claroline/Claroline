/**
 * Roles picker modal.
 *
 * Displays the groups picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {RolesModal} from '#/main/community/modals/roles/containers/modal'

const MODAL_ROLES = 'MODAL_ROLES'

// make the modal available for use
registry.add(MODAL_ROLES, RolesModal)

export {
  MODAL_ROLES
}
