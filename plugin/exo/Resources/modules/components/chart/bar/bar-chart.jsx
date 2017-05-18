import React, { Component } from 'react'
import {max} from 'd3-array'
import {scaleLinear, scaleBand} from 'd3-scale'
import {axisLeft, axisBottom} from 'd3-axis'

import Chart from './../base/chart.jsx'
import DataSeries from './data-series.jsx'
import Axis from './axis.jsx'

const T = React.PropTypes

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
      .domain([0, max(yValues)])
      .range([height, 0])

    const xScale = scaleBand()
      .domain(xValues)
      .rangeRound([0, width])
      .paddingInner([0.2])

    const yAxis = axisLeft(yScale)
      .tickValues(yValues)

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
            width={width}
            height={height}
            yScale={yScale}
            xScale={xScale}
          />

          <Axis height={height} width={width} margin={this.props.margin} axis={yAxis} axisType="y" />
          <Axis height={height} width={width} margin={this.props.margin} axis={xAxis} axisType="x" />
          {this.props.labels.show &&
            <g>
              <Axis height={height} width={width} margin={this.props.margin} axisType="labelX" axisLabel={this.props.labels.labelX} />
              <Axis height={height} width={width} margin={this.props.margin} axisType="labelY" axisLabel={this.props.labels.labelY} />
            </g>
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
  labels: T.shape({
    show:T.bool.isRequired,
    labelX: T.string,
    labelY: T.string
  }).isRequired,
  margin: T.shape({
    top: T.number.isRequired,
    right: T.number.isRequired,
    bottom: T.number.isRequired,
    left: T.number.isRequired
  }).isRequired
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
  labels: {
    show: false,
    labelX: 'X Axis Data',
    labelY: 'Y Axis Data'
  }
}
