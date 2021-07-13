import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isArray from 'lodash/isArray'
import uniq from 'lodash/uniq'
import {schemeCategory20c} from 'd3-scale'

import { implementPropTypes } from '#/main/app/prop-types'
import {AxisChart as ChartTypes} from '#/main/core/layout/chart/prop-types'
import {Chart} from '#/main/core/layout/chart/components/chart.jsx'
import {Axis} from '#/main/core/layout/chart/components/axis.jsx'
import {formatData} from '#/main/core/layout/chart/utils'
import {
  AXIS_TYPE_X,
  AXIS_TYPE_Y
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
  // allowing only one data serie is for retro compatibility
  let data = props.data
  if (!isArray(data)) {
    data = [data]
  }

  // merge all data series to be able to compute axis
  let yValues = []
  let yType
  let xValues = []
  let xType
  data.map((d) => {
    const formattedData = formatData(d)
    yValues = uniq(yValues.concat(formattedData.y.values))
    yType = formattedData.y.type
    xValues = uniq(xValues.concat(formattedData.x.values))
    xType = formattedData.x.type
  })

  const width = props.width - props.margin.left - props.margin.right
  const height = props.height - props.margin.top - props.margin.bottom

  const yScale = props.scaleAxis(yValues, AXIS_TYPE_Y, yType, height, props.minMaxAsYDomain)
  const xScale = props.scaleAxis(xValues, AXIS_TYPE_X, xType, width, true)

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
        values={xValues}
        scale={xScale}
        type={AXIS_TYPE_X}
        dataType={xType}
        label={props.xAxisLabel}
        ticksAsValues={true}
      />
      <Axis
        height={height}
        width={width}
        margin={props.margin}
        values={yValues}
        scale={yScale}
        type={AXIS_TYPE_Y}
        dataType={yType}
        label={props.yAxisLabel}
        ticksAsValues={props.ticksAsYValues}
      />

      {createElement(props.dataSeries, {
        data: data,
        height: height,
        yScale: yScale,
        xScale: xScale,
        colors: props.colors,
        color: props.color,
        altColor: props.altColor,
        showArea: props.showArea,
        onClick: props.onClick
      })}
    </Chart>
  )
}

implementPropTypes(AxisChart, ChartTypes, {
  dataSeries: T.func.isRequired,
  scaleAxis: T.func.isRequired // provided by implementation (eg. line, bar)
}, {
  colors: schemeCategory20c, // TODO : only for retro compatibility. Should be defined by caller
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
