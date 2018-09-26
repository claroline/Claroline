/**
 * Groups picker modal.
 *
 * Displays the groups picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {GroupsPickerModal} from '#/main/core/modals/groups/containers/modal'

const MODAL_GROUPS_PICKER = 'MODAL_GROUPS_PICKER'

// make the modal available for use
registry.add(MODAL_GROUPS_PICKER, GroupsPickerModal)

export {
  MODAL_GROUPS_PICKER
}
