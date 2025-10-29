/**
 * Events Parameters modal.
 * Displays a form to configure an Event.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {EventFormModal} from '#/plugin/agenda/event/modals/form/components/modal'

const MODAL_EVENT_FORM = 'MODAL_EVENT_FORM'

// make the modal available for use
registry.add(MODAL_EVENT_FORM, EventFormModal)

export {
  MODAL_EVENT_FORM
}
