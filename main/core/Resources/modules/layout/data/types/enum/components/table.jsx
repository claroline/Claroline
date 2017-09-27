import React from 'react'
import {PropTypes as T} from 'prop-types'
import {TableCell} from '#/main/core/layout/table/components/table.jsx'

const EnumCell = props =>
  <TableCell align="center" className="enum-cell">
    {props.options.enum[props.data]}
  </TableCell>

EnumCell.propTypes = {
  data: T.any.isRequired,
  options: T.object.isRequired
}

export {
  EnumCell
}
