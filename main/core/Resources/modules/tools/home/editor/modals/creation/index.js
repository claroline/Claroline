/**
 * Tab creation modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CreateTabModal} from '#/main/core/tools/home/editor/modals/creation/components/creation'

const MODAL_TAB_CREATE = 'MODAL_TAB_CREATE'

// make the modal available for use
registry.add(MODAL_TAB_CREATE, CreateTabModal)

export {
  MODAL_TAB_CREATE
}
