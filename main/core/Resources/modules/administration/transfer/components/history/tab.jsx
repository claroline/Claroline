import React from 'react'
import {connect} from 'react-redux'

import {actions} from '#/main/core/administration/transfer/components/log/actions'
import {Routes} from '#/main/core/router'
import {Logs} from '#/main/core/administration/transfer/components/log/components/logs.jsx'

import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {HistoryList} from '#/main/core/administration/transfer/components/history/history-list.jsx'

const Tab = (props) =>
  <div className="col-md-9">
    <Routes
      routes={[
        {
          path: '/history',
          exact: true,
          component: List
        },
        {
          path: '/history/:log',
          component: Logs,
          onEnter: (params) => {
            props.loadLog(params.log)
          }
        }
      ]}
    />
  </div>

const List = () =>
  <DataListContainer
    name="history"
    primaryAction={(row) => ({
      id: 'logfile',
      type: 'link',
      target: '/history/' + row.log
    })}
    fetch={{
      url: ['apiv2_transfer_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_transfer_delete_bulk']
    }}
    definition={HistoryList.definition}
    actions={() => [

    ]}
  />

const ConnectedTab = connect(
  null,
  dispatch => ({
    loadLog(filename) {
      dispatch(actions.load(filename))
    }
  })
)(Tab)

export {
  ConnectedTab as Tab
}
