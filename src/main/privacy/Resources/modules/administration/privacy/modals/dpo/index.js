/**
 * DPO modal.
 *
 * Displays a form to modify the dpo information.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {DpoModal} from '#/main/privacy/administration/privacy/modals/dpo/components/modal'

const MODAL_DPO = 'MODAL_DPO'

// make the modal available for use
registry.add(MODAL_DPO, DpoModal)

export {
  MODAL_DPO
}
