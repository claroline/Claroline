import React from 'react'
import {PropTypes as T} from 'prop-types'
import {arc} from 'd3-shape'

/**
 * Draws an Arc on a Pie chart.
 */
const Arc = props => {
  const arcInstance = arc()
    .innerRadius(props.innerRadius)
    .outerRadius(props.outerRadius)
    .startAngle(props.startAngle)
    .endAngle(props.endAngle)
  const centroid = arcInstance.centroid().map(v => 1.5 * v)
  return (
    <g>
      <path
        d={arcInstance()}
        fill={props.color}
      />
      {props.showValue &&
        <text textAnchor={'middle'} transform={`translate(${centroid})`}>{props.value}</text>
      }
    </g>
  )
}

Arc.propTypes = {
  color: T.string,
  innerRadius: T.number.isRequired,
  outerRadius: T.number.isRequired,
  startAngle: T.number.isRequired,
  endAngle: T.number.isRequired,
  showValue: T.bool.isRequired,
  value: T.oneOfType([T.number, T.string])
}

Arc.defaultProps = {
  color: '#337ab7' // Default bootstrap primary color
}

export {
  Arc
}
