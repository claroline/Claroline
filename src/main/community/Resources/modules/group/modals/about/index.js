/**
 * Group About modal.
 * Displays general information about the group.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AboutModal} from '#/main/community/group/modals/about/containers/modal'

const MODAL_GROUP_ABOUT = 'MODAL_GROUP_ABOUT'

// make the modal available for use
registry.add(MODAL_GROUP_ABOUT, AboutModal)

export {
  MODAL_GROUP_ABOUT
}
