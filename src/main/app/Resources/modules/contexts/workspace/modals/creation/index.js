/**
 * Workspace creation modal.
 * Displays a modal to alert the user.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CreationModal} from '#/main/app/contexts/workspace/modals/creation/containers/modal'

const MODAL_WORKSPACE_CREATION = 'MODAL_WORKSPACE_CREATION'

// make the modal available for use
registry.add(MODAL_WORKSPACE_CREATION, CreationModal)

export {
  MODAL_WORKSPACE_CREATION
}
