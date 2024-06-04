/**
 * Login modal.
 * Displays a modal to login in the platform.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {MyNotificationsModal} from '#/main/notification/modals/my-notifications/components/modal'

const MODAL_MY_NOTIFICATIONS = 'MODAL_MY_NOTIFICATIONS'

// make the modal available for use
registry.add(MODAL_MY_NOTIFICATIONS, MyNotificationsModal)

export {
  MODAL_MY_NOTIFICATIONS
}
