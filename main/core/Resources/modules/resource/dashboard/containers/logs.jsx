import React from 'react'
import {connect} from 'react-redux'

import {select as listSelect} from '#/main/app/content/list/store'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {actions as logActions} from  '#/main/core/layout/logs/actions'

import {selectors as dashboardSelectors} from '#/main/core/resource/dashboard/store'
import {Logs as LogsComponent} from '#/main/core/resource/dashboard/components/logs'

const Logs = connect(
  state => ({
    resourceId: resourceSelectors.resourceNode(state).autoId,
    chart: dashboardSelectors.dashboard(state).chart,
    // actions: state.actions,
    queryString: listSelect.queryString(listSelect.list(state, dashboardSelectors.STORE_NAME + '.logs'))
  }),
  dispatch => ({
    getChartData(resourceId, filters) {
      dispatch(logActions.getChartData('apiv2_resource_logs_list_chart', {resourceId}, filters))
    }
  })
)(LogsComponent)

export {
  Logs
}