import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {TableCell} from '#/main/core/layout/table/components/table.jsx'

const BooleanCell = props =>
  <TableCell align="center" className="boolean-cell">
    <span className={classes('fa fa-fw', {
      'fa-check': props.data,
      'fa-times': !props.data
    })} />

  </TableCell>

BooleanCell.propTypes = {
  data: T.bool.isRequired
}

export {
  BooleanCell
}
