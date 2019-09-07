import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {select as listSelect} from '#/main/app/content/list/store'

import {selectors as toolSelectors} from  '#/main/core/tool/store'
import {selectors} from '#/plugin/analytics/tools/dashboard/store'
import {actions as logActions} from  '#/main/core/layout/logs/actions'
import {LogList} from '#/main/core/layout/logs'

const LogsComponent = (props) =>
  <LogList
    id={props.workspaceId}
    listUrl={['apiv2_workspace_tool_logs_list', {workspaceId: props.workspaceId}]}
    actions={props.actions}
    chart={props.chart}
    getChartData={props.getChartData}
    queryString={props.queryString}
    name={selectors.STORE_NAME + '.logs'}
    path={props.path}
  />

LogsComponent.propTypes = {
  path: T.string.isRequired,
  workspaceId: T.number.isRequired,
  actions: T.array.isRequired,
  chart: T.object.isRequired,
  getChartData: T.func.isRequired,
  queryString: T.string
}

const Logs = connect(
  state => ({
    path: toolSelectors.path(state),
    workspaceId: toolSelectors.contextData(state).id,
    chart: selectors.chart(state),
    actions: selectors.actions(state),
    queryString: listSelect.queryString(listSelect.list(state, selectors.STORE_NAME + '.logs'))
  }),
  dispatch => ({
    getChartData(workspaceId, filters) {
      dispatch(logActions.getChartData('apiv2_workspace_tool_logs_list_chart', {workspaceId}, filters))
    }
  })
)(LogsComponent)

export {
  Logs
}