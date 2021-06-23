import React from 'react'

import {ListData} from '#/main/app/content/list/containers/data'
import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/main/log/administration/logs/store/selectors'

const DashboardLog = () =>
  <ListData
    name={selectors.LIST_NAME}
    fetch={{
      url: ['apiv2_logs_security'],
      autoload: true
    }}
    definition={[
      {
        name: 'doer',
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
        name: 'target',
        type: 'user',
        label: trans('target'),
        displayed: false
      }, {
        name: 'event',
        type: 'translation',
        label: trans('event'),
        displayed: false,
        options: {
          domain: 'security'
        }
      }, {
        name: 'doer_ip',
        label: trans('ip_address'),
        type: 'ip',
        displayed: false
      }
    ]}
  />

export {
  DashboardLog
}
