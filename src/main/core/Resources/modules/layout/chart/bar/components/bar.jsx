import React from 'react'
import {PropTypes as T} from 'prop-types'

/**
 * Draws a rectangle to represent a Bar on a chart.
 */
const Bar = (props) => 
  <rect
    fill={props.color}
    width={props.width}
    height={props.maxHeight - props.height}
    x={props.offsetX}
    y={props.height + props.offsetY}
    onClick={() => props.onClick()}
  />
    

Bar.propTypes = {
  color: T.string,
  width: T.number,
  height: T.number.isRequired,
  maxHeight: T.number.isRequired,
  offsetX: T.number,
  offsetY: T.number,
  onClick: T.func
}

Bar.defaultProps = {
  color: '#337ab7', // Default bootstrap primary color
  width: 10,
  height: 0,
  offsetX: 0,
  offsetY: 0,
  onClick: () => false
}

export {
  Bar
}
