import React from 'react'

import { implementPropTypes } from '#/main/core/scaffolding/prop-types'
import {AxisChart as ChartTypes} from '#/main/core/layout/chart/prop-types'
import {AxisChart} from '#/main/core/layout/chart/components/axis-chart.jsx'
import {
  BAR_CHART
} from '#/main/core/layout/chart/enums'

/**
 * Draws a Bar chart
 * data must be formed as a key value object collection
 * data : {
 *   key1: {xData: dataForXAxis, yData: dataForYAxis},
 *   key2: {xData: dataForXAxis, yData: dataForYAxis},
 *   ...
 * }
 */
const BarChart = props =>
  <AxisChart
    {...props}
    type={BAR_CHART}
  />

implementPropTypes(BarChart, ChartTypes, {}, {
  width: 550,
  height: 400,
  margin: {
    top: 20,
    right: 20,
    bottom: 20,
    left: 30
  }
})

export {
  BarChart
}
