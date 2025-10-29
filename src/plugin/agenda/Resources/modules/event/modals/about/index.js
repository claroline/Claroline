/**
 * Event About modal.
 * Displays general information about the event.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {EventAboutModal} from '#/plugin/agenda/event/modals/about/components/modal'

const MODAL_EVENT_ABOUT = 'MODAL_EVENT_ABOUT'

// make the modal available for use
registry.add(MODAL_EVENT_ABOUT, EventAboutModal)

export {
  MODAL_EVENT_ABOUT
}
