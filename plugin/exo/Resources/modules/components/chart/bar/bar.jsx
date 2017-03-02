import React, { Component } from 'react'

const T = React.PropTypes

/**
 * Draws a rectangle to represent a Bar on a chart.
 */
export default class Bar extends Component {
  render() {
    return (
      <rect
        fill={this.props.color}
        width={this.props.width}
        height={this.props.height}
        x={this.props.offset}
        y={this.props.maxHeight - this.props.height}
      />
    )
  }
}

Bar.propTypes = {
  color: T.string,
  width: T.number,
  height: T.number.isRequired,
  maxHeight: T.number.isRequired,
  offset: T.number
}

Bar.defaultProps = {
  color: '#337ab7', // Default bootstrap primary color
  width: 10,
  height: 0,
  offset: 0
}