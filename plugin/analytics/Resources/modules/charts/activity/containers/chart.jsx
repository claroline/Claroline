import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {actions, reducer, selectors} from '#/plugin/analytics/charts/activity/store'
import {ActivityChart as ActivityChartComponent} from '#/plugin/analytics/charts/activity/components/chart'

const ActivityChart = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state),
      data: selectors.data(state)
    }),
    (dispatch) => ({
      fetchActivity(url) {
        dispatch(actions.fetchActivity(url))
      }
    })
  )(ActivityChartComponent)
)

export {
  ActivityChart
}
