import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'

import {convertTimestampToString} from '#/main/core/logs/utils'
import {LogConnectPlatformCard} from '#/main/core/logs/data/components/log-connect-platform-card'

const Connections = () =>
  <ListData
    name="connections.list"
    fetch={{
      url: ['apiv2_log_connect_platform_list'],
      autoload: true
    }}
    definition={[
      {
        name: 'date',
        alias: 'connectionDate',
        type: 'date',
        label: trans('date'),
        displayed: true,
        filterable: false,
        primary: true,
        options: {
          time: true
        }
      }, {
        name: 'user.name',
        alias: 'name',
        type: 'string',
        label: trans('user'),
        displayed: true
      }, {
        name: 'duration',
        type: 'string',
        label: trans('duration'),
        displayed: true,
        filterable: false,
        calculated: (rowData) => rowData.duration !== null ? convertTimestampToString(rowData.duration) : null
      }
    ]}
    card={LogConnectPlatformCard}
  />

export {
  Connections
}
