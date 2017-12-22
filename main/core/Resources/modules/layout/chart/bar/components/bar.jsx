import React, { Component } from 'react'
import {PropTypes as T} from 'prop-types'

/**
 * Draws a rectangle to represent a Bar on a chart.
 */
class Bar extends Component {
  render() {
    return (
      <rect
        fill={this.props.color}
        width={this.props.width}
        height={this.props.maxHeight - this.props.height}
        x={this.props.offsetX}
        y={this.props.height + this.props.offsetY}
      />
    )
  }
}

Bar.propTypes = {
  color: T.string,
  width: T.number,
  height: T.number.isRequired,
  maxHeight: T.number.isRequired,
  offsetX: T.number,
  offsetY: T.number
}

Bar.defaultProps = {
  color: '#337ab7', // Default bootstrap primary color
  width: 10,
  height: 0,
  offsetX: 0,
  offsetY: 0
}

export {
  Bar
}
