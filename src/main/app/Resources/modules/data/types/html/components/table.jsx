import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

import {ContentHtml} from '#/main/app/content/components/html'
import {getPlainText} from '#/main/app/data/types/html/utils'

// react components require a DOM wrapper, that's why there is an extra span.
const HtmlCell = props => {
  if (props.data) {
    if (props.trust) {
      return <ContentHtml>{props.data}</ContentHtml>
    }

    const plainText = getPlainText(props.data)

    return <span>{50 < plainText.length ? `${plainText.substr(0, 50)}...` : plainText}</span>
  }

  return null
}

implementPropTypes(HtmlCell, DataCellTypes, {
  trust: T.bool
})

export {
  HtmlCell
}
