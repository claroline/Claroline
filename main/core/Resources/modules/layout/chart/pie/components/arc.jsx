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

  return (
    <path
      d={arcInstance()}
      fill={props.color}
    >
      {props.showValue &&
        <title>{props.value}</title>
      }
    </path>
  )
}

Arc.propTypes = {
  color: T.string,
  innerRadius: T.number.isRequired,
  outerRadius: T.number.isRequired,
  startAngle: T.number.isRequired,
  endAngle: T.number.isRequired,
  showValue: T.bool.isRequired,
  value: T.number
}

Arc.defaultProps = {
  color: '#337ab7' // Default bootstrap primary color
}

export {
  Arc
}
