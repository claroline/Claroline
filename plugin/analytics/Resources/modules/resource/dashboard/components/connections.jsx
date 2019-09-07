import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {convertTimestampToString} from '#/main/app/intl/date'

import {ListData} from '#/main/app/content/list/containers/data'

import {LogConnectCard} from '#/main/core/layout/logs/components/connect-card'

import {selectors} from '#/plugin/analytics/resource/dashboard/store/selectors'

const Connections = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.connections'}
    fetch={{
      url: ['apiv2_log_connect_resource_list', {resource: props.resourceId}],
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
    card={LogConnectCard}
  />

Connections.propTypes = {
  resourceId: T.number.isRequired
}

export {
  Connections
}
