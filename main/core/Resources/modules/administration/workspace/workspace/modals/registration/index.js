/**
 * Workspace Parameters modal.
 * Displays a form to configure the workspace.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {RoleRegistrationModal} from '#/main/core/administration/workspace/workspace/modals/registration/containers/modal'

const MODAL_WORKSPACE_ROLES = 'MODAL_WORKSPACE_ROLES'

// make the modal available for use
registry.add(MODAL_WORKSPACE_ROLES, RoleRegistrationModal)

export {
  MODAL_WORKSPACE_ROLES
}
