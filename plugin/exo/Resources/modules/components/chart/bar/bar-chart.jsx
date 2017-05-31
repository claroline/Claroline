import React, { Component } from 'react'
import {PropTypes as T} from 'prop-types'
import {max, min} from 'd3-array'
import {scaleLinear, scaleBand} from 'd3-scale'
import {axisLeft, axisBottom} from 'd3-axis'

import Chart from './../base/chart.jsx'
import DataSeries from './data-series.jsx'
import Axis from './axis.jsx'
import {
  AXIS_TYPE_X,
  AXIS_TYPE_Y,
  AXIS_TYPE_LABEL_X,
  AXIS_TYPE_LABEL_Y
} from './enums'

/**
 * Draws a Bar chart
 * data must be formed as a key value object collection
 * data : {
 *   key1: {xData: dataForXAxis, yData: dataForYAxis},
 *   key2: {xData: dataForXAxis, yData: dataForYAxis},
 *   ...
 * }
 */
export default class BarChart extends Component {
  render() {
    const yValues = Object.keys(this.props.data).map(key => { return this.props.data[key].yData })
    const xValues = Object.keys(this.props.data).map(key => { return this.props.data[key].xData })

    const width = this.props.width - this.props.margin.left - this.props.margin.right
    const height = this.props.height - this.props.margin.top - this.props.margin.bottom

    const yScale = scaleLinear()
    if(this.props.minMaxAsYDomain) {
      yScale.domain([min(yValues), max(yValues)])
    } else {
      yScale.domain([0, max(yValues)])
    }

    yScale.range([height, 0])

    const xScale = scaleBand()
      .domain(xValues)
      .rangeRound([0, width])
      .paddingInner([0.2])

    const yAxis = axisLeft(yScale)
    if (this.props.ticksAsYValues) {
      yAxis.tickValues(yValues)
    } else {
      yAxis.ticks(10)
    }

    const xAxis = axisBottom(xScale)
      .tickValues(xValues)

    return (
      <Chart
        width={this.props.width}
        height={this.props.height}
      >
        <g transform={`translate(${this.props.margin.left}, ${this.props.margin.top})`}>
          <DataSeries
            data={this.props.data}
            height={height}
            yScale={yScale}
            xScale={xScale}
          />
          <Axis height={height} width={width} margin={this.props.margin} axis={xAxis} type={AXIS_TYPE_X} />
          <Axis height={height} width={width} margin={this.props.margin} axis={yAxis} type={AXIS_TYPE_Y} />
          {this.props.xAxisLabel.show &&
            <Axis height={height} width={width} margin={this.props.margin} type={AXIS_TYPE_LABEL_X} label={this.props.xAxisLabel.text} />
          }
          {this.props.yAxisLabel.show &&
            <Axis height={height} width={width} margin={this.props.margin} type={AXIS_TYPE_LABEL_Y} label={this.props.yAxisLabel.text} />
          }
        </g>
      </Chart>
    )
  }
}

BarChart.propTypes = {
  data: T.object.isRequired,
  width: T.number,
  height: T.number,
  margin: T.shape({
    top: T.number.isRequired,
    right: T.number.isRequired,
    bottom: T.number.isRequired,
    left: T.number.isRequired
  }).isRequired,
  xAxisLabel: T.shape({
    show: T.bool.isRequired,
    text: T.string.isRequired
  }),
  yAxisLabel: T.shape({
    show: T.bool.isRequired,
    text: T.string.isRequired
  }),
  ticksAsYValues: T.bool,
  minMaxAsYDomain:T.bool
}

BarChart.defaultProps = {
  width: 550,
  height: 400,
  margin: {
    top: 20,
    right: 20,
    bottom: 20,
    left: 30
  },
  xAxisLabel: T.shape({
    show: false,
    text: 'X Axis DATA'
  }),
  yAxisLabel: T.shape({
    show: false,
    text: 'Y Axis DATA'
  }),
  ticksAsYValues: false,
  minMaxAsYDomain: false
}
