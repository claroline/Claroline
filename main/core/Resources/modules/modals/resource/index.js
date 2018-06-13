/**
 * Resources picker.
 * Displays a modal to let the user select one or more resources.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ResourceModal} from '#/main/core/modals/resource/components/modal'

const MODAL_RESOURCE_PICKER = 'MODAL_RESOURCE_PICKER'

// make the modal available for use
registry.add(MODAL_RESOURCE_PICKER, ResourceModal)

export {
  MODAL_RESOURCE_PICKER
}
