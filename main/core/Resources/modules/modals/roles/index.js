/**
 * Roles picker modal.
 *
 * Displays the groups picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {RolesPickerModal} from '#/main/core/modals/roles/containers/modal'

const MODAL_ROLES_PICKER = 'MODAL_ROLES_PICKER'

// make the modal available for use
registry.add(MODAL_ROLES_PICKER, RolesPickerModal)

export {
  MODAL_ROLES_PICKER
}
