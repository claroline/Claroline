/**
 * Session cancel modal.
 */

import {registry} from '#/main/app/modals/registry'

import {SessionCancelModal} from '#/plugin/cursus/session/modals/cancel/containers/modal'

const MODAL_SESSION_CANCEL = 'MODAL_SESSION_CANCEL'

registry.add(MODAL_SESSION_CANCEL, SessionCancelModal)

export {
  MODAL_SESSION_CANCEL
}
