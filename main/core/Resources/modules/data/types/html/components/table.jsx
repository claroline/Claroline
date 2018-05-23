import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {DataCell as DataCellTypes} from '#/main/core/data/prop-types'

import {getPlainText} from '#/main/core/data/types/html/utils'

// react components require a DOM wrapper, that's why there is an extra span.
//TODO: Change it to only return plain text once intra-plugin communication available
const HtmlCell = props => props.data ?
  ( props.trust ?
    <span dangerouslySetInnerHTML={{__html: props.data}}/> :
    <span>{getPlainText(props.data)}</span>
  ) : null

implementPropTypes(HtmlCell, DataCellTypes, {
  trust: T.bool
})

export {
  HtmlCell
}
