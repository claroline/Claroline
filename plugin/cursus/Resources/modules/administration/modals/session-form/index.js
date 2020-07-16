/**
 * Session form modal.
 * Displays a form to create/edit a course session.
 */

import {registry} from '#/main/app/modals/registry'

import {SessionFormModal} from '#/plugin/cursus/administration/modals/session-form/containers/modal'

const MODAL_SESSION_FORM = 'MODAL_SESSION_FORM'

registry.add(MODAL_SESSION_FORM, SessionFormModal)

export {
  MODAL_SESSION_FORM
}
