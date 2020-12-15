/**
 * workspaces picker modal.
 *
 * Displays the workspaces picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {WorkspacesModal} from '#/main/core/modals/workspaces/containers/modal'

const MODAL_WORKSPACES = 'MODAL_WORKSPACES'

// make the modal available for use
registry.add(MODAL_WORKSPACES, WorkspacesModal)

export {
  MODAL_WORKSPACES
}
