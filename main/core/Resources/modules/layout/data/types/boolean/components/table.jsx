import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {TableCell} from '#/main/core/layout/table/components/table.jsx'
import {translateBool} from '#/main/core/layout/data/types/boolean/utils'

const BooleanCell = props =>
  <TableCell align="center" className="boolean-cell">
    <span className={classes('fa fa-fw', {
      'fa-check true': props.data,
      'fa-times false': !props.data
    })} />
    <span className="sr-only">{translateBool(props.data)}</span>
  </TableCell>

BooleanCell.propTypes = {
  data: T.bool.isRequired
}

export {
  BooleanCell
}
