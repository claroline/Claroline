import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {actions, reducer, selectors} from '#/plugin/analytics/charts/actions/store'
import {ActionsChart as ActionsChartComponent} from '#/plugin/analytics/charts/actions/components/chart'

const ActionsChart = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state),
      data: selectors.data(state)
    }),
    (dispatch) => ({
      fetchActions(url, start, end) {
        dispatch(actions.fetchActions(url, start, end))
      }
    })
  )(ActionsChartComponent)
)

export {
  ActionsChart
}
