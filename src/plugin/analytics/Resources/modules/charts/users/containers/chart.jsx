import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {actions, reducer, selectors} from '#/plugin/analytics/charts/users/store'
import {UsersChart as UsersChartComponent} from '#/plugin/analytics/charts/users/components/chart'

const UsersChart = withReducer(selectors.STORE_NAME, reducer)(
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
      fetchUsers(url) {
        dispatch(actions.fetchUsers(url))
      }
    })
  )(UsersChartComponent)
)

export {
  UsersChart
}
