import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {DataCell as DataCellTypes} from '#/main/app/data/prop-types'

import {getPlainText} from '#/main/app/data/html/utils'

// react components require a DOM wrapper, that's why there is an extra span.
//TODO: Change it to only return plain text once intra-plugin communication available
const HtmlCell = props => {
  if (props.data) {
    if (props.trust) {
      return <span dangerouslySetInnerHTML={{__html: props.data}}/>
    } else {
      const plainText = getPlainText(props.data)

      return <span>{50 < plainText.length ? `${plainText.substr(0, 50)}...` : plainText}</span>
    }
  } else {
    return null
  }
}

implementPropTypes(HtmlCell, DataCellTypes, {
  trust: T.bool
})

export {
  HtmlCell
}
