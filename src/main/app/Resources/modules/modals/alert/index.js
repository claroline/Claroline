/**
 * Message modal.
 * Displays a modal to alert the user.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AlertModal} from '#/main/app/modals/alert/components/alert'

const MODAL_ALERT = 'MODAL_ALERT'

// make the modal available for use
registry.add(MODAL_ALERT, AlertModal)

export {
  MODAL_ALERT
}
