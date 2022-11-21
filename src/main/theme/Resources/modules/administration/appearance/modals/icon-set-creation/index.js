/**
 * Icon Set creation modal.
 * Displays a form to import an archive of icons.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {IconSetCreationModal} from '#/main/theme/administration/appearance/modals/icon-set-creation/containers/modal'

const MODAL_ICON_SET_CREATION = 'MODAL_ICON_SET_CREATION'

// make the modal available for use
registry.add(MODAL_ICON_SET_CREATION, IconSetCreationModal)

export {
  MODAL_ICON_SET_CREATION
}
