import {connect} from 'react-redux'

import {AppearanceColorCharts as AppearanceColorChartsComponent} from '#/main/theme/administration/appearance/components/colorCharts'
import {selectors} from '#/main/theme/administration/appearance/store'

const AppearanceColorCharts = connect(
  (state) => ({
    availableColorCharts: selectors.availableColorCharts(state),
    currentColorChart: selectors.currentColorChart(state)
  })
)(AppearanceColorChartsComponent)

export {
  AppearanceColorCharts
}
