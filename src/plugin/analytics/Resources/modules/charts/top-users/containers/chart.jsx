import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {TopUsersChart as TopUsersChartComponent} from '#/plugin/analytics/charts/top-users/components/chart'
import {actions, reducer, selectors} from '#/plugin/analytics/charts/top-users/store'

const TopUsersChart = withReducer(selectors.STORE_NAME, reducer)(
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
  )(TopUsersChartComponent)
)

export {
  TopUsersChart
}
