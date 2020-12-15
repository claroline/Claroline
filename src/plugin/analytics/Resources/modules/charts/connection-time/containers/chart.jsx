import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {actions, reducer, selectors} from '#/plugin/analytics/charts/connection-time/store'
import {ConnectionTimeChart as ConnectionTimeChartComponent} from '#/plugin/analytics/charts/connection-time/components/chart'

const ConnectionTimeChart = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state),
      data: selectors.data(state)
    }),
    (dispatch) => ({
      fetchConnectionTime(url) {
        dispatch(actions.fetchConnectionTime(url))
      }
    })
  )(ConnectionTimeChartComponent)
)

export {
  ConnectionTimeChart
}
