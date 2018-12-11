/**
 * Workspace Impersonation modal.
 * Lets the current user use another workspace role.
 *
 * @deprecated
 * @todo remove me when toolbar will use standard WS actions.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ImpersonationModal} from '#/main/core/workspace/modals/impersonation/components/impersonation'

const MODAL_WORKSPACE_IMPERSONATION = 'MODAL_WORKSPACE_IMPERSONATION'

// make the modal available for use
registry.add(MODAL_WORKSPACE_IMPERSONATION, ImpersonationModal)

export {
  MODAL_WORKSPACE_IMPERSONATION
}
