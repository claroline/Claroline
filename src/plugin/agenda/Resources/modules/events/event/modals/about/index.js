/**
 * Agenda event about modal.
 * Displays information about the event (used for integration with agenda).
 */

import {registry} from '#/main/app/modals/registry'

import {EventAboutModal} from '#/plugin/agenda/events/event/modals/about/components/modal'

const MODAL_AGENDA_EVENT_ABOUT = 'MODAL_TRAINING_EVENT_ABOUT'

registry.add(MODAL_AGENDA_EVENT_ABOUT, EventAboutModal)

export {
  MODAL_AGENDA_EVENT_ABOUT
}
