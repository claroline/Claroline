/**
 * Workspace About modal.
 * Displays general information about the workspace.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AboutModal} from '#/main/core/workspace/modals/about/containers/modal'

const MODAL_WORKSPACE_ABOUT = 'MODAL_WORKSPACE_ABOUT'

// make the modal available for use
registry.add(MODAL_WORKSPACE_ABOUT, AboutModal)

export {
  MODAL_WORKSPACE_ABOUT
}
