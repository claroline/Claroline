import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {actions, reducer, selectors} from '#/plugin/exo/charts/attempts/store'
import {AttemptsChart as AttemptsChartComponent} from '#/plugin/exo/charts/attempts/components/chart'

const AttemptsChart = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state),
      data: selectors.data(state)
    }),
    (dispatch) => ({
      fetchAttempts(quizId, userId = null) {
        return dispatch(actions.fetchAttempts(quizId, userId))
      }
    })
  )(AttemptsChartComponent)
)

export {
  AttemptsChart
}
