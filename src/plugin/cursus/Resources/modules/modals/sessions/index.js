/**
 * Courses picker modal.
 *
 * Displays the courses picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {SessionsModal} from '#/plugin/cursus/modals/sessions/containers/modal'

const MODAL_SESSIONS = 'MODAL_SESSIONS'

// make the modal available for use
registry.add(MODAL_SESSIONS, SessionsModal)

export {
  MODAL_SESSIONS
}
