import {connect} from 'react-redux'

import {select as listSelect} from '#/main/app/content/list/store'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {actions as logActions} from  '#/main/core/layout/logs/actions'

import {selectors as dashboardSelectors} from '#/plugin/analytics/resource/dashboard/store'
import {Logs as LogsComponent} from '#/plugin/analytics/resource/dashboard/components/logs'

const Logs = connect(
  state => ({
    resourceId: resourceSelectors.resourceNode(state).autoId,
    chart: dashboardSelectors.chart(state),
    actions: dashboardSelectors.store(state).actions,
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