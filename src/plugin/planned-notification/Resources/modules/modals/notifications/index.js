/**
 * Planned notifications picker modal.
 *
 * Displays the planned notifications picker inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {NotificationsPickerModal} from '#/plugin/planned-notification/modals/notifications/containers/modal'

const MODAL_PLANNED_NOTIFICATIONS_PICKER = 'MODAL_PLANNED_NOTIFICATIONS_PICKER'

// make the modal available for use
registry.add(MODAL_PLANNED_NOTIFICATIONS_PICKER, NotificationsPickerModal)

export {
  MODAL_PLANNED_NOTIFICATIONS_PICKER
}
