/**
 * Workspace shortcuts picker modal.
 *
 * Displays the workspace shortcuts picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ShortcutsModal} from '#/main/core/workspace/modals/shortcuts/components/modal'

const MODAL_WORKSPACE_SHORTCUTS = 'MODAL_WORKSPACE_SHORTCUTS'

// make the modal available for use
registry.add(MODAL_WORKSPACE_SHORTCUTS, ShortcutsModal)

export {
  MODAL_WORKSPACE_SHORTCUTS
}
