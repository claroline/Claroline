import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {select as listSelect} from '#/main/app/content/list/store'

import {actions as logActions} from  '#/main/core/layout/logs/actions'
import {LogList} from '#/main/core/layout/logs'

const List = (props) =>
  <LogList
    id={props.workspaceId}
    listUrl={['apiv2_workspace_tool_logs_list', {workspaceId: props.workspaceId}]}
    actions={props.actions}
    chart={props.chart}
    getChartData={props.getChartData}
    queryString={props.queryString}
  />

List.propTypes = {
  workspaceId: T.number.isRequired,
  actions: T.array.isRequired,
  chart: T.object.isRequired,
  getChartData: T.func.isRequired,
  queryString: T.string
}

const ListContainer = connect(
  state => ({
    workspaceId: state.workspaceId,
    chart: state.chart,
    actions: state.actions,
    queryString: listSelect.queryString(listSelect.list(state, 'logs'))
  }),
  dispatch => ({
    getChartData(workspaceId, filters) {
      dispatch(logActions.getChartData('apiv2_workspace_tool_logs_list_chart', {workspaceId}, filters))
    }
  })
)(List)

export {
  ListContainer as Logs
}