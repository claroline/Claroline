/**
 * List viewers for a content.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ViewersModal} from '#/main/app/modals/viewers/components/modal'

const MODAL_VIEWERS = 'MODAL_VIEWERS'

// make the modal available for use
registry.add(MODAL_VIEWERS, ViewersModal)

export {
  MODAL_VIEWERS
}
