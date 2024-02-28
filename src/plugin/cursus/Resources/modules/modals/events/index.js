/**
 * Training events picker modal.
 *
 * Displays the courses picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {EventsModal} from '#/plugin/cursus/modals/events/containers/modal'

const MODAL_TRAINING_EVENTS = 'MODAL_TRAINING_EVENTS'

// make the modal available for use
registry.add(MODAL_TRAINING_EVENTS, EventsModal)

export {
  MODAL_TRAINING_EVENTS
}
