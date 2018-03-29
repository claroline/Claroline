import React from 'react'

import {DataCell as DataCellTypes} from '#/main/core/data/prop-types'

import {getPlainText} from '#/main/core/data/types/html/utils'

// react components require a DOM wrapper, that's why there is an extra span.
const HtmlCell = props => props.data ?
  <span>
    {getPlainText(props.data)}
  </span> : null

HtmlCell.propTypes = DataCellTypes.propTypes

export {
  HtmlCell
}
