import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {implementPropTypes} from '#/main/app/prop-types'
import {Chart as ChartTypes} from '#/main/core/layout/chart/prop-types'

const Chart = props =>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    version="1.1"
    className={classes('chart', props.className)}
    {...!props.responsive && {width: props.width, height: props.height}}
    {...props.responsive && {viewBox: `0 0 ${props.width} ${props.height}`}}
    style={props.style}
  >
    <g transform={`translate(${props.margin.left}, ${props.margin.top})`}>
      {props.children}
    </g>
  </svg>

implementPropTypes(Chart, ChartTypes, {
  className: T.string,
  children: T.node.isRequired
})

export {
  Chart
}
