import React, { Component } from 'react'
import {PropTypes as T} from 'prop-types'
import {arc} from 'd3-shape'
/**
 * Draws an Arc on a Pie chart.
 */
class Arc extends Component {
  render() {
    const arcInstance = arc()
      .innerRadius(this.props.innerRadius)
      .outerRadius(this.props.outerRadius)
      .startAngle(this.props.startAngle)
      .endAngle(this.props.endAngle)

    return (
      <path
        d={arcInstance()}
        fill={this.props.color}
      >
        {this.props.showValue &&
          <title>{this.props.value}</title>
        }
      </path>
    )
  }
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
