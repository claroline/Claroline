/**
 * Workspace Roles modal.
 * Displays the list of roles with access to the Workspace.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {RolesModal} from '#/main/core/workspace/modals/roles/components/modal'

/**
 * @deprecated use standard MODAL_ROLES
 */
const MODAL_WORKSPACE_ROLES = 'MODAL_WORKSPACE_ROLES'

// make the modal available for use
registry.add(MODAL_WORKSPACE_ROLES, RolesModal)

export {
  MODAL_WORKSPACE_ROLES
}
