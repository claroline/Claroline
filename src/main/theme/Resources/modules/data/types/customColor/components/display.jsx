import React from 'react'

import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

const CustomColorDisplay = props =>
  <span className={`fa fa-fw fa-${props.data}`} style={{
    background: props.data
  }} />

CustomColorDisplay.propTypes = DataCellTypes.propTypes

export {
  CustomColorDisplay
}
