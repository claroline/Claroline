import React from 'react'
import {PropTypes as T} from 'prop-types'

import {TableCell} from '#/main/core/layout/table/components/table.jsx'

const FlagCell = props =>
  <TableCell align="center" className="boolean-cell">
    {props.data &&
      <span className="fa fa-fw fa-check" />
    }
  </TableCell>

FlagCell.propTypes = {
  data: T.bool.isRequired
}

export {
  FlagCell
}
