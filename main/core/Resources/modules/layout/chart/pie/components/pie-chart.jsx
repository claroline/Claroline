import React from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'

import {implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {Chart as ChartTypes} from '#/main/core/layout/chart/prop-types'
import {Chart} from '#/main/core/layout/chart/components/chart.jsx'
import {DataSeries} from '#/main/core/layout/chart/pie/components/data-series.jsx'

/**
 * Draws a Bar chart
 */
const PieChart = props => {
  let radius = props.width/2 - props.margin.top
  return (
    <Chart
      width={props.width}
      height={props.width}
      margin={{
        top: radius + props.margin.top,
        left: radius + props.margin.top
      }}
      responsive={props.responsive}
      style={props.style}
    >
      <DataSeries
        data={props.data}
        colors={props.colors}
        innerRadius={0}
        outerRadius={radius}
        showValue={props.showValue}
        showPercentage={props.showPercentage}
      />
    </Chart>
  )
}

implementPropTypes(PieChart, ChartTypes, {
  data: T.oneOfType([T.array, T.object]).isRequired,
  colors: T.arrayOf(T.string).isRequired,
  showValue: T.bool.isRequired,
  showPercentage: T.bool.isRequired
}, {
  colors: schemeCategory20c,
  width: 550,
  showValue: true,
  showPercentage: false
})

export {
  PieChart
}
