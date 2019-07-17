import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {select as listSelect} from '#/main/app/content/list/store'

import {actions as logActions} from  '#/main/core/layout/logs/actions'
import {selectors as toolSelectors} from  '#/main/core/tool/store'
import {selectors} from '#/main/core/administration/dashboard/store'
import {LogList} from '#/main/core/layout/logs'

const LogsComponent = (props) =>
  <LogList
    listUrl={['apiv2_admin_tool_logs_list']}
    name={selectors.STORE_NAME + '.logs'}
    actions={props.actions}
    chart={props.chart}
    getChartData={props.getChartData}
    queryString={props.queryString}
    path={props.path}
  />

LogsComponent.propTypes = {
  path: T.string.isRequired,
  actions: T.array.isRequired,
  chart: T.object.isRequired,
  getChartData: T.func.isRequired,
  queryString: T.string
}

const Logs = connect(
  state => ({
    path: toolSelectors.path(state),
    chart: selectors.chart(state),
    actions: selectors.actions(state),
    queryString: listSelect.queryString(listSelect.list(state, selectors.STORE_NAME + '.logs'))
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