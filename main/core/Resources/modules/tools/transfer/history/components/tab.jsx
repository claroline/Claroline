import React from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/tools/transfer/store'
import {actions as logActions} from '#/main/core/tools/transfer/log/store'
import {Logs} from '#/main/core/tools/transfer/log/components/logs'

const Tab = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/history',
        exact: true,
        component: ConnectedList
      }, {
        path: '/history/:log',
        component: Logs,
        onEnter: (params) => props.loadLog(params.log)
      }
    ]}
  />

const List = props =>
  <ListData
    name={selectors.STORE_NAME + '.history'}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/history/${row.log}`
    })}
    fetch={{
      url: props.workspace && props.workspace.id ? ['apiv2_workspace_transfer_list', {workspaceId: props.workspace.id}]: ['apiv2_transfer_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_transfer_delete_bulk']
    }}
    definition={[
      {
        name: 'id',
        type: 'string',
        label: trans('id'),
        displayed: true,
        primary: true
      }, {
        name: 'log',
        type: 'string',
        label: trans('log')
      }, {
        name: 'status',
        type: 'translation',
        label: trans('status'),
        displayed: true
      }, {
        name: 'uploadDate',
        type: 'date',
        label: trans('date'),
        displayed: true,
        options: {
          time: true
        }
      }
    ]}
  />

const History = connect(
  state => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  }),
  dispatch => ({
    loadLog(filename) {
      dispatch(logActions.load(filename))
    }
  })
)(Tab)

const ConnectedList = connect(
  state => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  })
)(List)

export {
  History
}
