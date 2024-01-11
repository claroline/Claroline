/**
 * Terms of service modal.
 *
 * Displays the platform terms of service.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {DpoModal} from '#/main/privacy/modals/dpo/containers/modal'

const MODAL_DPO = 'MODAL_DPO'

// make the modal available for use
registry.add(MODAL_DPO, DpoModal)

export {
  MODAL_DPO
}
