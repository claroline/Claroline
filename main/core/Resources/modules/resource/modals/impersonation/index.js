/**
 * Resource Impersonation modal.
 * Lets the current user use another role which can access the resource.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ImpersonationModal} from '#/main/core/resource/modals/impersonation/components/impersonation'

const MODAL_RESOURCE_IMPERSONATION = 'MODAL_RESOURCE_IMPERSONATION'

// make the modal available for use
registry.add(MODAL_RESOURCE_IMPERSONATION, ImpersonationModal)

export {
  MODAL_RESOURCE_IMPERSONATION
}
