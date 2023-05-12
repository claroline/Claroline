import { connect } from 'react-redux'

import { selectors } from '#/main/theme/administration/appearance/store/selectors'
import {actions} from '#/main/theme/administration/appearance/store'

import {AppearanceColorCharts as AppearanceColorChartsComponent} from '#/main/theme/administration/appearance/components/colorCharts'

const AppearanceColorCharts = connect(
  (state) => ({
    availableColorCharts: selectors.availableColorCharts(state)
  }),
  (dispatch) => ({
    updateColorChart(colorChart) {
      dispatch(actions.updateColorChart(colorChart))
    },
    removeColorChart(colorChart) {
      dispatch(actions.removeColorChart(colorChart))
    }
  })
)(AppearanceColorChartsComponent)

export {
  AppearanceColorCharts
}
