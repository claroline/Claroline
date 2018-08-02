import React from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'
import {ListData} from '#/main/app/content/list/containers/data'

import {Logs} from '#/main/core/administration/transfer/log/components/logs'
import {actions} from '#/main/core/administration/transfer/log/actions'

const Tab = (props) =>
  <Routes
    routes={[
      {
        path: '/history',
        exact: true,
        component: List
      }, {
        path: '/history/:log',
        component: Logs,
        onEnter: (params) => props.loadLog(params.log)
      }
    ]}
  />

const List = () =>
  <ListData
    name="history"
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: '/history/' + row.log
    })}
    fetch={{
      url: ['apiv2_transfer_list'],
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
      },
      {
        name: 'log',
        type: 'string',
        label: trans('log')
      },
      {
        name: 'status',
        type: 'string',
        label: trans('status'),
        displayed: true
      },
      {
        name: 'executionDate',
        type: 'date',
        label: trans('execution_date'),
        displayed: true
      },
      {
        name: 'uploadDate',
        type: 'date',
        label: trans('upload_date'),
        displayed: true
      }
    ]}
  />

const History = connect(
  null,
  dispatch => ({
    loadLog(filename) {
      dispatch(actions.load(filename))
    }
  })
)(Tab)

export {
  History
}
