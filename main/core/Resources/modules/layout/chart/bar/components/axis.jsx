import React, { Component } from 'react'
import {PropTypes as T} from 'prop-types'
import {select} from 'd3-selection'

import {
  AXIS_TYPE_X,
  AXIS_TYPE_Y,
  AXIS_TYPE_LABEL_X,
  AXIS_TYPE_LABEL_Y
} from './../enums'

class Axis extends Component {

  componentDidUpdate() {
    this.renderAxis()
  }

  componentDidMount() {
    this.renderAxis()
  }

  renderAxis() {
    if (this.props.axis) {
      const node = this.refs.axis
      select(node).call(this.props.axis)
    }
  }

  render() {
    let transform = ''
    switch (this.props.type) {
      case AXIS_TYPE_X:
        transform = `translate(0, ${this.props.height})`
        break
      case AXIS_TYPE_Y:
        transform = ''
        break
      case AXIS_TYPE_LABEL_X:
        transform = `translate(${(this.props.width - this.props.margin.left - this.props.margin.right) / 2}, ${this.props.height + 40})`
        break
      case AXIS_TYPE_LABEL_Y:
        transform = `translate(${0 - this.props.margin.left + 20}, ${(this.props.height + this.props.margin.top + this.props.margin.bottom) / 2})rotate(-90)`
        break
    }

    return (
      <g className="axis" ref="axis" transform={transform}>
        {this.props.label &&
          <text className="axis-label">{this.props.label}</text>
        }
      </g>
    )
  }
}

Axis.propTypes = {
  height: T.number.isRequired,
  width: T.number.isRequired,
  axis: T.func,
  type: T.oneOf([AXIS_TYPE_X, AXIS_TYPE_Y, AXIS_TYPE_LABEL_X, AXIS_TYPE_LABEL_Y]).isRequired,
  label: T.string,
  margin: T.shape({
    top: T.number.isRequired,
    right: T.number.isRequired,
    bottom: T.number.isRequired,
    left: T.number.isRequired
  }).isRequired
}

export {
  Axis
}
