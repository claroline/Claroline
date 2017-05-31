import React, { Component } from 'react'
import {PropTypes as T} from 'prop-types'
import {pie} from 'd3-shape'

import Arc from './arc.jsx'

export default class DataSeries extends Component {
  render() {
    const pieInstance = pie().sort(null)
    const arcData = pieInstance(this.props.data)

    return (
      <g transform={`translate(${ this.props.outerRadius }, ${ this.props.outerRadius })`}>
        {arcData.map((arc, index) => (
          <Arc
            key={index}
            color={this.props.colors[index]}
            innerRadius={this.props.innerRadius}
            outerRadius={this.props.outerRadius}
            startAngle={arc.startAngle}
            endAngle={arc.endAngle}
            value={arc.value}
            showValue={this.props.showValue}
          />
        ))}
      </g>
    )
  }
}

DataSeries.propTypes = {
  data: T.array.isRequired,
  colors: T.arrayOf(T.string).isRequired,
  innerRadius: T.number.isRequired,
  outerRadius: T.number.isRequired,
  showValue: T.bool.isRequired
}

DataSeries.defaultProps = {
}
