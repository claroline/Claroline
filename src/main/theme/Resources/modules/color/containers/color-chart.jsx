import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'

import {ColorChart as ColorChartComponent} from '#/main/theme/color/components/color-chart'

const ColorChart = connect(
  (state) => ({
    colorChart: configSelectors.param(state, 'colorChart')
  })
)(ColorChartComponent)

export {
  ColorChart
}
