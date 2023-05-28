/**
 * Notifications modal.
 * Displays the unread notifications of the current user.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {NotificationsModal} from '#/plugin/notification/modals/notifications/containers/modal'

const MODAL_NOTIFICATIONS = 'MODAL_NOTIFICATIONS'

// make the modal available for use
registry.add(MODAL_NOTIFICATIONS, NotificationsModal)

export {
  MODAL_NOTIFICATIONS
}
