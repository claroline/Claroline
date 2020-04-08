import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ContentHtml} from '#/main/app/content/components/html'

const SimpleWidget = props => {
  if (props.content) {
    return (
      <ContentHtml>{props.content}</ContentHtml>
    )
  }

  return (
    <ContentPlaceholder
      size="lg"
      icon="fa fa-file"
      title={trans('no_content')}
    />
  )
}


SimpleWidget.propTypes = {
  content: T.string
}

export {
  SimpleWidget
}
