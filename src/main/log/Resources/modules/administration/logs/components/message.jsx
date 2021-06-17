import React from 'react'

import {ListData} from '#/main/app/content/list/containers/data'
import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/main/log/administration/logs/store/selectors'

const DashboardMessage = () =>
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
        label: trans('description'),
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

export {
  DashboardMessage
}
