/**
 * Event About modal.
 * Displays general information about the event.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AboutModal} from '#/plugin/agenda/event/modals/about/components/modal'

const MODAL_EVENT_ABOUT = 'MODAL_EVENT_ABOUT'

// make the modal available for use
registry.add(MODAL_EVENT_ABOUT, AboutModal)

export {
  MODAL_EVENT_ABOUT
}
