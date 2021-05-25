import React from 'react'

import {implementPropTypes} from '#/main/app/prop-types'

import {AxisChart as ChartTypes} from '#/main/core/layout/chart/prop-types'
import {AxisChart} from '#/main/core/layout/chart/components/axis-chart'

import {DataSeries} from '#/main/core/layout/chart/line/components/data-series'
import {scaleAxis} from '#/main/core/layout/chart/line/utils'

/**
 * Draws a Bar chart
 * data must be formed as a key value object collection
 * data : {
 *   key1: {xData: dataForXAxis, yData: dataForYAxis},
 *   key2: {xData: dataForXAxis, yData: dataForYAxis},
 *   ...
 * }
 */
const LineChart = props =>
  <AxisChart
    {...props}
    dataSeries={DataSeries}
    scaleAxis={scaleAxis}
  />

implementPropTypes(LineChart, ChartTypes, {}, {
  width: 550,
  height: 400,
  margin: {
    top: 20,
    right: 20,
    bottom: 20,
    left: 60
  }
})

export {
  LineChart
}