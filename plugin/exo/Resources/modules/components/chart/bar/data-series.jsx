import React, { Component } from 'react'
import {PropTypes as T} from 'prop-types'
import Bar from './bar.jsx'

/**
 * Represents data on a Bar chart.
 */
export default class DataSeries extends Component {

  render() {
    const hasNegativeValues = Object.keys(this.props.data).some(key => parseFloat(this.props.data[key].yData) < 0)

    return (
      <g>
        {Object.keys(this.props.data).map((key, i) => {
          const isNegativeValue = parseFloat(this.props.data[key].yData) < 0
          return (
            <Bar
              key={i}
              height={isNegativeValue ? 0 : this.props.yScale(this.props.data[key].yData)}
              width={this.props.xScale.bandwidth()}
              offsetX={this.props.xScale(this.props.data[key].xData)}
              offsetY={isNegativeValue ? this.props.height / 2 : 0}
              maxHeight={hasNegativeValues ? this.props.height / 2 : this.props.height}
              color={isNegativeValue ? this.props.altColor : this.props.color}
            />
          )
        })}
      </g>
    )
  }
}

DataSeries.propTypes = {
  data: T.object.isRequired,
  yScale: T.func.isRequired,
  xScale: T.func.isRequired,
  height: T.number.isRequired,
  color: T.string,
  altColor: T.string
}

DataSeries.defaultProps = {
  color: '#337ab7', // Default bootstrap primary color
  altColor: 'brown'
}
