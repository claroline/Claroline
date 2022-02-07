/**
 * Participants modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParticipantsModal} from '#/plugin/path/analytics/workspace/path/modals/participants/containers/modal'

const MODAL_RESOURCE_PARTICIPANTS = 'MODAL_RESOURCE_PARTICIPANTS'

// make the modal available for use
registry.add(MODAL_RESOURCE_PARTICIPANTS, ParticipantsModal)

export {
  MODAL_RESOURCE_PARTICIPANTS
}
