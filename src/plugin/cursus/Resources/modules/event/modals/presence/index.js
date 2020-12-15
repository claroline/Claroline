/**
 * Event presence modal.
 * Displays a form to change the status of a user presence.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {PresenceModal} from '#/plugin/cursus/event/modals/presence/components/modal'

const MODAL_EVENT_PRESENCE = 'MODAL_EVENT_PRESENCE'

// make the modal available for use
registry.add(MODAL_EVENT_PRESENCE, PresenceModal)

export {
  MODAL_EVENT_PRESENCE
}
