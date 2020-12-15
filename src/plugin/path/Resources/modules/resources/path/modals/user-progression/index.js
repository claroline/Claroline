/**
 * User progression in path modal.
 * Displays the progression of an user in the path.
 */

import {registry} from '#/main/app/modals/registry'

import {UserProgressionModal} from '#/plugin/path/resources/path/modals/user-progression/containers/modal'

const MODAL_USER_PROGRESSION = 'MODAL_PATH_USER_PROGRESSION'

registry.add(MODAL_USER_PROGRESSION, UserProgressionModal)

export {
  MODAL_USER_PROGRESSION
}
