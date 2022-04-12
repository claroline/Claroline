/**
 * Workspace Import modal.
 * Displays a form to import a workspace archive.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ImportModal} from '#/main/core/workspace/modals/import/containers/modal'

const MODAL_WORKSPACE_IMPORT = 'MODAL_WORKSPACE_IMPORT'

// make the modal available for use
registry.add(MODAL_WORKSPACE_IMPORT, ImportModal)

export {
  MODAL_WORKSPACE_IMPORT
}
