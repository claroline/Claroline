import React from 'react'
import {PropTypes as T} from 'prop-types'

import {TableCell} from '#/main/core/layout/table/components/table.jsx'
import {getPlainText} from '#/main/core/layout/data/types/html/utils'

const HtmlCell = props =>
  <TableCell className="html-cell">
    {getPlainText(props.data)}
  </TableCell>

HtmlCell.propTypes = {
  data: T.bool.isRequired
}

export {
  HtmlCell
}
