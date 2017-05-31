import React, { Component } from 'react'
import {PropTypes as T} from 'prop-types'

import Chart from './../base/chart.jsx'
import DataSeries from './data-series.jsx'

/**
 * Draws a Bar chart
 */
export default class PieChart extends Component {
  render() {
    return (
      <Chart
        width={this.props.width}
        height={this.props.width}
      >
        <DataSeries
          data={this.props.data}
          colors={this.props.colors}
          innerRadius={0}
          outerRadius={this.props.width/2}
          showValue={this.props.showValue}
        />
      </Chart>
    )
  }
}

PieChart.propTypes = {
  data: T.array.isRequired,
  colors: T.arrayOf(T.string).isRequired,
  width: T.number,
  showValue: T.bool.isRequired
}

PieChart.defaultProps = {
  width: 550,
  showValue: true
}
