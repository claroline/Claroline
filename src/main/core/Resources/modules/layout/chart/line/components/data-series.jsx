import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {line, area} from 'd3-shape'

import {implementPropTypes} from '#/main/app/prop-types'
import {formatData} from '#/main/core/layout/chart/utils'
import {DataSeries as DataSeriesTypes} from '#/main/core/layout/chart/prop-types'
import {Path} from '#/main/core/layout/chart/line/components/path.jsx'

/**
 * Represents data on a Bar chart.
 */
const DataSeries = props =>
  <Fragment>
    {props.data.map((raw, idx) => {
      const data = formatData(raw)
      const lineData = line()
        //.curve(curveMonotoneX)
        .x(e => props.xScale(e.x))
        .y(e => props.yScale(e.y))(data.pairs) || ''

      let areaData = ''
      if (props.showArea) {
        areaData = area()
          //.curve(curveMonotoneX)
          .x(e => props.xScale(e.x))
          .y0(props.height)
          .y1(e => props.yScale(e.y))(data.pairs) || ''
      }

      return (
        <Path
          key={idx}
          strokeColor={props.colors[idx]}
          strokeWidth={2}
          line={lineData}
          area={areaData}
        />
      )
    })}
  </Fragment>

implementPropTypes(DataSeries, DataSeriesTypes, {
  showArea: T.bool
}, {
  data: [],
  showArea: false
})

export {
  DataSeries
}