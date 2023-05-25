import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {ColorChart as ColorChartComponent} from '#/main/theme/color/components/color-chart'
import {actions, reducer, selectors} from '#/main/theme/color/store'

const ColorChart = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      colorChart: selectors.colorChart(state)
    }),
    (dispatch) => ({
      async load() {
        await dispatch(actions.fetchColorChart())
      }
    })
  )(ColorChartComponent)
)

export {
  ColorChart
}
