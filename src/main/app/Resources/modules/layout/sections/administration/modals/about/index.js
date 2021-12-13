/**
 * Platform About modal.
 * Displays information about current Claroline version
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AboutModal} from '#/main/app/layout/sections/administration/modals/about/containers/modal'

const MODAL_PLATFORM_ABOUT = 'MODAL_PLATFORM_ABOUT'

// make the modal available for use
registry.add(MODAL_PLATFORM_ABOUT, AboutModal)

export {
  MODAL_PLATFORM_ABOUT
}
