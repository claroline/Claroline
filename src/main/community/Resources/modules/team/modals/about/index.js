/**
 * Team About modal.
 * Displays general information about the team.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AboutModal} from '#/main/community/team/modals/about/containers/modal'

const MODAL_TEAM_ABOUT = 'MODAL_TEAM_ABOUT'

// make the modal available for use
registry.add(MODAL_TEAM_ABOUT, AboutModal)

export {
  MODAL_TEAM_ABOUT
}
