import React from 'react'
import {PropTypes as T} from 'prop-types'

const DataGrid = props =>
  <div className={`data-grid data-grid-${props.size}`}>

  </div>

DataGrid.propTypes = {
  selectable: T.bool,
  size: T.oneOf(['sm', 'lg'])
}

DataGrid.defaultProps = {
  selectable: false,
  size: 'sm'
}

export {DataGrid}
