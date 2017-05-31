import React, { Component } from 'react'
import {PropTypes as T} from 'prop-types'

export default class Chart extends Component {
  render() {
    return (
      <svg className="chart" width={this.props.width} height={this.props.height}>{this.props.children}</svg>
    )
  }
}

Chart.propTypes = {
  width: T.number,
  height: T.number,
  children: T.node.isRequired
}

Chart.defaultProps = {
  width: 400,
  height: 400
}