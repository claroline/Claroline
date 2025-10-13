/**
 * Displays the progression of a user in the workspace.
 */

import {registry} from '#/main/app/modals/registry'

import {UserProgressionModal} from '#/main/evaluation/workspace/modals/user-progression/components/modal'
import {MODAL_WORKSPACE_USER_PROGRESSION} from '#/main/evaluation/workspace/modals/user-progression/constants'

registry.add(MODAL_WORKSPACE_USER_PROGRESSION, UserProgressionModal)

export {
  MODAL_WORKSPACE_USER_PROGRESSION
}
