import {connect} from 'react-redux'

import {selectors} from '#/main/theme/administration/appearance/store'
import {AppearanceColorsCharts as AppearanceColorsChartsComponent} from '#/main/theme/administration/appearance/components/colorsCharts'
const AppearanceColorsCharts = connect(
  (state) => ({
    currentIconSet: selectors.currentIconSet(state)
  })
)(AppearanceColorsChartsComponent)

export {
  AppearanceColorsCharts
}
