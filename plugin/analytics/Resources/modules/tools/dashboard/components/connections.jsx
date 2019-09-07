import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {convertTimestampToString} from '#/main/app/intl/date'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from  '#/main/core/tool/store'
import {selectors} from '#/plugin/analytics/tools/dashboard/store'
import {LogConnectCard} from '#/main/core/layout/logs/components/connect-card'

const ConnectionsComponent = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.connections.list'}
    fetch={{
      url: ['apiv2_log_connect_workspace_list', {workspace: props.workspaceId}],
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

ConnectionsComponent.propTypes = {
  workspaceId: T.number
}

const Connections = connect(
  state => ({
    workspaceId: toolSelectors.contextData(state).id
  })
)(ConnectionsComponent)

export {
  Connections
}
