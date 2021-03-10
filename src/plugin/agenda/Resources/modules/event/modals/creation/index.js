/**
 * Event creation modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {EventCreationModal} from '#/plugin/agenda/event/modals/creation/containers/modal'

const MODAL_EVENT_CREATION = 'MODAL_EVENT_CREATION'

// make the modal available for use
registry.add(MODAL_EVENT_CREATION, EventCreationModal)

export {
  MODAL_EVENT_CREATION
}
