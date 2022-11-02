/**
 * Groups picker modal.
 *
 * Displays the groups picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {GroupsModal} from '#/main/community/modals/groups/containers/modal'

const MODAL_GROUPS = 'MODAL_GROUPS'

// make the modal available for use
registry.add(MODAL_GROUPS, GroupsModal)

export {
  MODAL_GROUPS
}
