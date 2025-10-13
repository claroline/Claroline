/**
 * Displays an attempt of a user for a resource.
 */

import {registry} from '#/main/app/modals/registry'

import {UserAttemptModal} from '#/main/evaluation/resource/modals/user-attempt/components/modal'
import {MODAL_RESOURCE_USER_ATTEMPT} from '#/main/evaluation/resource/modals/user-attempt/constants'

registry.add(MODAL_RESOURCE_USER_ATTEMPT, UserAttemptModal)

export {
  MODAL_RESOURCE_USER_ATTEMPT
}
