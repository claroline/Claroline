import React, { Component } from 'react'
import {max, range} from 'd3-array'
import {scaleLinear, scaleBand} from 'd3-scale'

import Bar from './bar.jsx'

const T = React.PropTypes

/**
 * Represents data on a Bar chart.
 */
export default class DataSeries extends Component {
  render() {
    const yScale = scaleLinear()
      .domain([0, max(this.props.data)])
      .range([0, this.props.height])

    const xScale = scaleBand()
      .domain(range(this.props.data.length))
      .rangeRound([0, this.props.width])
      .paddingInner([0.2])

    // D3 Axis - renders a d3 scale in SVG
    /*var xAxis = d3.svg.axis()
      .scale(x)
      .orient("bottom");

    var yAxis = d3.svg.axis()
      .scale(y)
      .orient("left")
      .ticks(10, "%");*/

    return (
      <g>
        {this.props.data.map((point, i) => (
          <Bar
            key={i}
            height={yScale(point)}
            width={xScale.bandwidth()}
            offset={xScale(i)}
            maxHeight={this.props.height}
            color={this.props.color}
          />
        ))}
      </g>
    )
  }
}

DataSeries.propTypes = {
  data: T.array.isRequired,
  width: T.number.isRequired,
  height: T.number.isRequired,
  color: T.string
}

DataSeries.defaultProps = {
  color: '#337ab7' // Default bootstrap primary color
}