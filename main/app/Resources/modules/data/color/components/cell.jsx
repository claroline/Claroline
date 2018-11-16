import React from 'react'

import {DataCell as DataCellTypes} from '#/main/app/data/prop-types'

const ColorCell = props =>
  <span style={{
    background: props.data
  }} />

ColorCell.propTypes = DataCellTypes.propTypes

export {
  ColorCell
}
