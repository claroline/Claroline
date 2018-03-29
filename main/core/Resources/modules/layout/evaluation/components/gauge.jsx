import React from 'react'
import {PropTypes as T} from 'prop-types'
import {arc, pie} from 'd3-shape'

const RemainingPart = props => {
  const arcInstance = arc()
    .innerRadius(props.innerRadius)
    .outerRadius(props.outerRadius)
    .startAngle(props.startAngle)
    .endAngle(props.endAngle)

  return (
    <path
      className="progression-remaining"
      d={arcInstance()}
      fill={props.color}
    />
  )
}

const FilledPart = props => {
  const arcInstance = arc()
    .innerRadius(props.innerRadius)
    .outerRadius(props.outerRadius)
    .startAngle(props.startAngle)
    .endAngle(props.endAngle)

  return (
    <path
      className="progression-filled"
      d={arcInstance()}
      fill={props.color}
    />
  )
}

const Gauge = props => {
  const color = '#C51162'
  const width = 80
  const height = 80
  const radius = width / 2
  const fillSize = 10

  // generate data for gauge
  const gaugeData = pie().sort(null)([
    props.value, // filled part
    props.total - props.value // remaining part
  ])

  return (
    <svg className="gauge gauge-progression" width={width} height={height}>
      <g
        transform={`translate(${width / 2}, ${height / 2})`}
        alignmentBaseline="middle"
      >
        <text
          className="progression-value"
          fontSize={22}
          stroke={color}
          fill={color}
          textAnchor="middle"
        >
          {props.value}
        </text>

        <text
          strokeWidth={1}
          textAnchor="middle"
          y={22}
        >
          {props.percent ? '%' : props.total}
        </text>
      </g>

      <g transform={`translate(${radius}, ${radius})`}>
        <FilledPart
          color="#666666"
          innerRadius={radius - fillSize}
          outerRadius={radius}
          startAngle={gaugeData[0].startAngle}
          endAngle={gaugeData[0].endAngle}
          value={gaugeData[0].value}
        />

        <RemainingPart
          color="#ebebeb"
          innerRadius={radius - fillSize}
          outerRadius={radius}
          startAngle={gaugeData[1].startAngle}
          endAngle={gaugeData[1].endAngle}
          value={gaugeData[1].value}
        />
      </g>
    </svg>
  )
}

Gauge.propTypes = {
  value: T.number,
  total: T.number,
  unit: T.string,
  percent: T.bool
}

Gauge.defaultProps = {
  value: 25,
  total: 100,
  percent: true
}

export {
  Gauge
}
