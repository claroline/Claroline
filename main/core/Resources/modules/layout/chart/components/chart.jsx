import React, { Component } from 'react'
import {PropTypes as T} from 'prop-types'

const Chart = props =>
  <svg className="chart" width={props.width} height={props.height}>
    {props.children}
  </svg>

Chart.propTypes = {
  width: T.number,
  height: T.number,
  children: T.node.isRequired
}

Chart.defaultProps = {
  width: 400,
  height: 400
}

export {
  Chart
}
