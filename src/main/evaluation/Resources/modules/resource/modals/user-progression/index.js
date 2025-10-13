/**
 * Displays the progression of a user in the resource.
 */

import {registry} from '#/main/app/modals/registry'

import {UserProgressionModal} from '#/main/evaluation/resource/modals/user-progression/components/modal'
import {MODAL_RESOURCE_USER_PROGRESSION} from '#/main/evaluation/resource/modals/user-progression/constants'

registry.add(MODAL_RESOURCE_USER_PROGRESSION, UserProgressionModal)

export {
  MODAL_RESOURCE_USER_PROGRESSION
}
