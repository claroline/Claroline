import React from 'react'
import {PropTypes as T} from 'prop-types'

import { implementPropTypes } from '#/main/core/scaffolding/prop-types'
import {AxisChart as ChartTypes} from '#/main/core/layout/chart/prop-types'
import {Chart} from '#/main/core/layout/chart/components/chart.jsx'
import {Axis} from '#/main/core/layout/chart/components/axis.jsx'
import {scaleAxis, formatData} from '#/main/core/layout/chart/utils'
import {
  AXIS_TYPE_X,
  AXIS_TYPE_Y,
  DATA_SERIES,
  CHART_TYPES
} from '#/main/core/layout/chart/enums'

/**
 * Draws a Line or Bar chart (or any chart having axis)
 * data must be formed as a key value object collection
 * data : {
 *   key1: {xData: dataForXAxis, yData: dataForYAxis},
 *   key2: {xData: dataForXAxis, yData: dataForYAxis},
 *   ...
 * }
 */
const AxisChart = props => {
  const formattedData = formatData(props.data)
  const width = props.width - props.margin.left - props.margin.right
  const height = props.height - props.margin.top - props.margin.bottom

  const yScale = scaleAxis(formattedData.y.values, AXIS_TYPE_Y, formattedData.y.type, height, props.minMaxAsYDomain)
  const xScale = scaleAxis(formattedData.x.values, AXIS_TYPE_X, formattedData.x.type, width)

  return (
    <Chart
      width={props.width}
      height={props.height}
      margin={props.margin}
      responsive={props.responsive}
      style={props.style}
    >
      <Axis
        height={height}
        width={width}
        margin={props.margin}
        values={formattedData.x.values}
        scale={xScale}
        type={AXIS_TYPE_X}
        dataType={formattedData.x.type}
        label={props.xAxisLabel}
      />
      <Axis
        height={height}
        width={width}
        margin={props.margin}
        values={formattedData.y.values}
        scale={yScale}
        type={AXIS_TYPE_Y}
        dataType={formattedData.y.type}
        label={props.yAxisLabel}
        ticksAsValues={props.ticksAsYValues}
      />

      {React.createElement(DATA_SERIES[props.type], {
        data: formattedData,
        height: height,
        yScale: yScale,
        xScale: xScale,
        color: props.color,
        altColor: props.altColor,
        showArea: props.showArea
      })}
    </Chart>
  )
}

implementPropTypes(AxisChart, ChartTypes, {
  type: T.oneOf(CHART_TYPES).isRequired
}, {
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
  AxisChart
}
