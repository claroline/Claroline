import React from 'react'

import {ToolPage} from '#/main/core/tool/containers/page'
import {ListData} from '#/main/app/content/list/containers/data'
import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/analytics/administration/dashboard/store/selectors'

const DashboardMessage = () =>
  <ToolPage
    subtitle={trans('message')}
  >
    <ListData
      name={selectors.MESSAGE_NAME}
      fetch={{
        url: ['apiv2_logs_message'],
        autoload: true
      }}
      definition={[
        {
          name: 'sender',
          type: 'user',
          label: trans('user'),
          displayed: true
        }, {
          name: 'date',
          label: trans('date'),
          type: 'date',
          options: {time: true},
          displayed: true
        }, {
          name: 'details',
          type: 'string',
          label: trans('details'),
          displayed: true
        }, {
          name: 'receiver',
          type: 'user',
          label: trans('target'),
          displayed: false
        }, {
          name: 'event',
          type: 'translation',
          label: trans('event'),
          displayed: false,
          options: {
            domain: 'platform'
          }
        }
      ]}
    />
  </ToolPage>

export {
  DashboardMessage
}
