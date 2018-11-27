import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {NotificationCard} from '#/plugin/planned-notification/tools/planned-notification/notification/data/components/notification-card'

const NotificationList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/notifications/form/${row.id}`,
    label: trans('edit', {}, 'actions')
  }),
  definition: [
    {
      name: 'parameters.action',
      label: trans('action'),
      alias: 'action',
      type: 'string',
      displayed: true,
      render: (row) => trans(row.parameters.action, {}, 'planned_notification')
    }, {
      name: 'message.title',
      label: trans('message'),
      type: 'string',
      displayed: true
    }, {
      name: 'parameters.interval',
      label: trans('planned_interval', {}, 'planned_notification'),
      alias: 'interval',
      type: 'number',
      displayed: true
    }, {
      name: 'parameters.byMail',
      label: trans('email'),
      alias: 'byMail',
      type: 'boolean',
      displayed: true
    }, {
      name: 'parameters.byMessage',
      label: trans('message'),
      alias: 'byMessage',
      type: 'boolean',
      displayed: true
    }
  ],
  card: NotificationCard
}

export {
  NotificationList
}