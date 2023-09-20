/**
 * Registration About modal.
 * Displays general information about a training registration.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AboutModal} from '#/plugin/cursus/registration/modals/about/components/modal'

const MODAL_REGISTRATION_ABOUT = 'MODAL_REGISTRATION_ABOUT'

// make the modal available for use
registry.add(MODAL_REGISTRATION_ABOUT, AboutModal)

export {
  MODAL_REGISTRATION_ABOUT
}
