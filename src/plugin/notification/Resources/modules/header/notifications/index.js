import {trans} from '#/main/app/intl/translation'
import {NotificationsMenu} from '#/plugin/notification/header/notifications/containers/menu'

// expose main component to be used by the header
export default ({
  name: 'notifications',
  label: trans('notifications', {}, 'notification'),
  component: NotificationsMenu
})
