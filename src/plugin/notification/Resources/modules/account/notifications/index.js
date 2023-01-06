import {trans} from '#/main/app/intl'

import {NotificationMain} from '#/plugin/notification/account/notifications/containers/main'

export default {
  name: 'notifications',
  icon: 'fa fa-fw fa-bell',
  label: trans('notifications'),
  component: NotificationMain
}
