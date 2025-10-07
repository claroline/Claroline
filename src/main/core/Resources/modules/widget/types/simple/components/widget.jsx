import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {Html} from '#/main/app/components/html'

const SimpleWidget = props => {
  let content = props.content
  if (props.preview) {
    content = props.contentRaw
  }

  if (content) {
    return (
      <Html>{content}</Html>
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
  preview: T.bool,
  content: T.string,
  contentRaw: T.string
}

export {
  SimpleWidget
}
