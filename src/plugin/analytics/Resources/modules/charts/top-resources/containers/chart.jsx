import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {TopResourcesChart as TopResourcesChartComponent} from '#/plugin/analytics/charts/top-resources/components/chart'
import {actions, reducer, selectors} from '#/plugin/analytics/charts/top-resources/store'

const TopResourcesChart = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state),
      data: selectors.data(state)
    }),
    (dispatch) => ({
      fetchTop(url) {
        dispatch(actions.fetchTop(url))
      }
    })
  )(TopResourcesChartComponent)
)

export {
  TopResourcesChart
}
