/**
 * Confirm modal.
 * Displays a modal to request a user confirmation.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ConfirmModal} from '#/main/app/modals/confirm/components/confirm'

const MODAL_CONFIRM = 'MODAL_CONFIRM'

// make the modal available for use
registry.add(MODAL_CONFIRM, ConfirmModal)

export {
  MODAL_CONFIRM
}
