/**
 * Resource About modal.
 * Displays general information about the resource.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AboutModal} from '#/main/core/resource/modals/about/components/about'

const MODAL_RESOURCE_ABOUT = 'MODAL_RESOURCE_ABOUT'

// make the modal available for use
registry.add(MODAL_RESOURCE_ABOUT, AboutModal)

export {
  MODAL_RESOURCE_ABOUT
}
