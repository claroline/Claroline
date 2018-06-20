/**
 * Workspace About modal.
 * Displays general information about the workspace.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {EventModal} from '#/plugin/agenda/components/modal/components/event'

const MODAL_EVENT = 'MODAL_EVENT'

// make the modal available for use
registry.add(MODAL_EVENT, EventModal)

export {
  MODAL_EVENT
}
