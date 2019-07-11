import {trans} from '#/main/app/intl/translation'
import React from 'react'

import {ListData} from '#/main/app/content/list/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'

import {constants as listConstants} from '#/main/app/content/list/constants'
import {NotificationCard} from '#/plugin/notification/tools/notification/components/notification-card'

const List = () =>
  <ToolPage>
    <ListData
      name="notification.notifications"
      fetch={{
        url: ['apiv2_get_notifications_current'],
        autoload: true
      }}
      display={{
        available: [listConstants.DISPLAY_LIST],
        current: listConstants.DISPLAY_LIST
      }}
      definition={[{
        name: 'text',
        label: trans('text'),
        displayed: true
      }]}
      card={(row) => <NotificationCard {...row}/>}
    />
  </ToolPage>

export {
  List
}
