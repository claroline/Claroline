/**
 * workspaces picker modal.
 *
 * Displays the workspaces picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {WorkspacesPickerModal} from '#/main/core/modals/workspaces/containers/modal'

const MODAL_WORKSPACES_PICKER = 'MODAL_WORKSPACES_PICKER'

// make the modal available for use
registry.add(MODAL_WORKSPACES_PICKER, WorkspacesPickerModal)

export {
  MODAL_WORKSPACES_PICKER
}
