/**
 * Agenda event form modal.
 * Displays a form to create an event.
 */

import {registry} from '#/main/app/modals/registry'

import {EventFormModal} from '#/plugin/agenda/events/event/modals/form/components/modal'

const MODAL_AGENDA_EVENT_FORM = 'MODAL_AGENDA_EVENT_FORM'

registry.add(MODAL_AGENDA_EVENT_FORM, EventFormModal)

export {
  MODAL_AGENDA_EVENT_FORM
}
