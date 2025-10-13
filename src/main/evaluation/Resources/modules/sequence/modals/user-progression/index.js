/**
 * Displays the progression of a user in the sequence.
 */

import {registry} from '#/main/app/modals/registry'

import {UserProgressionModal} from '#/main/evaluation/sequence/modals/user-progression/components/modal'
import {MODAL_SEQUENCE_USER_PROGRESSION} from '#/main/evaluation/sequence/modals/user-progression/constants'

registry.add(MODAL_SEQUENCE_USER_PROGRESSION, UserProgressionModal)

export {
  MODAL_SEQUENCE_USER_PROGRESSION
}
