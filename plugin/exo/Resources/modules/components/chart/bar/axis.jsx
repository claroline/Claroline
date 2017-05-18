import React, { Component } from 'react'
import {select} from 'd3-selection'

const T = React.PropTypes

export default class Axis extends Component {

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
    switch (this.props.axisType) {
      case 'x':
        transform = `translate(0, ${this.props.height})`
        break
      case 'labelX':
        transform = `translate(${(this.props.width - this.props.margin.left - this.props.margin.right) / 2}, ${this.props.height + 50})`
        break
      case 'labelY':
        transform = `translate(-60, ${(this.props.height + this.props.margin.top + this.props.margin.bottom) / 2})rotate(-90)`
        break
    }

    return (
      <g className="axis" ref="axis" transform={transform}>
        {this.props.axisLabel &&
          <text className="axis-label">{this.props.axisLabel}</text>
        }
      </g>
    )
  }
}

Axis.propTypes = {
  height: T.number.isRequired,
  width: T.number.isRequired,
  axis: T.func,
  axisType: T.oneOf(['x', 'y', 'labelX', 'labelY']).isRequired,
  axisLabel: T.string,
  margin: T.shape({
    top: T.number.isRequired,
    right: T.number.isRequired,
    bottom: T.number.isRequired,
    left: T.number.isRequired
  }).isRequired
}
