import React from 'react'
import {PropTypes as T} from 'prop-types'

import { implementPropTypes } from '#/main/core/scaffolding/prop-types'
import {Chart as ChartTypes} from '#/main/core/layout/chart/prop-types'
import {Chart} from '#/main/core/layout/chart/components/chart.jsx'
import {DataSeries} from '#/main/core/layout/chart/pie/components/data-series.jsx'

// TODO merge with pie chart

/**
 * Draws a Circular gauge
 */
const CircularGauge = props => {
  const radius = props.width / 2

  return (
    <Chart
      width={props.width}
      height={props.width}
      margin={{
        'top': radius,
        'left': radius
      }}
      responsive={props.responsive}
      style={props.style}
    >
      <g
        alignmentBaseline="middle"
      >
        <text
          strokeWidth={1}
          textAnchor="middle"
        >
          {props.label}
        </text>

        <text
          fontSize={24}
          stroke={props.color}
          fill={props.color}
          textAnchor="middle"
          y={24}
        >
          {props.value}
        </text>
      </g>

      <DataSeries
        data={[props.value, props.max - props.value]}
        colors={[props.color, '#ccc']}
        innerRadius={radius - props.size}
        outerRadius={radius}
        showValue={props.showValue}
      />
    </Chart>
  )
}

implementPropTypes(CircularGauge, ChartTypes, {
  value: T.number,
  max: T.number.isRequired,
  color: T.string,
  label: T.string,
  size: T.number,
  showValue: T.bool.isRequired
}, {
  value: 0,
  color: '#337ab7', // Default bootstrap primary color
  label: null,
  width: 200,
  size: 30,
  showValue: true
})

export {
  CircularGauge
}
