/**
 * Modal button.
 * Opens a registered modal.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

const MODAL_BUTTON = 'modal'

// make the button available for use
registry.add(MODAL_BUTTON, ModalButton)

export {
  MODAL_BUTTON,
  ModalButton
}
