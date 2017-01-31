import React, { Component } from 'react'

import Chart from './../base/chart.jsx'
import DataSeries from './../pie/data-series.jsx'

const T = React.PropTypes

/**
 * Draws a Circular gauge
 */
export default class CircularGauge extends Component {
  render() {
    const radius = this.props.width / 2

    return (
      <Chart
        width={this.props.width}
        height={this.props.width}
      >
        <g
          transform={`translate(${ this.props.width / 2 }, ${ this.props.width / 2 })`}
          alignmentBaseline={'middle'}
        >
          <text
            strokeWidth={1}
            textAnchor={'middle'}
          >
            {this.props.label}
          </text>

          <text
            fontSize="24"
            stroke={this.props.color}
            fill={this.props.color}
            textAnchor={'middle'}
            y={24}
          >
            {this.props.value}
          </text>
        </g>

        <DataSeries
          data={[this.props.value, this.props.max - this.props.value]}
          colors={[this.props.color, '#ccc']}
          innerRadius={radius - this.props.size}
          outerRadius={radius}
        />
      </Chart>
    )
  }
}

CircularGauge.propTypes = {
  value: T.number,
  max: T.number.isRequired,
  color: T.string,
  label: T.string,
  width: T.number,
  size: T.number
}

CircularGauge.defaultProps = {
  value: 0,
  color: '#337ab7', // Default bootstrap primary color
  label: null,
  width: 200,
  size: 30
}
