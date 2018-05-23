import React from 'react'
import {PropTypes as T} from 'prop-types'
import {line, area} from 'd3-shape'
import {implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {DataSeries as DataSeriesTypes} from '#/main/core/layout/chart/prop-types'
import {Path} from '#/main/core/layout/chart/line/components/path.jsx'

/**
 * Represents data on a Bar chart.
 */
const DataSeries = props => {
  const lineData = line().x(e => props.xScale(e.x)).y(e => props.yScale(e.y))(props.data.pairs) || ''
  const areaData = props.showArea &&
    (area().x(e => props.xScale(e.x)).y0(props.height).y1(e => props.yScale(e.y))(props.data.pairs) || '')
  return (
    <g>
      <Path
        strokeColor={props.color}
        strokeWidth={2}
        line={lineData}
        area={areaData}
      />
    </g>
  )
}

implementPropTypes(DataSeries, DataSeriesTypes, {
  showArea: T.bool
}, {
  showArea: false
})

export {
  DataSeries
}
