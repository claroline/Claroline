/**
 * Registration modal.
 * Displays a modal to register to the platform.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {RegistrationModal} from '#/main/app/modals/registration/components/modal'

const MODAL_REGISTRATION = 'MODAL_REGISTRATION'

// make the modal available for use
registry.add(MODAL_REGISTRATION, RegistrationModal)

export {
  MODAL_REGISTRATION
}
