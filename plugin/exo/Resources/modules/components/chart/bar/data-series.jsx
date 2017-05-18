import React, { Component } from 'react'
import Bar from './bar.jsx'

const T = React.PropTypes

/**
 * Represents data on a Bar chart.
 */
export default class DataSeries extends Component {
  render() {
    return (
      <g>
        {Object.keys(this.props.data).map((key, i) => (
          <Bar
            key={i}
            height={this.props.yScale(this.props.data[key].yData)}
            width={this.props.xScale.bandwidth()}
            offset={this.props.xScale(this.props.data[key].xData)}
            maxHeight={this.props.height}
            color={this.props.color}
          />
        ))}
      </g>
    )
  }
}

DataSeries.propTypes = {
  data: T.object.isRequired,
  yScale: T.func.isRequired,
  xScale: T.func.isRequired,
  width: T.number.isRequired,
  height: T.number.isRequired,
  color: T.string
}

DataSeries.defaultProps = {
  color: '#337ab7' // Default bootstrap primary color
}
