import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {select as listSelect} from '#/main/app/content/list/store'

import {actions as logActions} from  '#/main/core/layout/logs/actions'
import {LogList} from '#/main/core/layout/logs'

const LogsComponent = (props) =>
  <LogList
    listUrl={['apiv2_admin_tool_logs_list']}
    actions={props.actions}
    chart={props.chart}
    getChartData={props.getChartData}
    queryString={props.queryString}
  />

LogsComponent.propTypes = {
  actions: T.array.isRequired,
  chart: T.object.isRequired,
  getChartData: T.func.isRequired,
  queryString: T.string
}

const Logs = connect(
  state => ({
    chart: state.chart,
    actions: state.actions,
    queryString: listSelect.queryString(listSelect.list(state, 'logs'))
  }),
  dispatch => ({
    getChartData(id, filters) {
      dispatch(logActions.getChartData('apiv2_admin_tool_logs_list_chart', {}, filters))
    }
  })
)(LogsComponent)

export {
  Logs
}