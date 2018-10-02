import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {select} from '#/main/app/content/list/store'
import {LogList} from '#/main/core/layout/logs'
import {actions as logActions} from  '#/main/core/layout/logs/actions'

const List = (props) =>
  <LogList
    listUrl={['apiv2_admin_tool_logs_list']}
    actions={props.actions}
    chart={props.chart}
    getChartData={props.getChartData}
    queryString={props.queryString}
  />

List.propTypes = {
  actions: T.array.isRequired,
  chart: T.object.isRequired,
  getChartData: T.func.isRequired,
  queryString: T.string
}

const ListContainer = connect(
  state => ({
    chart: state.chart,
    actions: state.actions,
    queryString: select.queryString(select.list(state, 'logs'))
  }),
  dispatch => ({
    getChartData(id, filters) {
      dispatch(logActions.getChartData('apiv2_admin_tool_logs_list_chart', {}, filters))
    }
  })
)(List)

export {
  ListContainer as Logs
}