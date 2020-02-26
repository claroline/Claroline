import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {actions, reducer, selectors} from '#/plugin/analytics/charts/resources/store'
import {ResourcesChart as ResourcesChartComponent} from '#/plugin/analytics/charts/resources/components/chart'

const ResourcesChart = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state),
      mode: selectors.mode(state),
      data: selectors.data(state)
    }),
    (dispatch) => ({
      changeMode(mode) {
        dispatch(actions.changeMode(mode))
      },
      fetchResources(url) {
        dispatch(actions.fetchResources(url))
      }
    })
  )(ResourcesChartComponent)
)

export {
  ResourcesChart
}
