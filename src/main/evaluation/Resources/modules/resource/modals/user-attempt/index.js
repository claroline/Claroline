/**
 * Displays an attempt of a user for a resource.
 */

import {registry} from '#/main/app/modals/registry'

import {UserAttemptModal} from '#/main/evaluation/resource/modals/user-attempt/components/modal'

const MODAL_RESOURCE_USER_ATTEMPT = 'MODAL_RESOURCE_USER_ATTEMPT'

registry.add(MODAL_RESOURCE_USER_ATTEMPT, UserAttemptModal)

export {
  MODAL_RESOURCE_USER_ATTEMPT
}
