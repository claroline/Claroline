import React from 'react'
import {PropTypes as T} from 'prop-types'
import {pie} from 'd3-shape'
import {formatData} from '#/main/core/layout/chart/utils'
import {Arc} from '#/main/core/layout/chart/pie/components/arc.jsx'

const DataSeries = props => {
  const pieInstance = pie().sort(null)
  const formattedData = formatData(props.data)
  const arcData = pieInstance(formattedData.y.values)
  const total = formattedData.y.values.reduce((t, v) => t+v, 0)
  return (
    <g>
      {arcData.map((arc, index) => (
        <Arc
          key={index}
          color={props.colors[index]}
          innerRadius={props.innerRadius}
          outerRadius={props.outerRadius}
          startAngle={arc.startAngle}
          endAngle={arc.endAngle}
          value={props.showPercentage ? (arc.value/total*100).toFixed(2) + '%' : arc.value}
          showValue={props.showValue && arc.value > 0}
        />
      ))}
    </g>
  )
}

DataSeries.propTypes = {
  data: T.oneOfType([T.array, T.object]).isRequired,
  colors: T.arrayOf(T.string).isRequired,
  innerRadius: T.number.isRequired,
  outerRadius: T.number.isRequired,
  showValue: T.bool.isRequired,
  showPercentage: T.bool.isRequired
}

export {
  DataSeries
}