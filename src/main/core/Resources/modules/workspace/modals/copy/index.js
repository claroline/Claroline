/**
 * Workspace Copy modal.
 * Displays a confirm modal when copying some workspaces.
 * It allows the user to choose if he want to copy the workspaces as model or not.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CopyModal} from '#/main/core/workspace/modals/copy/components/modal'

const MODAL_WORKSPACE_COPY = 'MODAL_WORKSPACE_COPY'

// make the modal available for use
registry.add(MODAL_WORKSPACE_COPY, CopyModal)

export {
  MODAL_WORKSPACE_COPY
}
