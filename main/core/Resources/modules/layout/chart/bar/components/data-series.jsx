import React from 'react'
import { implementPropTypes } from '#/main/core/scaffolding/prop-types'
import {DataSeries as DataSeriesTypes} from '#/main/core/layout/chart/prop-types'

import {Bar} from '#/main/core/layout/chart/bar/components/bar.jsx'

/**
 * Represents data on a Bar chart.
 */
const DataSeries = props => {
  const hasNegativeValues = props.data.y.values.some(val => val < 0)

  return (
    <g>
      {props.data.pairs.map((pair, i) => {
        const isNegativeValue = pair.y < 0
        return (
          <Bar
            key={i}
            height={isNegativeValue ? 0 : props.yScale(pair.y)}
            width={props.xScale.bandwidth()}
            offsetX={props.xScale(pair.x)}
            offsetY={isNegativeValue ? props.height / 2 : 0}
            maxHeight={hasNegativeValues ? props.height / 2 : props.height}
            color={isNegativeValue ? props.altColor : props.color}
          />
        )
      })}
    </g>
  )
}

implementPropTypes(DataSeries, DataSeriesTypes, {}, {})

export {
  DataSeries
}
