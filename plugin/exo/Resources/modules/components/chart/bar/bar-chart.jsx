import React, { Component } from 'react'

import Chart from './../base/chart.jsx'
import DataSeries from './data-series.jsx'

const T = React.PropTypes

/**
 * Draws a Bar chart
 */
export default class BarChart extends Component {
  render() {
    return (
      <Chart
        width={this.props.width}
        height={this.props.height}
      >
        <DataSeries
          data={this.props.data}
          width={this.props.width}
          height={this.props.height}
        />
      </Chart>
    )
  }
}

BarChart.propTypes = {
  data: T.array.isRequired,
  width: T.number,
  height: T.number
}

BarChart.defaultProps = {
  width: 550,
  height: 400
}
