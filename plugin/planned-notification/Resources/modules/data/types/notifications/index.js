import {trans} from '#/main/app/intl/translation'

import {NotificationsDisplay} from '#/plugin/planned-notification/data/types/notifications/components/display'
import {NotificationsGroup} from '#/plugin/planned-notification/data/types/notifications/components/group'

const dataType = {
  name: 'planned_notifications',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-bell',
    label: trans('notifications'),
    description: trans('notifications_desc', {}, 'planned_notification')
  },
  components: {
    details: NotificationsDisplay,
    form: NotificationsGroup
  }
}

export {
  dataType
}
