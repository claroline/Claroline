/**
 * Session event form modal.
 * Displays a form to create a session event.
 */

import {registry} from '#/main/app/modals/registry'

import {SessionEventFormModal} from '#/plugin/cursus/administration/modals/session-event-form/components/modal'

const MODAL_SESSION_EVENT_FORM = 'MODAL_SESSION_EVENT_FORM'

registry.add(MODAL_SESSION_EVENT_FORM, SessionEventFormModal)

export {
  MODAL_SESSION_EVENT_FORM
}
