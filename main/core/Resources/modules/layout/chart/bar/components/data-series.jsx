import React from 'react'
import { implementPropTypes } from '#/main/app/prop-types'
import {DataSeries as DataSeriesTypes} from '#/main/core/layout/chart/prop-types'

import {formatData} from '#/main/core/layout/chart/utils'
import {Bar} from '#/main/core/layout/chart/bar/components/bar.jsx'

/**
 * Represents data on a Bar chart.
 *
 * @todo allow multiple DataSeries
 */
const DataSeries = props => {
  const data = formatData(props.data[0])
  const hasNegativeValues = data.y.values.some(val => val < 0)

  return (
    <g>
      {data.pairs.map((pair, i) => {
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
            onClick={() => props.onClick ? props.onClick(pair, i) : false}
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
