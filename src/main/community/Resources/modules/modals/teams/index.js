/**
 * Teams picker modal.
 *
 * Displays the teams picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {TeamsModal} from '#/main/community/modals/teams/containers/modal'

const MODAL_TEAMS = 'MODAL_TEAMS'

// make the modal available for use
registry.add(MODAL_TEAMS, TeamsModal)

export {
  MODAL_TEAMS
}
